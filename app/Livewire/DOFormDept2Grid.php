<?php

namespace App\Livewire;

use App\Models\Item;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Department 2 DO line grid only — small Livewire surface so code+Enter does not re-render the full DO form.
 */
class DOFormDept2Grid extends Component
{
    public array $stackedItems = [];

    public array $freeFormTextRows = [];

    public bool $isView = false;

    public bool $isPosted = false;

    public function mount(
        array $stackedItems = [],
        array $freeFormTextRows = [],
        bool $isView = false,
        bool $isPosted = false,
    ): void {
        $this->stackedItems = $stackedItems;
        $this->freeFormTextRows = $freeFormTextRows;
        $this->isView = $isView;
        $this->isPosted = $isPosted;
    }

    #[On('dept2-grid-reload')]
    public function reloadFromParent(array $stackedItems, array $freeFormTextRows): void
    {
        $this->stackedItems = $stackedItems;
        $this->freeFormTextRows = $freeFormTextRows;
    }

    public function addItemByCodeAtRow(int $rowIndex, ?string $code = null): void
    {
        if ($this->isView || $this->isPosted) {
            return;
        }

        if ($rowIndex < 0 || $rowIndex > 23) {
            return;
        }

        $code = trim((string) ($code ?? $this->freeFormTextRows[$rowIndex]['code'] ?? ''));
        if ($code === '') {
            toastr()->warning('Enter an item code first.');

            return;
        }

        $item = $this->findItemByCodeSearch($code);
        if (!$item) {
            $this->dispatch(
                'dept2-request-quick-add',
                rowIndex: $rowIndex,
                code: $code,
            )->to(DOForm::class);

            return;
        }

        $this->appendDept2ItemAtRow($item, $rowIndex);
    }

    #[On('dept2-append-item-at-row')]
    public function appendItemAtRow(int $itemId, int $rowIndex): void
    {
        if ($this->isView || $this->isPosted) {
            return;
        }

        $item = Item::query()->find($itemId);
        if (!$item) {
            toastr()->error('Item could not be loaded onto the line.');

            return;
        }

        $this->appendDept2ItemAtRow($item, $rowIndex);
    }

    public function removeItem(int $index): void
    {
        if ($this->isView || $this->isPosted) {
            return;
        }

        unset($this->stackedItems[$index]);
        $this->stackedItems = array_values($this->stackedItems);
        $this->syncToParent();
    }

    public function updatePriceLine(int $index): void
    {
        if (!isset($this->stackedItems[$index])) {
            return;
        }

        $item = $this->stackedItems[$index];
        $raw = $item['item_qty'] ?? null;
        $trimmed = is_string($raw) ? trim($raw) : $raw;
        $numericQty = ($trimmed === '' || $trimmed === null) ? 0.0 : floatval($trimmed);

        if (!empty($item['is_text_only'])) {
            $this->stackedItems[$index]['item_qty'] = $numericQty === 0.0
                ? ''
                : ((floor($numericQty) == $numericQty) ? (int) $numericQty : $numericQty);
        } else {
            $this->stackedItems[$index]['item_qty'] = $numericQty;
        }

        $item = $this->stackedItems[$index];
        $item['item_unit_price'] = floatval($item['item_unit_price'] ?? 0);
        $effQty = floatval(($item['item_qty'] === '' || $item['item_qty'] === null) ? 0 : $item['item_qty']);
        $this->stackedItems[$index]['amount'] = $effQty * $item['item_unit_price'];

        $this->syncToParent();
    }

    public function updateUnitPrice(int $index): void
    {
        if (!isset($this->stackedItems[$index])) {
            return;
        }

        $this->stackedItems[$index]['price_manually_modified'] = true;
        $this->updatePriceLine($index);
    }

    public function updatedStackedItems($value, $key): void
    {
        if (str_contains((string) $key, '.item_qty')) {
            $index = (int) explode('.', (string) $key)[1];
            $this->updatePriceLine($index);
        }
    }

    public function updatedFreeFormTextRows(): void
    {
        $this->syncToParent();
    }

    public function getCurrentRowCount(): int
    {
        return count($this->stackedItems);
    }

    public function getRemainingRowCount(): int
    {
        return max(0, 24 - $this->getCurrentRowCount());
    }

