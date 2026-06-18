<?php

namespace App\Livewire\Concerns;

use App\Models\Item;
use App\Support\QuotationPrintLayout;
use App\Models\QuotationItem;
use Livewire\Attributes\On;

trait ManagesQuotationItemGrid
{
    public array $freeFormTextRows = [];

    /** Total row slots in the form grid (25 page 1 + 22 page 2). */
    private const QUOTATION_GRID_ROW_COUNT = 47;

    /** Rows visible in the scroll viewport before scrolling. */
    public const QUOTATION_VIEWPORT_ROWS = 21;

    /** ≤ this many item lines: one page with items + TOTAL + SIGNATURE (matches preview). */
    public const QUOTATION_PRINT_MAX_LINES_SINGLE_PAGE = 22;

    /** When above single-page max: page 1 holds up to this many grid lines + SIGNATURE (no TOTAL). */
    public const QUOTATION_PRINT_FIRST_PAGE_ITEMS_MAX = 25;

    /** Max grid lines on print page 2 (lines 26–47). */
    public const QUOTATION_PRINT_SECOND_PAGE_LINES_MAX = 22;

    #[On('do-item-picker-item-selected')]
    public function onDoItemPickerItemSelected(int $itemId, int $rowIndex): void
    {
        if ($this->isView) {
            return;
        }

        $this->addItemToRow($itemId, $rowIndex);
    }

    public function addItemToRow($itemId, $rowIndex): void
    {
        if ($this->isView) {
            return;
        }

        $this->convertFreeFormTextToItems();
        $this->addItem($itemId, $rowIndex);
    }

    public function quotationRowHasPendingFreeFormAtPreferred(int $preferredRow): bool
    {
        if (! isset($this->freeFormTextRows[$preferredRow])) {
            return false;
        }

        $rowData = $this->freeFormTextRows[$preferredRow];
        $text = is_array($rowData) ? ($rowData['text'] ?? '') : $rowData;

        return trim((string) $text) !== '';
    }

    public function quotationFreeFormPreferredKeyAtDisplayRow(int $displayRow): ?int
    {
        $layout = $this->quotationPrintLayout();

        foreach ($this->freeFormTextRows as $preferredRow => $rowData) {
            $preferredRow = (int) $preferredRow;

            if ($layout->resolvedRowForPreferred($preferredRow) !== $displayRow) {
                continue;
            }

            if ($this->quotationRowHasPendingFreeFormAtPreferred($preferredRow)) {
                return $preferredRow;
            }
        }

        return null;
    }

    public function quotationRowHasPendingFreeForm(int $displayRow): bool
    {
        return $this->quotationFreeFormPreferredKeyAtDisplayRow($displayRow) !== null;
    }

    public function quotationAnchorRowForDisplayRow(int $displayRow): int
    {
        return $this->quotationPrintLayout()->anchorRowForDisplayRow($displayRow);
    }

    public function quotationDisplayRowForPreferred(int $preferredRow): int
    {
        return $this->quotationPrintLayout()->resolvedRowForPreferred($preferredRow);
    }

    public function quotationRowShowsSequenceInput(int $rowIndex): bool
    {
        $layout = $this->quotationPrintLayout();

        if (isset($layout->baseRowMap()[$rowIndex])) {
            $item = $this->stackedItems[$layout->baseRowMap()[$rowIndex]] ?? null;

            return $item && $this->quotationStackedItemHasSequenceContent($item);
        }

        if ($layout->isOccupiedRow($rowIndex)) {
            return false;
        }

        return $this->quotationRowHasPendingFreeForm($rowIndex);
    }

    protected function isQuotationRowSequenceHidden(int $rowIndex): bool
    {
        [$rowToItemMap] = $this->buildQuotationRowMaps();

        if (isset($rowToItemMap[$rowIndex])) {
            $item = $this->stackedItems[$rowToItemMap[$rowIndex]] ?? null;

            return ! empty($item['sequence_hidden']);
        }

        $preferredRow = $this->quotationFreeFormPreferredKeyAtDisplayRow($rowIndex);

        if ($preferredRow === null) {
            return false;
        }

        $rowData = $this->freeFormTextRows[$preferredRow];

        return is_array($rowData) && ! empty($rowData['hide_sequence']);
    }

