<?php

namespace App\Support;

final class QuotationPrintLayout
{
    public const GRID_ROW_COUNT = 47;

    /** @var array<int, int|string> */
    private array $baseRowMap = [];

    /** @var array<int, array{kind: string, key: int|string, text?: string}> */
    private array $continuations = [];

  /** @var array<int, true> */
    private array $occupiedRows = [];

    private int $maxUsedRow = -1;

    /** @var list<array{preferred: int, base: int, end: int}> */
    private array $placedSpans = [];

    /**
     * @param  array<int, array<string, mixed>>  $stackedItems
     */
    public static function fromStackedItems(array $stackedItems): self
    {
        $entries = [];
        foreach ($stackedItems as $idx => $item) {
            $entries[] = [
                'key' => $idx,
                'preferred_row' => (int) ($item['original_row_index'] ?? 0),
                'is_text_only' => ! empty($item['is_text_only']),
                'item_id' => $item['item']['id'] ?? null,
                'details' => (string) ($item['item']['details'] ?? ''),
                'more_description' => (string) ($item['more_description'] ?? ''),
            ];
        }

        return new self($entries);
    }

    public static function fromQuotationItems(iterable $items): self
    {
        $entries = [];
        $fallbackRow = 0;

        foreach ($items as $item) {
            $preferredRow = $item->row_index;
            if ($preferredRow === null) {
                $preferredRow = $fallbackRow;
                $fallbackRow++;
            }

            $entries[] = [
                'key' => $item->id,
                'preferred_row' => (int) $preferredRow,
                'is_text_only' => $item->item_id === null && trim((string) ($item->custom_item_name ?? '')) !== '',
                'item_id' => $item->item_id,
                'details' => (string) ($item->item->details ?? ''),
                'more_description' => (string) ($item->more_description ?? ''),
            ];
        }

        return new self($entries);
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     */
    private function __construct(array $entries)
    {
        usort($entries, function (array $a, array $b): int {
            $rowCmp = $a['preferred_row'] <=> $b['preferred_row'];
            if ($rowCmp !== 0) {
                return $rowCmp;
            }

            return (string) $a['key'] <=> (string) $b['key'];
        });

        foreach ($entries as $entry) {
            $this->placeEntry($entry);
        }
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function placeEntry(array $entry): void
    {
        $preferred = (int) $entry['preferred_row'];
        $base = $this->nextFreeRow($preferred + $this->continuationShiftBeforePreferred($preferred));
        if ($base >= self::GRID_ROW_COUNT) {
            return;
        }

        $this->baseRowMap[$base] = $entry['key'];
        $this->occupiedRows[$base] = true;
        $row = $base;

        // Item master details are shown on DO only, not on quotations.
        $description = trim((string) ($entry['more_description'] ?? ''));
        if ($description !== '') {
            $row = $this->nextFreeRow($row + 1);
            if ($row < self::GRID_ROW_COUNT) {
                $this->continuations[$row] = [
                    'kind' => 'desc_spacer',
                    'key' => $entry['key'],
                ];
                $this->occupiedRows[$row] = true;
            }

            foreach (explode("\n", $description) as $line) {
                if (trim($line) === '') {
                    continue;
                }

                $wrapCount = $this->wrappedDescriptionLineCount($line);
                for ($wrap = 0; $wrap < $wrapCount; $wrap++) {
                    $row = $this->nextFreeRow($row + 1);
                    if ($row >= self::GRID_ROW_COUNT) {
                        break 2;
                    }
                    $this->continuations[$row] = [
                        'kind' => 'desc_line',
                        'key' => $entry['key'],
                        'text' => $line,
                    ];
                    $this->occupiedRows[$row] = true;
                }
            }
        }

        $this->maxUsedRow = max($this->maxUsedRow, $row);
        $this->placedSpans[] = [
            'preferred' => $preferred,
            'base' => $base,
            'end' => $row,
        ];
    }

    private function continuationShiftBeforePreferred(int $preferredRow): int
    {
        $shift = 0;

        foreach ($this->placedSpans as $span) {
            if ($span['preferred'] < $preferredRow) {
                $shift += $span['end'] - $span['base'];
            }
        }

        return $shift;
    }

    public function resolvedRowForPreferred(int $preferredRow): int
    {
        return $this->nextFreeRow($preferredRow + $this->continuationShiftBeforePreferred($preferredRow));
    }

    public function anchorRowForDisplayRow(int $displayRow): int
    {
        if ($displayRow <= 0) {
            return 0;
        }

        $low = 0;
        $high = $displayRow;

        while ($low < $high) {
            $mid = intdiv($low + $high, 2);

            if ($this->resolvedRowForPreferred($mid) < $displayRow) {
                $low = $mid + 1;
            } else {
                $high = $mid;
            }
        }

        return $low;
    }

    private function nextFreeRow(int $start): int
    {
        while ($start < self::GRID_ROW_COUNT && isset($this->occupiedRows[$start])) {
            $start++;
        }

        return $start;
    }

    /** @return array<int, int|string> */
    public function baseRowMap(): array
    {
        return $this->baseRowMap;
    }

    public function continuationAt(int $rowIndex): ?array
    {
        return $this->continuations[$rowIndex] ?? null;
    }

    public function isOccupiedRow(int $rowIndex): bool
    {
        return isset($this->occupiedRows[$rowIndex]);
    }

    public function maxUsedRowIndex(): int
    {
        return $this->maxUsedRow;
    }

    public function previewRowsToShow(): int
    {
        if ($this->maxUsedRow < 0) {
            return 0;
        }

        return $this->maxUsedRow + 1;
    }

    public static function normalizeDetailsLines(?string $details): array
    {
        if ($details === null || trim($details) === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $details);

        return array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));
    }

    public static function wrappedDescriptionLineCount(string $line): int
    {
        $charsPerRow = max(1, (int) config('do.description_chars_per_row', 80));

        return max(1, (int) ceil(strlen($line) / $charsPerRow));
    }
}
