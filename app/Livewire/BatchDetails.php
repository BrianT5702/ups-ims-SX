<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BatchTracking;
use Livewire\Attributes\Title;

#[Title('UR | Batch Details')]
class BatchDetails extends Component
{
    use WithPagination;

    public $batchNum;
    public $totalItems;
    public $totalQuantity;

    public function mount($batchNum)
    {
        $this->batchNum = $batchNum;
        $this->calculateTotals();
    }

    private function calculateTotals()
    {
        $totals = BatchTracking::where('batch_num', $this->batchNum)
            ->selectRaw('COUNT(*) as total_items, SUM(quantity) as total_quantity')
            ->first();
            
        $this->totalItems = $totals->total_items;
        $this->totalQuantity = $totals->total_quantity;
    }

    public function fetchBatchItems()
    {
        return BatchTracking::where('batch_num', $this->batchNum)
            ->with(['item', 'receivedBy', 'purchaseOrder'])
            ->orderBy('id', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        $batchItems = $this->fetchBatchItems();

        $importQtyByItemId = [];
        if ($batchItems->isNotEmpty()) {
            $itemIds = $batchItems->pluck('item_id')->filter()->unique()->values();
            $importQtyByItemId = BatchTracking::query()
                ->where('batch_num', BatchTracking::IMPORT_BATCH_NUM)
                ->whereIn('item_id', $itemIds)
                ->orderBy('id', 'asc')
                ->get()
                ->groupBy('item_id')
                ->map(fn ($rows) => (float) $rows->sum(
                    fn ($row) => $row->original_quantity ?? $row->quantity ?? 0
                ))
                ->all();
        }

        return view('livewire.batch-details', [
            'batchItems' => $batchItems,
            'importQtyByItemId' => $importQtyByItemId,
        ])->layout('layouts.app');
    }
} 