    protected function setQuotationRowSequenceHidden(int $rowIndex, bool $hidden): void
    {
        [$rowToItemMap] = $this->buildQuotationRowMaps();

        if (isset($rowToItemMap[$rowIndex])) {
            $this->stackedItems[$rowToItemMap[$rowIndex]]['sequence_hidden'] = $hidden;

            return;
        }

        $preferredRow = $this->quotationFreeFormPreferredKeyAtDisplayRow($rowIndex);

        if ($preferredRow === null) {
            return;
        }

        if (! is_array($this->freeFormTextRows[$preferredRow])) {
            $this->freeFormTextRows[$preferredRow] = ['text' => $this->freeFormTextRows[$preferredRow]];
        }

        $this->freeFormTextRows[$preferredRow]['hide_sequence'] = $hidden;
    }

    protected function textOnlyQtyForForm(mixed $qty): mixed
    {
        if ($qty === '' || $qty === null) {
            return '';
        }

        $f = is_numeric($qty) ? (float) $qty : floatval($qty);
        if ($f === 0.0) {
            return '';
        }

        return (floor($f) == $f) ? (int) $f : $f;
    }

    /**
     * @return array<int, int> stackedItems index => grid row index
     */
    protected function buildQuotationItemToRowMap(): array
    {
        [$rowToItemMap] = $this->buildQuotationRowMaps();
        $itemToRowMap = [];

        foreach ($rowToItemMap as $rowIndex => $itemIndex) {
            $itemToRowMap[(int) $itemIndex] = (int) $rowIndex;
        }

        return $itemToRowMap;
    }

    public function hasQuotationGridContent(): bool
    {
        if (! empty($this->stackedItems)) {
            return true;
        }

        foreach ($this->freeFormTextRows as $rowData) {
            $text = is_array($rowData) ? ($rowData['text'] ?? '') : $rowData;
            if (trim((string) $text) !== '') {
                return true;
            }
        }

        return false;
    }

    public function getCurrentRowCount(): int
    {
        return $this->countOccupiedQuotationGridSlots();
    }

    public function getRemainingRowCount(): int
    {
        return max(0, self::QUOTATION_GRID_ROW_COUNT - $this->getCurrentRowCount());
    }

    public function getQuotationMaxPrintRows(): int
    {
        return $this->calculateQuotationMaxPrintRows();
    }

    public function quotationPrintLayout(): QuotationPrintLayout
    {
        return QuotationPrintLayout::fromStackedItems($this->stackedItems);
    }

    public function isQuotationGridRowOccupied(int $rowIndex): bool
    {
        return $this->quotationPrintLayout()->isOccupiedRow($rowIndex);
    }

    public function getQuotationRowToItemMap(): array
    {
        [$rowToItemMap] = $this->buildQuotationRowMaps();

        return $rowToItemMap;
    }

    public function getQuotationGridRowCount(): int
    {
        return self::QUOTATION_GRID_ROW_COUNT;
    }

    public function getQuotationViewportRows(): int
    {
        return self::QUOTATION_VIEWPORT_ROWS;
    }

    public function getQuotationPage2StartRowIndex(): int
    {
        return self::QUOTATION_PRINT_FIRST_PAGE_ITEMS_MAX;
    }

    public function getFormRowsToShow(int $maxItemRowIndex = -1, int $occupiedRowCount = 0): int
    {
        return self::QUOTATION_GRID_ROW_COUNT;
    }

    public function moveItemUp($index): void
    {
        $this->moveItemToAdjacentEmptyRow($index, -1);
    }

    public function moveItemDown($index): void
    {
        $this->moveItemToAdjacentEmptyRow($index, 1);
    }

    protected function buildQuotationRowMaps(): array
    {
        $layout = $this->quotationPrintLayout();
        $rowToItemMap = $layout->baseRowMap();

        $itemToRowMap = [];
        foreach ($rowToItemMap as $rowIndex => $itemIndex) {
            $itemToRowMap[(int) $itemIndex] = (int) $rowIndex;
        }

        return [$rowToItemMap, $itemToRowMap];
    }

