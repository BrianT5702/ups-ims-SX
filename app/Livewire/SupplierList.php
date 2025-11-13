<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Supplier;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

#[Title('UR | Supplier List')]

class SupplierList extends Component
{
    use WithPagination;

    public $supplierSearchTerm = null;
    public $activePageNumber = 1;

    public $sortColumn = 'sup_name';
    public $sortOrder = 'asc';

    public function sortBy($columnName){
        if($this->sortColumn === $columnName){
            $this->sortOrder = $this->sortOrder === 'asc' ? 'desc' : 'asc';
        }else{
            $this->sortColumn = $columnName;
            $this->sortOrder = 'asc';
        }
    }

    public function fetchSuppliers(){
        return Supplier::where('sup_name', 'like', '%'. $this->supplierSearchTerm. '%')->
        orderBy($this->sortColumn, $this->sortOrder)->
        paginate(8); 
    }

    public function render() {
        $suppliers = $this->fetchSuppliers();
        return view('livewire.supplier-list', compact('suppliers'))->layout('layouts.app');
    }

    public function deleteSupplier(Supplier $supplier){

        if($supplier){
            if ($supplier->items()->exists()) {
                toastr()->error('This supplier cannot be deleted because it associated with item(s).');
                return;
            }

            if ($supplier->purchaseOrders()->exists()) {
                toastr()->error('This supplier cannot be deleted because it has associated Purchase Orders.');
                return;
            }

            try{
                $supplier->delete();
                toastr()->success('Supplier deleted successfully');
            }catch(\Exception $e){
                toastr()->error('An error occurred while deleting the supplier'. $e->getMessage());
            }
        }

        $suppliers = $this->fetchSuppliers();

        if($suppliers->isEmpty() && $this->activePageNumber > 1){
            $this->gotoPage($this->activePageNumber - 1);
        }

        else{
            $this->gotoPage($this->activePageNumber);
        }
    }

    public function updatingPage($pageNumber){
        $this->activePageNumber = $pageNumber;
    }

    public function showSupplierPO($supplierId)
    {
        return redirect()->route('purchase-order', ['supplierId' => $supplierId]);
    }
}
