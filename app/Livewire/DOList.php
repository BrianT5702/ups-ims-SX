<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DeliveryOrder;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Support\TenantUser;
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
        $this->endDate = $this->defaultEndDateForGmtPlus8();
    }

    /**
     * "To date" filter default: today in GMT+8 (business locale).
     */
    private function defaultEndDateForGmtPlus8(): string
    {
        return Carbon::now('Asia/Singapore')->format('Y-m-d');
    }

    public function clearFilters()
    {
        $this->reset([
            'doSearchTerm',
            'filterCustomerId',
            'startDate',
            'endDate',
        ]);
        $this->endDate = $this->defaultEndDateForGmtPlus8();
    }

    public function render()
    {
        $user = Auth::user();
        $isPrivileged = $user && (
            $user->hasRole('Admin')
            || $user->hasRole('Super Admin')
            || $user->hasRole('Department1')
            || $user->hasRole('Department 1')
            || $user->hasRole('Department2')
            || $user->hasRole('Department 2')
            || $user->hasRole('Department2 Admin')
            || $user->hasRole('Department 2 Admin')
        );
        
        $query = DeliveryOrder::with(['customer', 'user', 'updatedBy'])
            ->withCount('items')
            ->when(!$isPrivileged, function ($q) use ($user) {
                return $q->where('user_id', TenantUser::resolveId($user));
            })
            ->when($this->filterCustomerId, function($q) {
                return $q->where('cust_id', $this->filterCustomerId);
            })
            ->when($this->doSearchTerm, function($q) {
              return  $q->where(function($query) {
                    $query->where('do_num', 'like', '%' . $this->doSearchTerm . '%')
                      ->orWhereHas('customer', function($subQuery) {
                          $subQuery->where('cust_name', 'like', '%' . $this->doSearchTerm . '%')
                              ->orWhere('account', 'like', '%' . $this->doSearchTerm . '%');
                      });
                });
            })
            ->when($this->startDate && $this->endDate, function($q) {
                return $q->whereBetween('date', [
                    Carbon::parse($this->startDate)->toDateString(),
                    Carbon::parse($this->endDate)->toDateString(),
                ]);
            });

        $delivery_orders = $query->orderByDesc('date')
            ->orderByDesc('created_at')
            ->paginate(15);

        $filteredCustomer = $this->filterCustomerId
        ? \App\Models\Customer::findOrFail($this->filterCustomerId) 
        : null;

        $countQuery = DeliveryOrder::query();
        if (!$isPrivileged) {
            $countQuery->where('user_id', TenantUser::resolveId($user));
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