    protected function moveItemToAdjacentEmptyRow($index, int $direction): void
    {
        if ($this->isView || ! isset($this->stackedItems[$index])) {
            return;
        }

        [, $itemToRowMap] = $this->buildQuotationRowMaps();
        $layout = $this->quotationPrintLayout();
        $currentAnchor = (int) ($this->stackedItems[$index]['original_row_index'] ?? ($itemToRowMap[$index] ?? 0));
        $targetAnchor = $currentAnchor + $direction;

        if ($targetAnchor < 0 || $targetAnchor >= self::QUOTATION_GRID_ROW_COUNT) {
            return;
        }

        $targetDisplay = $layout->resolvedRowForPreferred($targetAnchor);

        if ($targetDisplay < 0 || $targetDisplay >= self::QUOTATION_GRID_ROW_COUNT) {
            return;
        }

        [$rowToItemMap] = $this->buildQuotationRowMaps();
        if (isset($rowToItemMap[$targetDisplay]) || $this->quotationRowHasPendingFreeForm($targetDisplay) || $layout->isOccupiedRow($targetDisplay)) {
            toastr()->warning('Target row is occupied. Move is only allowed into empty rows.');

            return;
        }

        $this->stackedItems[$index]['original_row_index'] = $targetAnchor;
    }

    protected function coalesceFreeFormTextRowsToAnchors(): void
    {
        if ($this->freeFormTextRows === []) {
            return;
        }

        $layout = $this->quotationPrintLayout();
        $merged = [];

        foreach ($this->freeFormTextRows as $key => $rowData) {
            $key = (int) $key;

            if (! is_array($rowData)) {
                $rowData = ['text' => $rowData];
            }

            $anchor = $layout->anchorRowForDisplayRow($layout->resolvedRowForPreferred($key));

            if (! isset($merged[$anchor])) {
                $merged[$anchor] = $rowData;

                continue;
            }

            foreach (['text', 'qty', 'um', 'price'] as $field) {
                $existing = trim((string) ($merged[$anchor][$field] ?? ''));
                $incoming = trim((string) ($rowData[$field] ?? ''));

                if ($existing === '' && $incoming !== '') {
                    $merged[$anchor][$field] = $rowData[$field];
                }
            }
        }

        $this->freeFormTextRows = $merged;
    }

    protected function hydrateQuotationFreeFormRowFromSaved($qItem, int $rowIndex): void
    {
        $name = trim((string) ($qItem->custom_item_name ?? ''));

        if ($name === '') {
            return;
        }

        $this->freeFormTextRows[$rowIndex] = [
            'text' => $name,
            'qty' => $this->textOnlyQtyForForm($qItem->qty),
            'um' => $qItem->custom_um ?? '',
            'price' => $qItem->unit_price ?? 0,
            'hide_sequence' => (bool) ($qItem->sequence_hidden ?? false),
        ];
    }

    protected function persistQuotationFreeFormRows(int $quotationId): void
    {
        $this->coalesceFreeFormTextRowsToAnchors();

        foreach ($this->freeFormTextRows as $anchorRow => $rowData) {
            $text = is_array($rowData) ? ($rowData['text'] ?? '') : $rowData;
            $text = trim((string) $text);

            if ($text === '') {
                continue;
            }

            $qty = is_array($rowData) ? (float) ($rowData['qty'] ?? 0) : 0;
            $price = is_array($rowData) ? (float) ($rowData['price'] ?? 0) : 0;
            $um = is_array($rowData) ? trim((string) ($rowData['um'] ?? '')) : '';

            QuotationItem::create([
                'quotation_id' => $quotationId,
                'item_id' => null,
                'row_index' => (int) $anchorRow,
                'sequence_hidden' => is_array($rowData) && ! empty($rowData['hide_sequence']),
                'custom_item_name' => $text,
                'custom_um' => $um !== '' ? $um : null,
                'qty' => $qty,
                'unit_price' => $price,
                'pricing_tier' => null,
                'more_description' => null,
                'amount' => $qty * $price,
            ]);
        }
    }

