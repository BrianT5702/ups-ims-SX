<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Group;
use App\Rules\UniqueInCurrentDatabase;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

#[Title('UR | Manage Group')]
class GroupList extends Component
{
    use WithPagination; // Use WithPagination instead of just Pagination

    public $group_name;

    public $groupSearchTerm = null;
    public $activePageNumber = 1;

    public $sortColumn = 'group_name';
    public $sortOrder = 'asc';

    public function addGroup()
    {
        $this->validate([
            'group_name' => ['required', 'min:3', 'max:50', new UniqueInCurrentDatabase('groups', 'group_name')],
        ], [
            'group_name.required' => 'The group name field is required.',
            'group_name.min' => 'The group name must be at least 3 characters.',
            'group_name.max' => 'The group name may not be greater than 50 characters.',
            'group_name.unique' => 'This group name is already taken. Please enter a different one.',
        ]);

        try {
            Group::create([
                'group_name' => $this->group_name,
            ]);

            $this->group_name = '';
            toastr()->success('Group added successfully');
        } catch (\Exception $e) {
            toastr()->error('An error occurred while adding the group: ' . $e->getMessage());
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

    public function fetchGroups(){
        return Group::where('group_name', 'like', '%'. $this->groupSearchTerm. '%')->
        orderBy($this->sortColumn, $this->sortOrder)->
        paginate(8); 
    }

    public function render() {
        $groups = $this->fetchGroups();
        return view('livewire.group-list', compact('groups'))->layout('layouts.app');
    }

    public function deleteGroup(Group $group){


        if($group){
            if ($group->items()->exists()) {
                toastr()->error('This group cannot be deleted because it associated with item(s).');
                return;
            }
            try{
                $group->delete();
                toastr()->success('Group deleted successfully');
            }catch(\Exception $e){
                toastr()->error('An error occurred while deleting the group'. $e->getMessage());
            }
        }


        $groups = $this->fetchGroups();

        if($groups->isEmpty() && $this->activePageNumber > 1){
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

