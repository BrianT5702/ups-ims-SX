<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DeliveryOrder;
use App\Services\DeliveryOrderListQuery;
use App\Support\DdMmYyyyInput;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Support\TenantUser;
use Livewire\Attributes\Title;

#[Title('UR | Delivery Order List')]
class DOList extends Component
{
    use WithPagination;

    public $doSearchTerm = null;
    public $filterCustomerId = null;
    public $startDate = null;
    public $endDate = null;
    public $startDateInput = null;
    public $endDateInput = null;

    public function updatingDOSearchTerm()
    {
        $this->resetPage();
    }

    public function applyDateFilter()
    {
        $start = DdMmYyyyInput::toIso($this->startDateInput);
        $end = DdMmYyyyInput::toIso($this->endDateInput);

        if ($this->startDateInput && !$start) {
            toastr()->error('Invalid from date. Use dd/mm/yyyy');

            return;
        }

        if ($this->endDateInput && !$end) {
            toastr()->error('Invalid to date. Use dd/mm/yyyy');

            return;
        }

        if ($start && $end && $start > $end) {
            toastr()->error('From date cannot be later than to date');

            return;
        }

        $this->startDate = $start;
        $this->endDate = $end;
        $this->startDateInput = DdMmYyyyInput::toDisplay($start);
        $this->endDateInput = DdMmYyyyInput::toDisplay($end);
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset([
            'doSearchTerm',
            'filterCustomerId',
            'startDate',
            'endDate',
            'startDateInput',
            'endDateInput',
        ]);
        $this->resetPage();
    }

    public function mount($customerId = null)
    {
        $this->filterCustomerId = $customerId;
    }

    public function render()
    {
        $user = Auth::user();
        $isPrivileged = DeliveryOrderListQuery::isPrivilegedUser($user);

        $delivery_orders = DeliveryOrderListQuery::build(
            $user,
            $this->doSearchTerm,
            $this->filterCustomerId ? (int) $this->filterCustomerId : null,
            $this->startDate,
            $this->endDate,
        )->paginate(15);

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