    protected function convertFreeFormTextToItems(): void
    {
        $convertedRows = [];

        foreach ($this->freeFormTextRows as $rowIndex => $rowData) {
            $text = is_array($rowData) ? ($rowData['text'] ?? '') : $rowData;
            $textTrim = trim((string) $text);

            $qtyFromRow = is_array($rowData) ? (float) ($rowData['qty'] ?? 0) : 0;
            $priceFromRow = is_array($rowData) ? (float) ($rowData['price'] ?? 0) : 0;
            $umFromRow = is_array($rowData) ? trim((string) ($rowData['um'] ?? '')) : '';

            if ($textTrim === '') {
                continue;
            }

            $convertedRows[] = $rowIndex;

            $this->stackedItems[] = [
                'item' => [
                    'id' => null,
                    'item_code' => '',
                    'item_name' => '',
                    'um' => '',
                    'details' => '',
                    'memo' => '',
                    'qty' => 0,
                    'cost' => 0,
                    'cust_price' => 0,
                    'term_price' => 0,
                    'cash_price' => 0,
                    'latest_quote_price' => null,
                    'latest_quote_date' => null,
                ],
                'custom_item_name' => $textTrim,
                'custom_um' => $umFromRow,
                'item_qty' => $this->textOnlyQtyForForm($qtyFromRow),
                'item_unit_price' => $priceFromRow,
                'amount' => floatval($qtyFromRow) * floatval($priceFromRow),
                'pricing_tier' => '',
                'more_description' => null,
                'is_text_only' => true,
                'original_row_index' => $rowIndex,
                'price_manually_modified' => true,
                'sequence_hidden' => is_array($rowData) && ! empty($rowData['hide_sequence']),
            ];
        }

        foreach ($convertedRows as $rowIndex) {
            unset($this->freeFormTextRows[$rowIndex]);
        }
    }

    /**
     * Drop blank text-only lines so preview/save does not persist empty "Detail/text" rows.
     */
    protected function pruneEmptyTextOnlyStackedItems(): void
    {
        $this->stackedItems = array_values(array_filter($this->stackedItems, function ($item) {
            $isTextOnly = ! empty($item['is_text_only']) || empty($item['item']['id']);
            if (! $isTextOnly) {
                return true;
            }

            return trim((string) ($item['custom_item_name'] ?? '')) !== '';
        }));
    }

    /**
     * Rows consumed by saved/converted lines only (matches DO estimateTotalRows).
     * Pending free-form typing must NOT reduce visible grid rows.
     */
    protected function estimateQuotationGridRows(bool $includeNewItem): int
    {
        $totalRows = count($this->stackedItems);

        if ($includeNewItem) {
            $totalRows += 1;
        }

        return $totalRows;
    }

    public function countOccupiedQuotationGridSlots(): int
    {
        [$rowToItemMap] = $this->buildQuotationRowMaps();
        $occupied = array_fill_keys(array_keys($rowToItemMap), true);
        $layout = $this->quotationPrintLayout();

        foreach ($this->freeFormTextRows as $preferredRow => $rowData) {
            $preferredRow = (int) $preferredRow;
            $displayRow = $layout->resolvedRowForPreferred($preferredRow);

            if (isset($occupied[$displayRow])) {
                continue;
            }

            if ($this->quotationRowHasPendingFreeFormAtPreferred($preferredRow)) {
                $occupied[$displayRow] = true;
            }
        }

        return count($occupied);
    }

    public function countQuotationGridLines(): int
    {
        [$rowToItemMap] = $this->buildQuotationRowMaps();
        $layout = $this->quotationPrintLayout();
        $maxRow = -1;

        foreach (array_keys($rowToItemMap) as $rowIndex) {
            $maxRow = max($maxRow, (int) $rowIndex);
        }

        foreach ($this->freeFormTextRows as $preferredRow => $rowData) {
            $preferredRow = (int) $preferredRow;

            if ($this->quotationRowHasPendingFreeFormAtPreferred($preferredRow)) {
                $maxRow = max($maxRow, $layout->resolvedRowForPreferred($preferredRow));
            }
        }

        $lines = $maxRow >= 0 ? $maxRow + 1 : 0;

        return min($lines, self::QUOTATION_GRID_ROW_COUNT);
    }

