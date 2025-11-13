<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use App\Models\DeliveryOrder;

#[Title('UR | User List')]

class UserList extends Component
{
    use WithPagination;
    public $userSearchTerm = null;
    public $activePageNumber = 1;

    public $sortColumn = 'name';
    public $sortOrder = 'asc';

    public function sortBy($columnName){
        if($this->sortColumn === $columnName){
            $this->sortOrder = $this->sortOrder === 'asc' ? 'desc' : 'asc';
        }else{
            $this->sortColumn = $columnName;
            $this->sortOrder = 'asc';
        }
    }

    public function fetchUsers()
    {
        return User::with('roles') // Eager load roles
            ->where('name', 'like', '%'. $this->userSearchTerm. '%')
            ->orWhere('username', 'like', '%'. $this->userSearchTerm. '%')
            ->orderBy($this->sortColumn, $this->sortOrder)
            ->paginate(8); 
    }
    

    public function render() {
        $users = $this->fetchUsers();
        return view('livewire.user-list', compact('users'))->layout('layouts.app');
    }

    public function deleteUser(User $user){

        $loggedInUserId = Auth::id();

        if ($user->id === $loggedInUserId) {
            toastr()->error('You cannot delete your own account.');
            return;
        }

        if (
            $user->purchaseOrders()->exists() ||
            $user->deliveryOrders()->exists() ||
            DeliveryOrder::where('salesman_id', $user->id)->exists()
        ) {
            toastr()->error('Cannot delete user because they have associated Purchase Orders, Delivery Orders, or are assigned as a Salesman in Delivery Orders.');
            return;
        }

        if($user){
            try{
                $user->delete();
                toastr()->success('User deleted successfully');
            }catch(\Exception $e){
                toastr()->error('An error occurred while deleting the user'. $e->getMessage());
            }
        }

        // return $this->redirect('/users', navigate: true);
        //Redirect to active page

        $users = $this->fetchUsers();

        if($users->isEmpty() && $this->activePageNumber > 1){
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
