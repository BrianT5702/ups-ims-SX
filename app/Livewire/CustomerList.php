<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

#[Title('UR | Customer List')]

class CustomerList extends Component
{
    use WithPagination; // Use WithPagination instead of just Pagination

    public $customerSearchTerm = null;
    public $activePageNumber = 1;

    public $sortColumn = 'cust_name';
    public $sortOrder = 'asc';

    public function sortBy($columnName){
        if($this->sortColumn === $columnName){
            $this->sortOrder = $this->sortOrder === 'asc' ? 'desc' : 'asc';
        }else{
            $this->sortColumn = $columnName;
            $this->sortOrder = 'asc';
        }
    }

    public function fetchCustomers(){
        return Customer::where('cust_name', 'like', '%'. $this->customerSearchTerm. '%')->
        orderBy($this->sortColumn, $this->sortOrder)->
        paginate(8); 
    }

    public function render() {
        $customers = $this->fetchCustomers();
        return view('livewire.customer-list', compact('customers'))->layout('layouts.app');
    }

    public function deleteCustomer(Customer $customer){

        if($customer){
            if ($customer->deliveryOrders()->exists()) {
                toastr()->error('This customer cannot be deleted because it associated with delivery order(s).');
                return;
            }
            try{
                $customer->delete();
                toastr()->success('Customer deleted successfully');
            }catch(\Exception $e){
                toastr()->error('An error occurred while deleting the customer'. $e->getMessage());
            }
        } 

        // return $this->redirect('/customers', navigate: true);
        //Redirect to active page

        $customers = $this->fetchCustomers();

        if($customers->isEmpty() && $this->activePageNumber > 1){
            $this->gotoPage($this->activePageNumber - 1);
        }

        else{
            $this->gotoPage($this->activePageNumber);
        }
    }

    public function updatingPage($pageNumber){
        $this->activePageNumber = $pageNumber;
    }

    public function showCustomerDO($customerId)
    {
        return redirect()->route('delivery-order', ['customerId' => $customerId]);
    }
}
