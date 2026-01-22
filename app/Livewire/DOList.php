<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DeliveryOrder;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Title('UR | Delivery Order List')]
class DOList extends Component
{
    use WithPagination;

    public $doSearchTerm = null;
    public $filterCustomerId = null;
    public $startDate = null;
    public $endDate = null;

    public function updatingDOSearchTerm()
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

    public function mount($customerId = null)
    { 
        $this->filterCustomerId = $customerId;   
    }

    public function clearFilters()
    { 
        $this->reset([
            'doSearchTerm', 
            'filterCustomerId',
            'startDate', 
            'endDate'
        ]);
    }

    public function render()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->hasRole('Admin');
        
        $query = DeliveryOrder::with(['customer', 'user'])
            ->when(!$isAdmin, function($q) use ($user) {
                // Non-admins only see their own records
                return $q->where('user_id', $user->id);
            })
            ->when($this->filterCustomerId, function($q) {
                return $q->where('cust_id', $this->filterCustomerId);
            })
            ->when($this->doSearchTerm, function($q) {
              return  $q->where(function($query) {
                    $query->where('do_num', 'like', '%' . $this->doSearchTerm . '%')
                      ->orWhereHas('customer', function($subQuery) {
                          $subQuery->where('cust_name', 'like', '%' . $this->doSearchTerm . '%');
                      });
                });
            })
            ->when($this->startDate && $this->endDate, function($q) {
               return $q->whereBetween('created_at', [
                    Carbon::parse($this->startDate)->startOfDay(), 
                    Carbon::parse($this->endDate)->endOfDay()
                ]);
            });
    
        $delivery_orders = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        $filteredCustomer = $this->filterCustomerId
        ? \App\Models\Customer::findOrFail($this->filterCustomerId) 
        : null;

        $countQuery = DeliveryOrder::query();
        if (!$isAdmin) {
            $countQuery->where('user_id', $user->id);
        }
        if ($this->filterCustomerId) {
            $countQuery->where('cust_id', $this->filterCustomerId);
        }
        $delivery_order_count = $countQuery->count();
    
        return view('livewire.d-o-list', [
            'delivery_orders' => $delivery_orders,
            'delivery_order_count' => $delivery_order_count,
            'filteredCustomer' => $filteredCustomer,
        ])->layout('layouts.app');
    }
}