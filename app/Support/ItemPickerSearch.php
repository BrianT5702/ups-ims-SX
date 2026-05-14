<?php

namespace App\Support;

use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ItemPickerSearch
{
    public static function displayNameSqlExpression(): string
    {
        return "COALESCE(NULLIF(TRIM(REGEXP_REPLACE(item_name, '^[[:space:]@#*~^$]+', '')), ''), item_name)";
    }

    public static function descriptionSortKey(string $itemName): string
    {
        $stripped = preg_replace('/^[\s@*#~^$]+/u', '', $itemName);
        $stripped = ltrim((string) $stripped);

        return $stripped !== '' ? $stripped : $itemName;
    }

    /** @param  Builder<Item>  $query */
    public static function applyDescriptionOrder(Builder $query): Builder
    {
        $expr = self::displayNameSqlExpression();

        return $query->orderByRaw($expr . ' ASC')->orderBy('id');
    }

    public static function fractionSearchKeepsRow(string $itemName, string $term): bool
    {
        $pos = mb_stripos($itemName, $term);
        if ($pos === false) {
            return false;
        }

        $before = mb_substr($itemName, 0, $pos);
        if (preg_match('/\s[xX]\s*$/u', $before)) {
            return false;
        }
        if (preg_match('/[xX]$/u', $before)) {
            return false;
        }

        return true;
    }

    /**
     * MySQL REGEXP pattern: whole inches + space + fraction (e.g. "1 1/4") so we can exclude it when
     * the user searches for the bare fraction only ("1/4").
     */
    public static function compoundMixedNumberRegexp(string $fractionTerm): string
    {
        $f = preg_replace('/\s+/', '', $fractionTerm);
        if (! preg_match('/^\d+\/\d+$/', $f)) {
            return '(?=a)b';
        }

        return '[0-9]+[[:space:]]+' . preg_quote($f, '/');
    }

    /**
     * F2 modal search (code or name mode). Empty term returns no rows.
     *
     * @return Collection<int, Item>
     */
    public static function modalSearch(string $mode, string $term): Collection
    {
        $term = trim($term);
        $query = Item::query()->select(['id', 'item_code', 'item_name', 'qty', 'um', 'cash_price']);

        if ($term === '') {
            return new Collection;
        }

        $isFractionSearch = preg_match('/\d+\s*\/\s*\d+/', $term) === 1;
        $isNameSearch = $mode !== 'code';

        if ($mode === 'code') {
            $escapedCodeTerm = addcslashes($term, '\%_');
            $query->where('item_code', 'like', $escapedCodeTerm . '%');
        } else {
            $regexTerm = preg_quote($term, '/');
            $query->whereRaw(
                "REGEXP_REPLACE(item_name, '^[[:space:]@#*~^$]+', '') REGEXP ?",
                ['(^|[[:space:][:punct:]])' . $regexTerm]
            );
        }

        $fetchLimit = $isFractionSearch ? 300 : 120;

        if ($isNameSearch) {
            $displayExpr = self::displayNameSqlExpression();
            $lowerTerm = mb_strtolower($term);
            $results = $query
                ->orderByRaw("CASE WHEN LOWER($displayExpr) LIKE ? THEN 0 ELSE 1 END", [$lowerTerm . '%'])
                ->orderByRaw("LOCATE(?, LOWER($displayExpr)) ASC", [$lowerTerm])
                ->orderByRaw($displayExpr . ' ASC')
                ->orderBy('id')
                ->limit($fetchLimit)
                ->get();
        } else {
            $results = $query->orderBy('item_code')->orderBy('id')
                ->limit($fetchLimit)
                ->get();
        }

        if ($isFractionSearch && $mode !== 'code') {
            $results = $results
                ->filter(fn ($item) => self::fractionSearchKeepsRow($item->item_name ?? '', $term))
                ->sortBy(fn ($item) => mb_strtolower(self::descriptionSortKey($item->item_name ?? '')))
                ->values()
                ->take(120);
        }

        return $results;
    }
}
