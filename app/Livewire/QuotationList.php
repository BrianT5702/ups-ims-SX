<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

#[Title('UR | Quotation List')]
class QuotationList extends Component
{
    use WithPagination;

    public $quotationSearchTerm = null;
    public $filterCustomerId = null;
    public $startDate = null;
    public $endDate = null;

    public function updatingQuotationSearchTerm() { $this->resetPage(); }
    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }

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

    public function clearFilters()
    { 
        $this->reset([
            'quotationSearchTerm', 
            'filterCustomerId',
            'startDate', 
            'endDate'
        ]);
    }

    public function render()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->hasRole('Admin');
        
        $query = Quotation::with(['customer', 'user', 'updatedBy'])
            ->when(!$isAdmin, function($q) use ($user) {
                // Non-admins only see their own records
                return $q->where('user_id', $user->id);
            })
            ->when($this->filterCustomerId, fn($q) => $q->where('cust_id', $this->filterCustomerId))
            ->when($this->quotationSearchTerm, function($q){
                return $q->where(function($sub){
                    $sub->where('quotation_num', 'like', '%' . $this->quotationSearchTerm . '%')
                        ->orWhereHas('customer', function($subQuery){
                            $subQuery->where('cust_name', 'like', '%' . $this->quotationSearchTerm . '%');
                        });
                });
            })
            ->when($this->startDate && $this->endDate, function($q){
                return $q->whereBetween('created_at', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay()
                ]);
            });

        $quotations = $query->orderBy('created_at', 'desc')->paginate(10);

        $filteredCustomer = $this->filterCustomerId ? \App\Models\Customer::find($this->filterCustomerId) : null;
        
        $countQuery = Quotation::query();
        if (!$isAdmin) {
            $countQuery->where('user_id', $user->id);
        }
        if ($this->filterCustomerId) {
            $countQuery->where('cust_id', $this->filterCustomerId);
        }
        $quotation_count = $countQuery->count();

        return view('livewire.quotation-list', [
            'quotations' => $quotations,
            'quotation_count' => $quotation_count,
            'filteredCustomer' => $filteredCustomer,
        ])->layout('layouts.app');
    }
}


