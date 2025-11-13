<?php

namespace App\Livewire\ChemicalForm;

use Livewire\Component;
use App\Models\IncomingQualityControl;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

#[Title('UR | Incoming Quality Control')]
class IncomingQualityControlForm extends Component
{
    use WithPagination;

    public $do_num;
    public $che_code;
    public $date_arrived;
    public $qty;
    public $expiry_date;

    public $searchTerm = null;
    public $startDate = null;
    public $endDate = null;
    public $activePageNumber = 1;

    // Inline edit state
    public $editingId = null;
    public $edit_do_num;
    public $edit_che_code;
    public $edit_date_arrived;
    public $edit_qty;
    public $edit_expiry_date;

    public function mount()
    {
        $this->date_arrived = date('Y-m-d'); // Set default date to today for form input
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

    public function addIncomingQC()
    {
        $this->validate([
            'do_num' => 'required|max:50',
            'che_code' => 'required|max:50',
            'date_arrived' => 'required|date',
            'qty' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date|after:date_arrived',
        ], [
            'do_num.required' => 'The DO number field is required.',
            'che_code.required' => 'The chemical code field is required.',
            'date_arrived.required' => 'The date arrived field is required.',
            'qty.required' => 'The quantity field is required.',
            'qty.integer' => 'The quantity must be a whole number.',
            'qty.min' => 'The quantity must be at least 1.',
            'expiry_date.after' => 'The expiry date must be after the arrival date.',
        ]);

        try {
            IncomingQualityControl::create([
                'do_num' => $this->do_num,
                'che_code' => $this->che_code,
                'date_arrived' => $this->date_arrived,
                'qty' => $this->qty,

                'expiry_date' => $this->expiry_date ?: null,
                'user_id' => Auth::id(),
            ]);

            $this->resetFormFields();
            toastr()->success('Quality Control record added successfully');
        } catch (\Exception $e) {
            toastr()->error('An error occurred while adding the record: ' . $e->getMessage());
        }
    }

    public function resetFormFields()
    {
        $this->do_num = '';
        $this->che_code = '';
        $this->date_arrived = date('Y-m-d'); // Reset to today's date
        $this->qty = '';
        $this->expiry_date = '';
    }

    public function startEdit($id)
    {
        $record = IncomingQualityControl::find($id);
        if (!$record) {
            toastr()->error('Record not found');
            return;
        }
        $this->editingId = $id;
        $this->edit_do_num = $record->do_num;
        $this->edit_che_code = $record->che_code;
        $this->edit_date_arrived = $record->date_arrived;
        $this->edit_qty = $record->qty;
        $this->edit_expiry_date = $record->expiry_date;
    }

    public function cancelEdit()
    {
        $this->editingId = null;
        $this->edit_do_num = null;
        $this->edit_che_code = null;
        $this->edit_date_arrived = null;
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
            'edit_che_code' => 'required|max:50',
            'edit_date_arrived' => 'required|date',
            'edit_qty' => 'required|integer|min:1',
            'edit_expiry_date' => 'nullable|date|after:edit_date_arrived',
        ]);

        try {
            $record = IncomingQualityControl::findOrFail($id);
            $record->update([
                'do_num' => $this->edit_do_num,
                'che_code' => $this->edit_che_code,
                'date_arrived' => $this->edit_date_arrived,
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
            $record = IncomingQualityControl::findOrFail($id);
            $record->delete();
            toastr()->success('Record deleted successfully');
            $this->resetPage();
        } catch (\Exception $e) {
            toastr()->error('Failed to delete record: ' . $e->getMessage());
        }
    }

    public function fetchIncomingQCs()
    {
        $query = IncomingQualityControl::query();
        
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('do_num', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('che_code', 'like', '%' . $this->searchTerm . '%');
            });
        }
        
        if ($this->startDate) {
            $query->whereDate('date_arrived', '>=', $this->startDate);
        }
        
        if ($this->endDate) {
            $query->whereDate('date_arrived', '<=', $this->endDate);
        }
        
        $query->orderBy('created_at', 'desc');
        
        return $query->with('user')->paginate(10);
    }

    public function render() 
    {
        $incomingQCs = $this->fetchIncomingQCs();
        
        return view('livewire.chemical-form.incoming-quality-control-form', [
            'incomingQCs' => $incomingQCs,
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