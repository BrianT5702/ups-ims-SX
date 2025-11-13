<?php

namespace App\Livewire\ChemicalForm;

use Livewire\Component;
use App\Models\IBCChemical;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

#[Title('UR | IBC Tank Chemical Stock Level')]
class IBCChemicalForm extends Component
{
    use WithPagination;

    public $do_num;
    public $batch_no;
    public $date;
    public $che_code;
    public $qty;
    public $expiry_date;

    public $searchTerm = null;
    public $startDate = null;
    public $endDate = null;
    public $activePageNumber = 1;

    // Inline edit state
    public $editingId = null;
    public $edit_do_num;
    public $edit_batch_no;
    public $edit_date;
    public $edit_che_code;
    public $edit_qty;
    public $edit_expiry_date;

    public function mount()
    {
        $this->date = date('Y-m-d'); // Set default date to today for form input
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

    public function addChemicalStock()
    {
        $this->validate([
            'do_num' => 'required|max:50',
            'batch_no' => 'required|max:50',
            'date' => 'required|date',
            'che_code' => 'required|max:50',
            'qty' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date|after:date',
        ], [
            'do_num.required' => 'The DO number field is required.',
            'batch_no.required' => 'The batch number field is required.',
            'date.required' => 'The loading date field is required.',
            'che_code.required' => 'The chemical code field is required.',
            'qty.required' => 'The quantity field is required.',
            'qty.integer' => 'The quantity must be a whole number.',
            'qty.min' => 'The quantity must be at least 1.',
            'expiry_date.after' => 'The expiry date must be after the loading date.',
        ]);

        try {
            IBCChemical::create([
                'do_num' => $this->do_num,
                'batch_no' => $this->batch_no,
                'date' => $this->date,
                'che_code' => $this->che_code,
                'qty' => $this->qty,
                'expiry_date' => $this->expiry_date ?: null,
                'user_id' => Auth::id(),
            ]);

            $this->resetFormFields();
            toastr()->success('IBC Chemical Stock added successfully');
        } catch (\Exception $e) {
            toastr()->error('An error occurred while adding the stock: ' . $e->getMessage());
        }
    }

    public function resetFormFields()
    {
        $this->do_num = '';
        $this->batch_no = '';
        $this->date = date('Y-m-d'); // Reset to today's date
        $this->che_code = '';
        $this->qty = '';
        $this->expiry_date = '';
    }

    public function startEdit($id)
    {
        $record = IBCChemical::find($id);
        if (!$record) {
            toastr()->error('Record not found');
            return;
        }
        $this->editingId = $id;
        $this->edit_do_num = $record->do_num;
        $this->edit_batch_no = $record->batch_no;
        $this->edit_date = $record->date;
        $this->edit_che_code = $record->che_code;
        $this->edit_qty = $record->qty;
        $this->edit_expiry_date = $record->expiry_date;
    }

    public function cancelEdit()
    {
        $this->editingId = null;
        $this->edit_do_num = null;
        $this->edit_batch_no = null;
        $this->edit_date = null;
        $this->edit_che_code = null;
        $this->edit_qty = null;
        $this->edit_expiry_date = null;
    }

    public function saveEdit($id)
    {
        if ($this->editingId !== $id) {
            return;
        }

        $this->validate([
            'edit_do_num' => 'required|max:50',
            'edit_batch_no' => 'required|max:50',
            'edit_date' => 'required|date',
            'edit_che_code' => 'required|max:50',
            'edit_qty' => 'required|integer|min:1',
            'edit_expiry_date' => 'nullable|date|after:edit_date',
        ], [
            'edit_do_num.required' => 'The DO number field is required.',
            'edit_batch_no.required' => 'The batch number field is required.',
            'edit_date.required' => 'The loading date field is required.',
            'edit_che_code.required' => 'The chemical code field is required.',
            'edit_qty.required' => 'The quantity field is required.',
            'edit_qty.integer' => 'The quantity must be a whole number.',
            'edit_qty.min' => 'The quantity must be at least 1.',
            'edit_expiry_date.after' => 'The expiry date must be after the loading date.',
        ]);

        try {
            $record = IBCChemical::findOrFail($id);
            $record->update([
                'do_num' => $this->edit_do_num,
                'batch_no' => $this->edit_batch_no,
                'date' => $this->edit_date,
                'che_code' => $this->edit_che_code,
                'qty' => $this->edit_qty,
                'expiry_date' => $this->edit_expiry_date ?: null,
            ]);

            $this->cancelEdit();
            toastr()->success('Record updated successfully');
        } catch (\Exception $e) {
            toastr()->error('Failed to update record: ' . $e->getMessage());
        }
    }

    public function deleteRecord($id)
    {
        try {
            $record = IBCChemical::findOrFail($id);
            $record->delete();
            toastr()->success('Record deleted successfully');
            // If current page becomes empty after delete, refresh page
            $this->resetPage();
        } catch (\Exception $e) {
            toastr()->error('Failed to delete record: ' . $e->getMessage());
        }
    }

    public function fetchChemicalStocks()
    {
        $query = IBCChemical::query();
        
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('do_num', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('batch_no', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('che_code', 'like', '%' . $this->searchTerm . '%');
            });
        }
        
        if ($this->startDate) {
            $query->whereDate('date', '>=', $this->startDate);
        }
        
        if ($this->endDate) {
            $query->whereDate('date', '<=', $this->endDate);
        }
        
        $query->orderBy('created_at', 'desc');
        
        return $query->with('user')->paginate(10);
    }

    public function render() 
    {
        $chemicalStocks = $this->fetchChemicalStocks();
        
        return view('livewire.chemical-form.i-b-c-chemical-form', [
            'chemicalStocks' => $chemicalStocks,
        ])->layout('layouts.app');
    }

    public function clearFilters()
    {
        $this->searchTerm = null;
        $this->startDate = null; // Remove default date
        $this->endDate = null; // Remove default date
        $this->resetPage();
    }
}