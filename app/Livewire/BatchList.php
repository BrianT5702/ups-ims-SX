<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BatchTracking;
use Livewire\Attributes\Title;

#[Title('UR | Batch List')]
class BatchList extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap'; // Set pagination theme to bootstrap
    
    public $startDate = null;
    public $endDate = null;
    public $searchTerm = null;
    public $activePageNumber = 1;
    public $perPage = 8;

    public function mount()
    {
        // Empty mount method
    }

    public function updatedStartDate($value)
    {
        $this->resetPage(); // Reset to first page when filter changes
        
        if ($this->endDate && $value > $this->endDate) {
            $this->endDate = $value;
        }
    }

    public function updatedEndDate($value)
    {
        $this->resetPage(); // Reset to first page when filter changes
        
        if ($this->startDate && $value < $this->startDate) {
            $this->endDate = $this->startDate;
            toastr()->error('End date cannot be earlier than start date');
        }
    }
    
    public function updatedSearchTerm()
    {
        $this->resetPage(); // Reset to first page when search term changes
    }

    public function fetchBatches()
    {
        $query = BatchTracking::query()
            ->select('batch_num', 'received_date', 'received_by', 'po_id', 'created_at')
            ->with(['purchaseOrder', 'receivedBy'])
            ->orderBy('created_at', 'desc');

        if ($this->startDate) {
            $query->whereDate('received_date', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('received_date', '<=', $this->endDate);
        }

        if ($this->searchTerm) {
            $query->where('batch_num', 'like', '%' . $this->searchTerm . '%');
        }

        // Get unique batch numbers with their latest record
        $query->whereIn('id', function($subquery) {
            $subquery->selectRaw('MAX(id)')
                ->from('batch_trackings')
                ->groupBy('batch_num');
        });
        
        return $query->paginate($this->perPage);
    }

    public function clearFilters()
    {
        $this->startDate = null;
        $this->endDate = null;
        $this->searchTerm = null;
        $this->resetPage();
    }

    public function render()
    {
        $batches = $this->fetchBatches();
        
        return view('livewire.batch-list', [
            'batches' => $batches,
        ])->layout('layouts.app');
    }

    private function calculateTotals()
    {
        $allItems = BatchTracking::where('batch_num', $this->batchNum)->get();
        $this->totalItems = $allItems->count();
        $this->totalQuantity = $allItems->sum('quantity');
    }
}