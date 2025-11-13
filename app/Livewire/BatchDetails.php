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
        
        return view('livewire.batch-details', [
            'batchItems' => $batchItems,
        ])->layout('layouts.app');
    }
} 