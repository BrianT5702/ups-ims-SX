<?php

namespace App\Support;

use App\Helpers\CompanyAccess;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InventoryListBrowse
{
    public const SESSION_KEY = 'inventory_list_browse';

    public const ITEM_CACHE_SESSION_KEY = 'inventory_browse_item_cache';

    /** Warm ordered IDs on the list page when result sets are reasonably small. */
    private const LIST_WARM_MAX_TOTAL = 1500;

    /** Preload all item rows for instant in-browser browse (arrow keys). */
    public const FULL_SNAPSHOT_MAX = 800;

    private const NAME_SORT_EXPR = "COALESCE(NULLIF(TRIM(REGEXP_REPLACE(item_name, '^[[:space:]@#*~^$]+', '')), ''), item_name)";

    private const ITEM_SNAPSHOT_COLUMNS = [
        'id',
        'item_code',
        'item_name',
        'qty',
        'cust_price',
        'cost',
        'term_price',
        'cash_price',
        'stock_alert_level',
        'sup_id',
        'cat_id',
        'family_id',
        'group_id',
        'warehouse_id',
        'location_id',
        'image',
        'um',
        'memo',
        'details',
    ];

    /**
     * Persist list filters/sort so the item form can walk prev/next in the same order.
     *
     * @param  object  $list  ItemList Livewire instance
     */
    public static function saveContextFromList(object $list): void
    {
        session()->forget(self::ITEM_CACHE_SESSION_KEY);

        session([
            self::SESSION_KEY => [
                'active_db' => session('active_db', DB::getDefaultConnection()),
                'itemSearchTerm' => $list->itemSearchTerm,
                'itemSearchMode' => $list->itemSearchMode,
                'filterFamilyId' => $list->filterFamilyId,
                'filterLocationId' => $list->filterLocationId,
                'filterDeadStock' => $list->filterDeadStock,
                'quantityFilter' => $list->quantityFilter,
                'selectedCategories' => array_values($list->selectedCategories ?? []),
                'selectedFamilies' => array_values($list->selectedFamilies ?? []),
                'selectedGroups' => array_values($list->selectedGroups ?? []),
                'selectedSuppliers' => array_values($list->selectedSuppliers ?? []),
                'sortField' => $list->sortField,
                'sortDirection' => $list->sortDirection,
            ],
        ]);
    }

    /**
     * Pre-build ordered IDs while the user is still on the list (avoids wait on first item open).
     */
    public static function warmOrderedIdsForList(int $resultTotal, bool $hasSearch): void
    {
        if (! self::context()) {
            return;
        }

        if (! $hasSearch && $resultTotal > self::LIST_WARM_MAX_TOTAL) {
            return;
        }

        $ids = self::ensureOrderedIds();
        if ($ids !== [] && $resultTotal <= self::FULL_SNAPSHOT_MAX) {
            self::warmItemSnapshotCacheForIds($ids);
        }
    }

    /**
     * @param  list<int>  $ids
     */
    public static function warmItemSnapshotCacheForIds(array $ids): void
    {
        if ($ids === [] || count($ids) > self::FULL_SNAPSHOT_MAX) {
            return;
        }

        $cache = session(self::ITEM_CACHE_SESSION_KEY, []);
        $missing = array_values(array_diff(
            array_map('intval', $ids),
            array_map('intval', array_keys($cache))
        ));

        if ($missing === []) {
            return;
        }

        foreach (array_chunk($missing, 250) as $chunk) {
            Item::query()
                ->select(self::ITEM_SNAPSHOT_COLUMNS)
                ->whereIn('id', $chunk)
                ->get()
                ->each(function (Item $item) use (&$cache) {
                    $cache[(int) $item->id] = $item->only(self::ITEM_SNAPSHOT_COLUMNS);
                });
        }

        session([self::ITEM_CACHE_SESSION_KEY => $cache]);
    }

    /**
     * Payload for instant client-side browse (no Livewire round-trip per arrow).
     *
     * @return array{ids: list<int>, items: array<string, array<string, mixed>>, index: int, locationsByWarehouse: array<string, list<array{id: int, name: string}>>, urlTemplate: string}|null
     */
    public static function buildClientPayload(int $currentItemId, bool $isView): ?array
    {
        $ctx = self::context();
        if (! $ctx) {
            return null;
        }

        $ids = self::ensureOrderedIds($ctx);
        if ($ids === [] || count($ids) > self::FULL_SNAPSHOT_MAX) {
            return null;
        }

        $idx = array_search($currentItemId, $ids, true);
        if ($idx === false) {
            return null;
        }

        self::warmItemSnapshotCacheForIds($ids);
        $cache = session(self::ITEM_CACHE_SESSION_KEY, []);

        $items = [];
        $warehouseIds = [];
        foreach ($ids as $id) {
            if (! isset($cache[$id])) {
                continue;
            }
            $row = self::snapshotToClientRow($cache[$id]);
            $items[(string) $id] = $row;
            if (! empty($row['warehouse_id'])) {
                $warehouseIds[(int) $row['warehouse_id']] = true;
            }
        }

        if (count($items) !== count($ids)) {
            return null;
        }

        $routeName = $isView ? 'items.view' : 'items.edit';
        $urlTemplate = str_replace(
            (string) $currentItemId,
            '__ID__',
            route($routeName, ['item' => $currentItemId])
        );

        return [
            'ids' => $ids,
            'items' => $items,
            'index' => (int) $idx,
            'locationsByWarehouse' => self::locationsForWarehouses(array_keys($warehouseIds)),
            'urlTemplate' => $urlTemplate,
        ];
    }

    /**
     * @param  list<int>  $warehouseIds
     * @return array<string, list<array{id: int, name: string}>>
     */
    private static function locationsForWarehouses(array $warehouseIds): array
    {
        if ($warehouseIds === []) {
            return [];
        }

        $out = [];
        Location::query()
            ->select(['id', 'warehouse_id', 'location_name'])
            ->whereIn('warehouse_id', $warehouseIds)
            ->orderBy('location_name')
            ->get()
            ->groupBy('warehouse_id')
            ->each(function ($locations, $warehouseId) use (&$out) {
                $out[(string) $warehouseId] = $locations
                    ->map(fn ($loc) => ['id' => (int) $loc->id, 'name' => $loc->location_name])
                    ->values()
                    ->all();
            });

        return $out;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private static function snapshotToClientRow(array $row): array
    {
        $image = $row['image'] ?? null;

        return [
            'id' => (int) $row['id'],
            'item_code' => (string) ($row['item_code'] ?? ''),
            'item_name' => (string) ($row['item_name'] ?? ''),
            'qty' => CompanyAccess::displayInventoryQty($row['qty'] ?? 0, session('active_db')),
            'cust_price' => $row['cust_price'] ?? 0,
            'cost' => $row['cost'] ?? 0,
            'term_price' => $row['term_price'] ?? 0,
            'cash_price' => $row['cash_price'] ?? 0,
            'stock_alert_level' => $row['stock_alert_level'] ?? 0,
            'supplier' => $row['sup_id'] ?? '',
            'category' => $row['cat_id'] ?? '',
            'family' => $row['family_id'] ?? '',
            'group' => $row['group_id'] ?? '',
            'warehouse_id' => $row['warehouse_id'] ?? '',
            'location_id' => $row['location_id'] ?? '',
            'um' => (string) ($row['um'] ?? 'UNIT'),
            'memo' => (string) ($row['memo'] ?? ''),
            'details' => (string) ($row['details'] ?? ''),
            'image_url' => $image ? Storage::url($image) : null,
        ];
    }

    public static function getCachedItem(int $itemId): ?Item
    {
        $cache = session(self::ITEM_CACHE_SESSION_KEY, []);
        if (! isset($cache[$itemId]) || ! is_array($cache[$itemId])) {
            return null;
        }

        $item = new Item();
        $item->forceFill($cache[$itemId]);
        $item->exists = true;

        return $item;
    }

    /**
     * @param  list<int>  $ids
     */
    public static function cacheItemsAroundIndex(array $ids, int $index): void
    {
        $idsToCache = array_values(array_filter([
            $ids[$index] ?? null,
            $ids[$index - 1] ?? null,
            $ids[$index + 1] ?? null,
        ], fn ($id) => $id !== null));

        if ($idsToCache === []) {
            return;
        }

        $cache = session(self::ITEM_CACHE_SESSION_KEY, []);
        $missing = array_values(array_diff($idsToCache, array_map('intval', array_keys($cache))));

        if ($missing === []) {
            return;
        }

        $items = Item::query()
            ->select(self::ITEM_SNAPSHOT_COLUMNS)
            ->whereIn('id', $missing)
            ->get();

        foreach ($items as $item) {
            $cache[(int) $item->id] = $item->only(self::ITEM_SNAPSHOT_COLUMNS);
        }

        session([self::ITEM_CACHE_SESSION_KEY => $cache]);
    }

    public static function findItemForBrowse(int $itemId): Item
    {
        return self::getCachedItem($itemId)
            ?? Item::query()
                ->select(self::ITEM_SNAPSHOT_COLUMNS)
                ->findOrFail($itemId);
    }

    public static function context(): ?array
    {
        $ctx = session(self::SESSION_KEY);
        if (! is_array($ctx)) {
            return null;
        }

        $activeDb = session('active_db', DB::getDefaultConnection());
        if (($ctx['active_db'] ?? null) !== $activeDb) {
            return null;
        }

        return $ctx;
    }

    public static function queryFromContext(?array $context = null): Builder
    {
        $ctx = $context ?? self::context();
        if (! $ctx) {
            return Item::query()->whereRaw('0 = 1');
        }

        $query = Item::query()
            ->when(! empty($ctx['itemSearchTerm']), function ($query) use ($ctx) {
                $raw = trim((string) $ctx['itemSearchTerm']);
                if ($raw === '') {
                    return;
                }
                $norm = preg_replace('#\s*/\s*#', '/', $raw);
                $isFraction = (bool) preg_match('/^\d+\/\d+$/', $norm);
                $escapedRaw = addcslashes($raw, '\%_');
                $escapedNorm = addcslashes($norm, '\%_');
                $byName = ($ctx['itemSearchMode'] ?? 'code') === 'name';

                if ($byName) {
                    if ($isFraction) {
                        $compoundRe = ItemPickerSearch::compoundMixedNumberRegexp($norm);
                        $query->where(function ($q) use ($escapedNorm, $compoundRe) {
                            $q->where('item_name', 'like', '%' . $escapedNorm . '%')
                                ->whereRaw('NOT (item_name REGEXP ?)', [$compoundRe]);
                        });
                    } else {
                        $query->where('item_name', 'like', '%' . $escapedRaw . '%');
                    }
                } else {
                    if ($isFraction) {
                        $compoundRe = ItemPickerSearch::compoundMixedNumberRegexp($norm);
                        $query->where(function ($q) use ($escapedNorm, $compoundRe) {
                            $q->where('item_code', 'like', '%' . $escapedNorm . '%')
                                ->whereRaw('NOT (item_code REGEXP ?)', [$compoundRe]);
                        });
                    } else {
                        $query->where('item_code', 'like', '%' . $escapedRaw . '%');
                    }
                }
            })
            ->when(! empty($ctx['selectedCategories']), fn ($q) => $q->whereIn('cat_id', $ctx['selectedCategories']))
            ->when(! empty($ctx['selectedFamilies']), fn ($q) => $q->whereIn('family_id', $ctx['selectedFamilies']))
            ->when(! empty($ctx['selectedGroups']), fn ($q) => $q->whereIn('group_id', $ctx['selectedGroups']))
            ->when(! empty($ctx['selectedSuppliers']), fn ($q) => $q->whereIn('sup_id', $ctx['selectedSuppliers']))
            ->when(! empty($ctx['filterLocationId']), fn ($q) => $q->where('location_id', $ctx['filterLocationId']))
            ->when(! empty($ctx['filterDeadStock']), fn ($q) => $q->where('qty', 0))
            ->when(($ctx['quantityFilter'] ?? null) === 'zero', fn ($q) => $q->where('qty', 0))
            ->when(($ctx['quantityFilter'] ?? null) === 'positive', fn ($q) => $q->where('qty', '>', 0))
            ->when(($ctx['quantityFilter'] ?? null) === 'negative', fn ($q) => $q->where('qty', '<', 0));

        return $query;
    }

    /**
     * Ordered item IDs for the current list context (built once, then cached in session).
     *
     * @return list<int>
     */
    public static function ensureOrderedIds(?array $context = null): array
    {
        $ctx = $context ?? self::context();
        if (! $ctx) {
            return [];
        }

        if (array_key_exists('ordered_ids', $ctx) && is_array($ctx['ordered_ids'])) {
            return array_map('intval', $ctx['ordered_ids']);
        }

        $ids = self::fetchOrderedIds($ctx);
        $ctx['ordered_ids'] = $ids;
        session([self::SESSION_KEY => $ctx]);

        return $ids;
    }

    public static function browseIndex(int $itemId, ?array $context = null): ?int
    {
        $ids = self::ensureOrderedIds($context);
        if ($ids === []) {
            return null;
        }

        $idx = array_search($itemId, $ids, true);

        return $idx === false ? null : $idx;
    }

    public static function isItemInContext(int $itemId, ?array $context = null): bool
    {
        return self::browseIndex($itemId, $context) !== null;
    }

    /**
     * @return list<int>
     */
    private static function fetchOrderedIds(array $ctx): array
    {
        $query = self::queryFromContext($ctx);
        $sortField = in_array($ctx['sortField'] ?? '', ['item_code', 'item_name'], true)
            ? $ctx['sortField']
            : 'item_code';
        $sortDir = strtolower((string) ($ctx['sortDirection'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortField === 'item_code') {
            return $query
                ->orderBy('item_code', $sortDir)
                ->orderBy('id', $sortDir)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $expr = self::NAME_SORT_EXPR;

        return $query
            ->orderByRaw($expr . ' ' . strtoupper($sortDir))
            ->orderBy('id', $sortDir)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public static function adjacentItemId(int $currentId, string $direction, ?array $context = null): ?int
    {
        if (! in_array($direction, ['prev', 'next'], true)) {
            return null;
        }

        $ctx = $context ?? self::context();
        if (! $ctx) {
            return null;
        }

        $current = Item::find($currentId);
        if (! $current) {
            return null;
        }

        $sortField = in_array($ctx['sortField'] ?? '', ['item_code', 'item_name'], true)
            ? $ctx['sortField']
            : 'item_code';
        $sortDir = strtolower((string) ($ctx['sortDirection'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $wantNext = $direction === 'next';
        $seekForward = ($wantNext && $sortDir === 'asc') || (! $wantNext && $sortDir === 'desc');

        $query = self::queryFromContext($ctx);

        if ($sortField === 'item_code') {
            return self::adjacentByItemCode($query, $current, $seekForward);
        }

        return self::adjacentByItemName($query, $current, $seekForward);
    }

    public static function positionLabel(int $itemId, ?array $context = null): ?string
    {
        $idx = self::browseIndex($itemId, $context);
        if ($idx === null) {
            return null;
        }

        $total = count(self::ensureOrderedIds($context));

        return ($idx + 1) . ' of ' . $total;
    }

    private static function adjacentByItemCode(Builder $query, Item $current, bool $seekForward): ?int
    {
        $q = clone $query;

        if ($seekForward) {
            $id = $q->where(function ($sub) use ($current) {
                $sub->where('item_code', '>', $current->item_code)
                    ->orWhere(function ($inner) use ($current) {
                        $inner->where('item_code', $current->item_code)
                            ->where('id', '>', $current->id);
                    });
            })
                ->orderBy('item_code', 'asc')
                ->orderBy('id', 'asc')
                ->value('id');
        } else {
            $id = $q->where(function ($sub) use ($current) {
                $sub->where('item_code', '<', $current->item_code)
                    ->orWhere(function ($inner) use ($current) {
                        $inner->where('item_code', $current->item_code)
                            ->where('id', '<', $current->id);
                    });
            })
                ->orderBy('item_code', 'desc')
                ->orderBy('id', 'desc')
                ->value('id');
        }

        return $id ? (int) $id : null;
    }

    private static function adjacentByItemName(Builder $query, Item $current, bool $seekForward): ?int
    {
        $expr = self::NAME_SORT_EXPR;
        $currentKey = self::nameSortKey($current->item_name);
        $q = clone $query;

        if ($seekForward) {
            $id = $q->whereRaw(
                "({$expr} > ? OR ({$expr} = ? AND id > ?))",
                [$currentKey, $currentKey, $current->id]
            )
                ->orderByRaw("{$expr} asc")
                ->orderBy('id', 'asc')
                ->value('id');
        } else {
            $id = $q->whereRaw(
                "({$expr} < ? OR ({$expr} = ? AND id < ?))",
                [$currentKey, $currentKey, $current->id]
            )
                ->orderByRaw("{$expr} desc")
                ->orderBy('id', 'desc')
                ->value('id');
        }

        return $id ? (int) $id : null;
    }

    private static function nameSortKey(string $itemName): string
    {
        $trimmed = preg_replace('/^[[:space:]@#*~^$]+/u', '', $itemName) ?? $itemName;
        $trimmed = ltrim($trimmed);

        return $trimmed !== '' ? $trimmed : $itemName;
    }
}
