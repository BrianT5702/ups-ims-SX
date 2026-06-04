<?php

namespace App\Livewire;

use App\Models\Item;
use Livewire\Attributes\On;
use Livewire\Component;

class DOItemPicker extends Component
{
    public bool $showModal = false;
    public ?int $rowIndex = null;
    public string $searchMode = 'code';
    public string $searchTerm = '';

    /** @var \Illuminate\Database\Eloquent\Collection<int, Item>|array */
    public $results = [];

    #[On('do-item-picker-open')]
    public function open(int $rowIndex, string $mode = 'code'): void
    {
        if (!in_array($mode, ['code', 'name'], true)) {
            $mode = 'code';
        }

        $this->rowIndex = $rowIndex;
        $this->searchMode = $mode;
        $this->searchTerm = '';
        $this->results = [];
        $this->showModal = true;
        $this->js('setTimeout(() => document.getElementById("do-item-picker-search")?.focus(), 10)');
    }

    #[On('do-item-picker-close')]
    public function close(): void
    {
        $this->showModal = false;
        $this->rowIndex = null;
        $this->searchTerm = '';
        $this->results = [];
    }

    public function updatedSearchTerm(): void
    {
        if (!$this->showModal) {
            return;
        }

        $term = trim((string) $this->searchTerm);
        if ($term === '') {
            $this->results = [];

            return;
        }

        $query = Item::query()->select(['id', 'item_code', 'item_name', 'qty', 'um', 'cash_price']);
        $escapedTerm = addcslashes($term, '\%_');
        $isFractionSearch = preg_match('/\d+\s*\/\s*\d+/', $term) === 1;
        $termLength = mb_strlen($term);

        if ($this->searchMode === 'code') {
            $results = $query
                ->where('item_code', 'like', $escapedTerm . '%')
                ->orderBy('item_code')
                ->orderBy('id')
                ->limit($isFractionSearch ? 220 : ($termLength <= 2 ? 60 : 120))
                ->get();
        } else {
            $results = $query
                ->where(function ($nameQuery) use ($escapedTerm) {
                    $nameQuery->where('item_name', 'like', $escapedTerm . '%')
                        ->orWhere('item_name', 'like', '% ' . $escapedTerm . '%')
                        ->orWhere('item_name', 'like', '%/' . $escapedTerm . '%')
                        ->orWhere('item_name', 'like', '%-' . $escapedTerm . '%');
                })
                ->orderByRaw(
                    "CASE WHEN item_name LIKE ? THEN 0 WHEN item_name LIKE ? THEN 1 ELSE 2 END",
                    [$escapedTerm . '%', '% ' . $escapedTerm . '%']
                )
                ->orderBy('item_name')
                ->orderBy('id')
                ->limit($isFractionSearch ? 220 : ($termLength <= 2 ? 60 : 120))
                ->get();
        }

        $this->results = $results;
    }

    public function selectItem(int $itemId): void
    {
        if ($this->rowIndex === null) {
            return;
        }

        $this->dispatch('do-item-picker-item-selected', itemId: $itemId, rowIndex: $this->rowIndex);
        $this->close();
    }

    public function render()
    {
        return view('livewire.d-o-item-picker');
    }
}