    public function countQuotationPrintLines(): int
    {
        $layout = $this->quotationPrintLayout();

        return max($this->countQuotationGridLines(), $layout->maxUsedRowIndex() + 1);
    }

    protected function quotationStackedItemHasSequenceContent(array $item): bool
    {
        $isTextOnly = ! empty($item['is_text_only']) || empty($item['item']['id']);

        if ($isTextOnly) {
            return trim((string) ($item['custom_item_name'] ?? '')) !== '';
        }

        return true;
    }

    /**
     * Grid row index => sequential item # (1, 2, 3…) for rows with content, top to bottom.
     *
     * @return array<int, int>
     */
    public function getQuotationRowToItemSequenceMap(): array
    {
        $layout = $this->quotationPrintLayout();
        $occupiedRows = [];

        foreach ($layout->baseRowMap() as $rowIndex => $itemIndex) {
            $item = $this->stackedItems[$itemIndex] ?? null;
            if ($item && $this->quotationStackedItemHasSequenceContent($item) && empty($item['sequence_hidden'])) {
                $occupiedRows[] = (int) $rowIndex;
            }
        }

        foreach ($this->freeFormTextRows as $rowIndex => $rowData) {
            $preferredRow = (int) $rowIndex;
            $displayRow = $layout->resolvedRowForPreferred($preferredRow);

            if (! $layout->isOccupiedRow($displayRow) && $this->quotationRowHasPendingFreeFormAtPreferred($preferredRow) && ! $this->isQuotationRowSequenceHidden($displayRow)) {
                $occupiedRows[] = $displayRow;
            }
        }

        $occupiedRows = array_values(array_unique($occupiedRows));
        sort($occupiedRows, SORT_NUMERIC);

        $sequenceMap = [];
        foreach ($occupiedRows as $i => $rowIndex) {
            $sequenceMap[$rowIndex] = $i + 1;
        }

        return $sequenceMap;
    }

    public function setQuotationItemSequenceFromRow(int $sourceRowIndex, $newSequence): void
    {
        if ($this->isView) {
            return;
        }

        if ($newSequence === '' || $newSequence === null) {
            if (! $this->quotationRowShowsSequenceInput($sourceRowIndex)) {
                return;
            }
            $this->setQuotationRowSequenceHidden($sourceRowIndex, true);

            return;
        }

        $newSequence = (int) $newSequence;
        if ($newSequence < 1) {
            if (! $this->quotationRowShowsSequenceInput($sourceRowIndex)) {
                return;
            }
            $this->setQuotationRowSequenceHidden($sourceRowIndex, true);

            return;
        }

        if (! $this->quotationRowShowsSequenceInput($sourceRowIndex)) {
            return;
        }

        $wasHidden = $this->isQuotationRowSequenceHidden($sourceRowIndex);
        $this->setQuotationRowSequenceHidden($sourceRowIndex, false);

        $sequenceMap = $this->getQuotationRowToItemSequenceMap();
        if (! isset($sequenceMap[$sourceRowIndex])) {
            if ($wasHidden) {
                $this->setQuotationRowSequenceHidden($sourceRowIndex, true);
            }

            return;
        }

        if ($newSequence === $sequenceMap[$sourceRowIndex]) {
            return;
        }

        [$rowToItemMap] = $this->buildQuotationRowMaps();
        $layout = $this->quotationPrintLayout();
        $rowSlots = array_keys($sequenceMap);
        sort($rowSlots, SORT_NUMERIC);

        $entries = [];
        foreach ($rowSlots as $row) {
            if (isset($rowToItemMap[$row])) {
                $entries[] = [
                    'type' => 'stacked',
                    'itemIndex' => $rowToItemMap[$row],
                    'row' => $row,
                    'payload' => null,
                ];
            } else {
                $preferredKey = $this->quotationFreeFormPreferredKeyAtDisplayRow($row);
                $entries[] = [
                    'type' => 'freeform',
                    'itemIndex' => null,
                    'row' => $row,
                    'payload' => $preferredKey !== null ? ($this->freeFormTextRows[$preferredKey] ?? null) : null,
                ];
            }
        }

        $sourcePos = null;
        foreach ($entries as $i => $entry) {
            if ($entry['row'] === $sourceRowIndex) {
                $sourcePos = $i;
                break;
            }
        }

        if ($sourcePos === null) {
            return;
        }

        $moving = $entries[$sourcePos];
        array_splice($entries, $sourcePos, 1);
        $newSequence = min($newSequence, count($entries) + 1);
        array_splice($entries, $newSequence - 1, 0, [$moving]);

        foreach ($entries as $entry) {
            if ($entry['type'] !== 'freeform') {
                continue;
            }

            $preferredKey = $this->quotationFreeFormPreferredKeyAtDisplayRow($entry['row']);
            if ($preferredKey !== null) {
                unset($this->freeFormTextRows[$preferredKey]);
            }
        }

        foreach ($rowSlots as $i => $targetDisplayRow) {
            $entry = $entries[$i];
            if ($entry['type'] === 'stacked') {
                $this->stackedItems[$entry['itemIndex']]['original_row_index'] = $layout->anchorRowForDisplayRow($targetDisplayRow);
            } elseif ($entry['payload'] !== null) {
                $this->freeFormTextRows[$layout->anchorRowForDisplayRow($targetDisplayRow)] = $entry['payload'];
            }
        }
    }

