<?php

namespace App\Livewire\ChemicalForm;

use Livewire\Component;
use App\Models\LoadingUnloading;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

#[Title('UR | Chemical Loading and Unloading')]
class LoadingUnloadingForm extends Component
{
    use WithPagination;

    public $selectedChemical = '';
    public $tank_id = '';
    public $date;
    public $start_time;
    public $stop_time;
    public $che_before;
    public $che_after;
    public $isFollowDO = false;

    public $searchTerm = null;
    public $startDate = null;
    public $endDate = null;
    public $activePageNumber = 1;
    public $filterChemical = '';

    // Inline edit state
    public $editingId = null;
    public $edit_tank_id;
    public $edit_che_code;
    public $edit_date;
    public $edit_start_time;
    public $edit_stop_time;
    public $edit_che_before;
    public $edit_che_after;
    public $edit_isFollowDO = false;

    // Chemical options
    public $chemicalOptions = [
        'Pentane' => ['label' => 'Pentane', 'code' => 'Pentane', 'tanks' => ['B05']],
        'PH1139' => ['label' => 'Polyol (PH1139)', 'code' => 'PH1139', 'tanks' => ['B01', 'B02']],
        'KH1250' => ['label' => 'Isocyanate (KH1250)', 'code' => 'KH1250', 'tanks' => ['B03', 'B04']]
    ];

    public function mount()
    {
        $this->date = date('Y-m-d');
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

    public function updatedSelectedChemical()
    {
        $this->tank_id = isset($this->chemicalOptions[$this->selectedChemical]) ? 
            $this->chemicalOptions[$this->selectedChemical]['tanks'][0] : '';
    }

    public function getTankOptions()
    {
        if (!$this->selectedChemical || !isset($this->chemicalOptions[$this->selectedChemical])) {
            return [];
        }
        
        return $this->chemicalOptions[$this->selectedChemical]['tanks'];
    }

    public function getChemicalCode()
    {
        return $this->selectedChemical;
    }

    public function addLoadingUnloading()
    {
        $this->validate([
            'selectedChemical' => 'required',
            'tank_id' => 'required',
            'date' => 'required|date',
            'start_time' => 'required',
            'stop_time' => 'required',
            'che_before' => 'required|numeric|min:0|max:100',
            'che_after' => 'required|numeric|min:0|max:100|gt:che_before',
        ], [
            'selectedChemical.required' => 'Please select a chemical.',
            'tank_id.required' => 'Please select a tank number.',
            'date.required' => 'The date field is required.',
            'start_time.required' => 'The start time is required.',
            'stop_time.required' => 'The stop time is required.',
            'che_before.required' => 'The chemical before % is required.',
            'che_after.required' => 'The chemical after % is required.',
            'che_before.numeric' => 'The chemical before % must be a number.',
            'che_after.numeric' => 'The chemical after % must be a number.',
            'che_before.min' => 'The chemical before % must be at least 0.',
            'che_after.min' => 'The chemical after % must be at least 0.',
            'che_before.max' => 'The chemical before % cannot exceed 100.',
            'che_after.max' => 'The chemical after % cannot exceed 100.',
            'che_after.gt' => 'The chemical after % must be greater than the before percentage.',
        ]);

        // Custom validation: start_time must be earlier than stop_time
        if (strtotime($this->start_time) >= strtotime($this->stop_time)) {
            $this->addError('start_time', 'The start time must be earlier than the end time.');
            return;
        }

        try {
            LoadingUnloading::create([
                'tank_id' => $this->tank_id,
                'che_code' => $this->selectedChemical,
                'date' => $this->date,
                'start_time' => $this->start_time,
                'stop_time' => $this->stop_time,
                'che_before' => $this->che_before,
                'che_after' => $this->che_after,
                'isFollowDO' => $this->isFollowDO,
                'user_id' => Auth::id(),
            ]);

            $this->resetFormFields();
            toastr()->success('Loading/Unloading record added successfully');
        } catch (\Exception $e) {
            toastr()->error('An error occurred: ' . $e->getMessage());
        }
    }

    public function resetFormFields()
    {
        $this->tank_id = '';
        $this->date = date('Y-m-d');
        $this->start_time = '';
        $this->stop_time = '';
        $this->che_before = '';
        $this->che_after = '';
        $this->isFollowDO = false;
    }

    public function startEdit($id)
    {
        $record = LoadingUnloading::find($id);
        if (!$record) {
            toastr()->error('Record not found');
            return;
        }
        $this->editingId = $id;
        $this->edit_tank_id = $record->tank_id;
        $this->edit_che_code = $record->che_code;
        $this->edit_date = $record->date;
        $this->edit_start_time = $record->start_time;
        $this->edit_stop_time = $record->stop_time;
        $this->edit_che_before = $record->che_before;
        $this->edit_che_after = $record->che_after;
        $this->edit_isFollowDO = (bool)$record->isFollowDO;
    }

    public function cancelEdit()
    {
        $this->editingId = null;
        $this->edit_tank_id = null;
        $this->edit_che_code = null;
        $this->edit_date = null;
        $this->edit_start_time = null;
        $this->edit_stop_time = null;
        $this->edit_che_before = null;
        $this->edit_che_after = null;
        $this->edit_isFollowDO = false;
    }

    public function saveEdit($id)
    {
        if ($this->editingId !== $id) {
            return;
        }

        $this->validate([
            'edit_tank_id' => 'required',
            'edit_che_code' => 'required',
            'edit_date' => 'required|date',
            'edit_start_time' => 'required',
            'edit_stop_time' => 'required',
            'edit_che_before' => 'required|numeric|min:0|max:100',
            'edit_che_after' => 'required|numeric|min:0|max:100|gt:edit_che_before',
        ]);

        if (strtotime($this->edit_start_time) >= strtotime($this->edit_stop_time)) {
            $this->addError('edit_start_time', 'The start time must be earlier than the end time.');
            return;
        }

        try {
            $record = LoadingUnloading::findOrFail($id);
            $record->update([
                'tank_id' => $this->edit_tank_id,
                'che_code' => $this->edit_che_code,
                'date' => $this->edit_date,
                'start_time' => $this->edit_start_time,
                'stop_time' => $this->edit_stop_time,
                'che_before' => $this->edit_che_before,
                'che_after' => $this->edit_che_after,
                'isFollowDO' => (bool)$this->edit_isFollowDO,
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
            $record = LoadingUnloading::findOrFail($id);
            $record->delete();
            toastr()->success('Record deleted successfully');
            $this->resetPage();
        } catch (\Exception $e) {
            toastr()->error('Failed to delete record: ' . $e->getMessage());
        }
    }

    public function fetchLoadingUnloadings()
    {
        $query = LoadingUnloading::query();
        
        if ($this->filterChemical) {
            $query->where('che_code', $this->filterChemical);
        }
        
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('tank_id', 'like', '%' . $this->searchTerm . '%')
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

    public function setFilterChemical($chemical = '')
    {
        $this->filterChemical = $chemical;
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->searchTerm = null;
        $this->filterChemical = '';
        $this->startDate = null;
        $this->endDate = null;
        $this->resetPage();
    }

    public function render() 
    {
        $loadingUnloadings = $this->fetchLoadingUnloadings();
        
        return view('livewire.chemical-form.loading-unloading-form', [
            'loadingUnloadings' => $loadingUnloadings,
            'tankOptions' => $this->getTankOptions(),
        ])->layout('layouts.app');
    }
}