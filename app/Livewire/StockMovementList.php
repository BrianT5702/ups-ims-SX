<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\StockMovement;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Title('UR | Stock Movement List')]
#[Layout('layouts.app')]
class StockMovementList extends Component
{
    use WithPagination;

    public $search = '';
    public $movementTypeFilter = '';
    public $startDate = null;
    public $endDate = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'movementTypeFilter' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingMovementTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function updatedStartDate($value)
    {
        if ($this->endDate && $value > $this->endDate) {
            $this->endDate = $value;
        }
    }

    public function updatedEndDate($value)
    {
        if ($this->startDate && $value < $this->startDate) {
            $this->endDate = $this->startDate;
            toastr()->error('End date cannot be earlier than start date');
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'movementTypeFilter', 'startDate', 'endDate']);
        $this->resetPage();
    }

    public function deleteStockMovement($id)
    {
        try {
            $stockMovement = StockMovement::findOrFail($id);
            
            // Delete associated stock movement items first
            $stockMovement->items()->delete();
            
            // Delete the stock movement
            $stockMovement->delete();
            
            toastr()->success('Stock movement deleted successfully.');
            
        } catch (\Exception $e) {
            toastr()->error('Error deleting stock movement: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = StockMovement::with(['user', 'items.item'])
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('reference_no', 'like', '%' . $this->search . '%')
                  ->orWhere('remarks', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function ($userQuery) {
                      $userQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->movementTypeFilter) {
            $query->where('movement_type', $this->movementTypeFilter);
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('movement_date', [
                Carbon::parse($this->startDate)->startOfDay(), 
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        }

        $stockMovements = $query->paginate(10);

        return view('livewire.stock-movement-list', [
            'stockMovements' => $stockMovements
        ]);
    }
}