    public function getFormRowsToShow(int $maxItemRowIndex = -1, int $occupiedRowCount = 0): int
    {
        $positionSpan = max(0, $maxItemRowIndex + 1);
        $remaining = $this->getRemainingRowCount();
        $rowsForBudget = max(0, $occupiedRowCount) + $remaining;

        return min(24, max($positionSpan, $rowsForBudget));
    }

    public function render()
    {
        return view('livewire.d-o-form-dept2-grid');
    }

    private function appendDept2ItemAtRow(Item $item, int $rowIndex): void
    {
        [$rowToItemMap] = $this->buildCurrentRowMaps();
        if (isset($rowToItemMap[$rowIndex])) {
            toastr()->warning('This row already has a line. Clear it or use another row.');

            return;
        }

        if (count($this->stackedItems) >= 24) {
            toastr()->error('⚠️ PAGE LIMIT REACHED: Cannot add item.');

            return;
        }

        unset($this->freeFormTextRows[$rowIndex]);

        $defaultUm = ($item->um ?? 'UNIT') === 'UNIT' ? 'UNITS' : ($item->um ?? 'UNIT');
        $unitPrice = floatval($item->cash_price ?? 0);
        $qty = 1.0;

        $this->stackedItems[] = [
            'item' => [
                'id' => $item->id,
                'item_code' => $item->item_code,
                'item_name' => $item->item_name,
                'qty' => $item->qty,
                'cost' => $item->cost,
                'cust_price' => $item->cust_price,
                'term_price' => $item->term_price,
                'cash_price' => $item->cash_price,
                'latest_do_price' => 0,
                'latest_do_date' => null,
                'details' => '',
                'memo' => $item->memo ?? '',
                'um' => $item->um ?? 'UNIT',
            ],
            'details_lines' => [],
            'custom_um' => $defaultUm,
            'item_qty' => $qty,
            'pricing_tier' => '',
            'item_unit_price' => $unitPrice,
            'amount' => $qty * $unitPrice,
            'more_description' => null,
            'custom_item_name' => $item->item_name,
            'price_manually_modified' => true,
            'original_row_index' => $rowIndex,
        ];

        $this->syncToParent();
        $this->dispatch('focus-qty-row', ['rowIndex' => $rowIndex]);
    }

    private function findItemByCodeSearch(string $term): ?Item
    {
        $term = trim($term);
        if ($term === '') {
            return null;
        }

        $columns = [
            'id', 'item_code', 'item_name', 'um', 'qty', 'cost',
            'cust_price', 'term_price', 'cash_price', 'memo',
        ];

        return Item::query()
            ->select($columns)
            ->where('item_code', $term)
            ->first();
    }

    private function buildCurrentRowMaps(): array
    {
        $rowToItemMap = [];
        $regularItemIndex = 0;

        foreach ($this->stackedItems as $idx => $item) {
            if (isset($item['original_row_index']) && $item['original_row_index'] !== null) {
                $originalRow = (int) $item['original_row_index'];
                if ($originalRow < 24) {
                    $rowToItemMap[$originalRow] = $idx;
                } else {
                    while (isset($rowToItemMap[$regularItemIndex]) && $regularItemIndex < 24) {
                        $regularItemIndex++;
                    }
                    if ($regularItemIndex < 24) {
                        $rowToItemMap[$regularItemIndex] = $idx;
                        $regularItemIndex++;
                    }
                }
            } else {
                while (isset($rowToItemMap[$regularItemIndex]) && $regularItemIndex < 24) {
                    $regularItemIndex++;
                }
                if ($regularItemIndex < 24) {
                    $rowToItemMap[$regularItemIndex] = $idx;
                    $regularItemIndex++;
                }
            }
        }

        return [$rowToItemMap];
    }

    private function syncToParent(): void
    {
        $linesTotal = 0.0;
        foreach ($this->stackedItems as $stackedItem) {
            $linesTotal += (float) ($stackedItem['amount'] ?? 0);
        }

        $this->dispatch(
            'dept2-grid-sync',
            stackedItems: $this->stackedItems,
            freeFormTextRows: $this->freeFormTextRows,
            linesTotalAmount: $linesTotal,
        )->to(DOForm::class);
    }
}