    public function removeQuotationRowAtGridIndex(int $rowIndex): void
    {
        if ($this->isView) {
            return;
        }

        [$rowToItemMap] = $this->buildQuotationRowMaps();

        if (isset($rowToItemMap[$rowIndex])) {
            $itemIndex = $rowToItemMap[$rowIndex];
            unset($this->stackedItems[$itemIndex]);
            $this->stackedItems = array_values($this->stackedItems);

            if (method_exists($this, 'recalculateTotals')) {
                $this->recalculateTotals();
            }

            return;
        }

        $preferredKey = $this->quotationFreeFormPreferredKeyAtDisplayRow($rowIndex);
        if ($preferredKey !== null) {
            unset($this->freeFormTextRows[$preferredKey]);
        }
    }

    public function estimateQuotationPrintPageCount(?int $lineCount = null): int
    {
        $lines = $lineCount ?? $this->countQuotationPrintLines();

        if ($lines <= 0) {
            return 1;
        }

        if ($lines <= self::QUOTATION_PRINT_MAX_LINES_SINGLE_PAGE) {
            return 1;
        }

        if ($lines <= self::QUOTATION_PRINT_FIRST_PAGE_ITEMS_MAX) {
            return 2;
        }

        $overflow = $lines - self::QUOTATION_PRINT_FIRST_PAGE_ITEMS_MAX;
        $overflowPages = max(1, (int) ceil($overflow / self::QUOTATION_PRINT_SECOND_PAGE_LINES_MAX));

        return 1 + $overflowPages;
    }

    /**
     * Fixed print-page zone for the edit grid: lines 1–25 = page 1, lines 26–47 = page 2.
     */
    public function getQuotationPrintPageForGridLine(int $gridLine): int
    {
        if ($gridLine <= self::QUOTATION_PRINT_FIRST_PAGE_ITEMS_MAX) {
            return 1;
        }

        return 2;
    }

    public function isQuotationGridRowOnSecondPrintPage(int $rowIndex): bool
    {
        return $this->getQuotationPrintPageForGridLine($rowIndex + 1) === 2;
    }

    /**
     * @return array{lines: int, pages: int}
     */
    public function getQuotationPrintPageStatus(): array
    {
        $layout = $this->quotationPrintLayout();
        $gridLines = max($this->countQuotationGridLines(), $layout->maxUsedRowIndex() + 1);

        return [
            'lines' => $gridLines,
            'pages' => $this->estimateQuotationPrintPageCount($gridLines),
        ];
    }

