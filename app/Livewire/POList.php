<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PurchaseOrder;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Title('UR | Purchase Order List')]
class POList extends Component
{
    use WithPagination;

    public $poSearchTerm = null;
    public $filterSupplierId = null;
    public $startDate = null;
    public $endDate = null;
    public $statusFilter='';

    public function updatingPOSearchTerm()
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

    public function updatingStatusFilter()
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

    public function mount($supplierId = null)
    { 
        $this->filterSupplierId = $supplierId;   
    }

    public function clearFilters()
    { 
        $this->reset([
            'poSearchTerm', 
            'filterSupplierId',
            'statusFilter',
            'startDate', 
            'endDate'
        ]);
    }

    public function render()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->hasRole('Admin');
        
        $query = PurchaseOrder::with(['supplier', 'user', 'updatedBy'])
            ->when(!$isAdmin, function($q) use ($user) {
                // Non-admins only see their own records
                return $q->where('user_id', $user->id);
            })
            ->when($this->filterSupplierId, function($q) {
                return $q->where('sup_id', $this->filterSupplierId);
            })
            ->when($this->poSearchTerm, function($q) {
              return  $q->where(function($query) {
                    $query->where('po_num', 'like', '%' . $this->poSearchTerm . '%')
                      ->orWhereHas('supplier', function($subQuery) {
                          $subQuery->where('sup_name', 'like', '%' . $this->poSearchTerm . '%');
                      });
                });
            })
            ->when($this->startDate && $this->endDate, function($q) {
               return $q->whereBetween('created_at', [
                    Carbon::parse($this->startDate)->startOfDay(), 
                    Carbon::parse($this->endDate)->endOfDay()
                ]);
            })->when($this->statusFilter, function ($q) {
                return $q->where('status', $this->statusFilter);
            })
            ->where('po_num', '!=', 'PO0000000000');
    
        $purchase_orders = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        $filteredSupplier = $this->filterSupplierId
        ? \App\Models\Supplier::findOrFail($this->filterSupplierId) 
        : null;

        $statusOptions = PurchaseOrder::distinct('status')->pluck('status');

        $countQuery = PurchaseOrder::query()
            ->where('po_num', '!=', 'PO0000000000');
        if (!$isAdmin) {
            $countQuery->where('user_id', $user->id);
        }
        if ($this->filterSupplierId) {
            $countQuery->where('sup_id', $this->filterSupplierId);
        }
        $purchase_order_count = $countQuery->count();
    
        return view('livewire.p-o-list', [
            'purchase_orders' => $purchase_orders,
            'purchase_order_count' => $purchase_order_count,
            'filteredSupplier' => $filteredSupplier,
            'statusOptions' => $statusOptions
        ])->layout('layouts.app');
    }
}
