<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Family;
use App\Rules\UniqueInCurrentDatabase;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

#[Title('UR | Manage Family')]
class FamilyList extends Component
{
    use WithPagination; // Use WithPagination instead of just Pagination

    public $family_name;

    public $familySearchTerm = null;
    public $activePageNumber = 1;

    public $sortColumn = 'family_name';
    public $sortOrder = 'asc';

    public function addFamily()
    {
        $this->validate([
            'family_name' => ['required', 'min:3', 'max:50', new UniqueInCurrentDatabase('families', 'family_name')],
        ], [
            'family_name.required' => 'The family name field is required.',
            'family_name.min' => 'The family name must be at least 3 characters.',
            'family_name.max' => 'The family name may not be greater than 50 characters.',
            'family_name.unique' => 'This family name is already taken. Please enter a different one.',
        ]);

        try {
            Family::create([
                'family_name' => $this->family_name,
            ]);

            $this->family_name = '';
            toastr()->success('Family added successfully');
        } catch (\Exception $e) {
            toastr()->error('An error occurred while adding the family: ' . $e->getMessage());
        }
    }

    public function sortBy($columnName){
        if($this->sortColumn === $columnName){
            $this->sortOrder = $this->sortOrder === 'asc' ? 'desc' : 'asc';
        }else{
            $this->sortColumn = $columnName;
            $this->sortOrder = 'asc';
        }
    }

    public function fetchFamilies(){
        return Family::where('family_name', 'like', '%'. $this->familySearchTerm. '%')->
        orderBy($this->sortColumn, $this->sortOrder)->
        paginate(8); 
    }

    public function render() {
        $families = $this->fetchFamilies();
        return view('livewire.family-list', compact('families'))->layout('layouts.app');
    }

    public function deleteFamily(Family $family){


        if($family){
            if ($family->items()->exists()) {
                toastr()->error('This family cannot be deleted because it associated with item(s).');
                return;
            }
            try{
                $family->delete();
                toastr()->success('Family deleted successfully');
            }catch(\Exception $e){
                toastr()->error('An error occurred while deleting the family'. $e->getMessage());
            }
        }


        $families = $this->fetchFamilies();

        if($families->isEmpty() && $this->activePageNumber > 1){
            $this->gotoPage($this->activePageNumber - 1);
        }

        else{
            $this->gotoPage($this->activePageNumber);
        }
    }

    public function updatingPage($pageNumber){
        $this->activePageNumber = $pageNumber;
    }
}