    protected function firstAvailableQuotationRowIndex(): ?int
    {
        $layout = $this->quotationPrintLayout();

        for ($rowIndex = 0; $rowIndex < self::QUOTATION_GRID_ROW_COUNT; $rowIndex++) {
            if (! $layout->isOccupiedRow($rowIndex) && ! $this->quotationRowHasPendingFreeForm($rowIndex)) {
                return $rowIndex;
            }
        }

        return null;
    }

    protected function makeQuotationStackedItemFromInventory(Item $item, int $rowIndex): array
    {
        $defaultUm = ($item->um ?? 'UNIT') === 'UNIT' ? 'UNITS' : ($item->um ?? 'UNIT');

        return [
            'item' => [
                'id' => $item->id,
                'item_code' => $item->item_code,
                'item_name' => $item->item_name,
                'qty' => $item->qty,
                'cost' => $item->cost,
                'cust_price' => $item->cust_price,
                'term_price' => $item->term_price,
                'cash_price' => $item->cash_price,
                'memo' => $item->memo ?? '',
                'details' => $item->details ?? '',
                'um' => $item->um ?? 'UNIT',
                'latest_quote_price' => $this->getLatestQuotationPriceForItem($item->id, $this->cust_id),
                'latest_quote_date' => $this->getLatestQuotationDateForItem($item->id, $this->cust_id),
            ],
            'custom_um' => $defaultUm,
            'item_qty' => 1,
            'pricing_tier' => '',
            'item_unit_price' => 0,
            'amount' => 0,
            'more_description' => null,
            'custom_item_name' => $item->item_name,
            'price_manually_modified' => true,
            'original_row_index' => $rowIndex,
        ];
    }

    protected function hydrateQuotationStackedItemFromSaved($qItem, ?int $rowIndex = null): array
    {
        $isTextOnly = empty($qItem->item_id);
        $itemModel = $qItem->item;

        if ($isTextOnly) {
            return [
                'item' => [
                    'id' => null,
                    'item_code' => '',
                    'item_name' => '',
                    'um' => '',
                    'details' => '',
                    'memo' => '',
                    'qty' => 0,
                    'cost' => 0,
                    'cust_price' => 0,
                    'term_price' => 0,
                    'cash_price' => 0,
                    'latest_quote_price' => null,
                    'latest_quote_date' => null,
                ],
                'custom_um' => $qItem->custom_um ?? '',
                'item_qty' => $this->textOnlyQtyForForm($qItem->qty),
                'pricing_tier' => $qItem->pricing_tier ?? '',
                'item_unit_price' => $qItem->unit_price,
                'amount' => $qItem->amount,
                'more_description' => $qItem->more_description,
                'custom_item_name' => $qItem->custom_item_name ?? '',
                'is_text_only' => true,
                'original_row_index' => $rowIndex ?? $qItem->row_index,
                'price_manually_modified' => empty($qItem->pricing_tier),
                'sequence_hidden' => (bool) ($qItem->sequence_hidden ?? false),
            ];
        }

        return [
            'item' => [
                'id' => $itemModel->id,
                'item_code' => $itemModel->item_code,
                'item_name' => $itemModel->item_name,
                'qty' => $itemModel->qty,
                'cost' => $itemModel->cost,
                'cust_price' => $itemModel->cust_price,
                'term_price' => $itemModel->term_price,
                'cash_price' => $itemModel->cash_price,
                'memo' => $itemModel->memo ?? '',
                'details' => $itemModel->details ?? '',
                'um' => $itemModel->um ?? 'UNIT',
                'latest_quote_price' => $this->getLatestQuotationPriceForItem($itemModel->id, $this->cust_id),
                'latest_quote_date' => $this->getLatestQuotationDateForItem($itemModel->id, $this->cust_id),
            ],
            'custom_um' => $qItem->custom_um ?? (($itemModel->um ?? 'UNIT') === 'UNIT' ? 'UNITS' : ($itemModel->um ?? 'UNIT')),
            'item_qty' => $qItem->qty,
            'pricing_tier' => $qItem->pricing_tier ?? '',
            'item_unit_price' => $qItem->unit_price,
            'amount' => $qItem->amount,
            'more_description' => $qItem->more_description,
            'custom_item_name' => $qItem->custom_item_name ?? $itemModel->item_name,
            'original_row_index' => $rowIndex ?? $qItem->row_index,
            'price_manually_modified' => empty($qItem->pricing_tier),
            'sequence_hidden' => (bool) ($qItem->sequence_hidden ?? false),
        ];
    }

    protected function persistQuotationStackedItems(int $quotationId): void
    {
        QuotationItem::where('quotation_id', $quotationId)->delete();

        foreach ($this->stackedItems as $idx => $item) {
            $isTextOnly = ! empty($item['is_text_only']) || empty($item['item']['id']);
            // Persist anchor rows — the layout engine derives pushed display rows from these.
            $rowIndex = (int) ($item['original_row_index'] ?? 0);

            if ($isTextOnly) {
                $name = trim((string) ($item['custom_item_name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $qty = floatval($item['item_qty'] ?? 0);
                $price = floatval($item['item_unit_price'] ?? 0);

                QuotationItem::create([
                    'quotation_id' => $quotationId,
                    'item_id' => null,
                    'row_index' => $rowIndex,
                    'sequence_hidden' => ! empty($item['sequence_hidden']),
                    'custom_item_name' => $item['custom_item_name'] ?? null,
                    'custom_um' => ! empty(trim((string) ($item['custom_um'] ?? ''))) ? trim((string) $item['custom_um']) : null,
                    'qty' => $qty,
                    'unit_price' => $price,
                    'pricing_tier' => null,
                    'more_description' => null,
                    'amount' => $qty * $price,
                ]);

                continue;
            }

            $moreDescription = $item['more_description'] ?? null;
            if ($moreDescription !== null && is_string($moreDescription) && trim($moreDescription) === '') {
                $moreDescription = null;
            }

            QuotationItem::create([
                'quotation_id' => $quotationId,
                'item_id' => $item['item']['id'],
                'row_index' => $rowIndex,
                'sequence_hidden' => ! empty($item['sequence_hidden']),
                'custom_item_name' => $item['custom_item_name'] ?? null,
                'custom_um' => ! empty(trim((string) ($item['custom_um'] ?? ''))) ? trim((string) $item['custom_um']) : null,
                'qty' => floatval($item['item_qty'] ?? 0),
                'unit_price' => floatval($item['item_unit_price'] ?? 0),
                'pricing_tier' => $item['pricing_tier'] ?? null,
                'more_description' => $moreDescription,
                'amount' => floatval($item['item_qty'] ?? 0) * floatval($item['item_unit_price'] ?? 0),
            ]);
        }

        $this->persistQuotationFreeFormRows($quotationId);
    }

    protected function seedQuotationLastValidDescriptions(): void
    {
        foreach ($this->stackedItems as $idx => $item) {
            $this->lastValidDescriptions[$idx] = $item['more_description'] ?? '';
        }
    }

    protected function normalizeQuotationDescriptions(): void
    {
        foreach ($this->stackedItems as $idx => $item) {
            if (! isset($item['more_description'])) {
                continue;
            }

            $desc = $item['more_description'];
            if (is_string($desc) && trim($desc) === '') {
                $this->stackedItems[$idx]['more_description'] = null;
            }
        }
    }

    public function calculateQuotationMaxPrintRows(): int
    {
        return QuotationPrintLayout::GRID_ROW_COUNT;
    }

    public function validateDescriptionRowsOnShow(int $index): void
    {
        if (! isset($this->stackedItems[$index])) {
            return;
        }
    }

    public function saveDescriptionAndValidate(int $index): void
    {
        if ($this->isView || ! isset($this->stackedItems[$index])) {
            return;
        }

        $currentDesc = $this->stackedItems[$index]['more_description'] ?? '';

        if ($currentDesc !== null && trim($currentDesc) === '') {
            $currentDesc = null;
            $this->stackedItems[$index]['more_description'] = null;
        }

        $this->lastValidDescriptions[$index] = $currentDesc;
    }
}
