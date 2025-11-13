<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Location;
use App\Models\Warehouse;
use App\Rules\UniqueInCurrentDatabase;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;

#[Title('UR | Warehouse & Location Management')]
class WarehouseLocation extends Component
{
    use WithPagination;
    
    // Location properties
    public $location_name = '';
    public $warehouse_id = '';
    public $showLocationForm = false;
    
    // Warehouse properties
    public $warehouse_name = '';
    public $showWarehouseForm = false;
    
    public $warehouses = [];
    public $filter_warehouse_id = '';

    // Location validation rules
    protected $locationRules = [
        'warehouse_id' => ['required', 'exists:warehouses,id'],
        'location_name' => ['required', 'string', 'min:3', 'max:20']
    ];

    // Warehouse validation rules
    protected function getWarehouseRules()
    {
        return [
            'warehouse_name' => ['required', 'string', 'min:3', 'max:50', new UniqueInCurrentDatabase('warehouses', 'warehouse_name')],
        ];
    }

    protected $messages = [
        'warehouse_id.required' => 'Please select a warehouse first.',
        'warehouse_id.exists' => 'The selected warehouse is invalid.',
        'location_name.required' => 'Location name is required.',
        'location_name.min' => 'Location name must be at least 3 characters.',
        'location_name.max' => 'Location name cannot exceed 20 characters.',
        'warehouse_name.required' => 'Warehouse name is required.',
        'warehouse_name.unique' => 'This warehouse name already exists.',
        'warehouse_name.min' => 'Warehouse name must be at least 3 characters.',
        'warehouse_name.max' => 'Warehouse name cannot exceed 50 characters.',
    ];

    public function updatedFilterWarehouseId()
    {
        $this->resetPage();
    }
    
    public function mount()
    {
        $this->loadWarehouses();
    }
    
    public function loadWarehouses()
    {
        $this->warehouses = Warehouse::orderBy('warehouse_name')->get();
    }
    
    public function toggleLocationForm()
    {
        $this->showLocationForm = !$this->showLocationForm;
        $this->reset(['location_name', 'warehouse_id']);
        $this->resetValidation();
    }
    
    public function toggleWarehouseForm()
    {
        $this->showWarehouseForm = !$this->showWarehouseForm;
        $this->reset(['warehouse_name']);
        $this->resetValidation();
    }

    public function deleteWarehouse($warehouseId)
    {
        $warehouse = Warehouse::find($warehouseId);
        
        if (!$warehouse) {
            toastr()->error('Warehouse not found.');
            return;
        }

        // Check if warehouse has any locations
        if ($warehouse->locations()->exists()) {
            toastr()->error('This warehouse cannot be deleted because it has associated locations.');
            return;
        }

        // Check if any locations have items
        if ($warehouse->locations()->whereHas('items')->exists()) {
            toastr()->error('This warehouse cannot be deleted because it has associated items.');
            return;
        }

        try {
            $warehouse->delete();
            $this->loadWarehouses();
            toastr()->success('Warehouse deleted successfully.');
        } catch (\Exception $e) {
            toastr()->error('An error occurred while deleting the warehouse.');
        }
    }
    
    public function updated($propertyName)
    {
        if ($propertyName === 'warehouse_name') {
            $this->validateOnly($propertyName, $this->getWarehouseRules());
        } elseif (in_array($propertyName, ['warehouse_id', 'location_name'])) {
            $this->validateOnly($propertyName, [
                'warehouse_id' => $this->locationRules['warehouse_id'],
                'location_name' => array_merge(
                    $this->locationRules['location_name'],
                    [Rule::unique('locations')->where(function ($query) {
                        return $query->where('warehouse_id', $this->warehouse_id);
                    })]
                )
            ]);
        }
    }
    
    public function addWarehouse()
    {
        $validatedData = $this->validate($this->getWarehouseRules());

        try {
            Warehouse::create([
                'warehouse_name' => $this->warehouse_name
            ]);
            
            $this->reset(['warehouse_name']);
            $this->showWarehouseForm = false;
            $this->loadWarehouses();
            
            toastr()->success('Warehouse added successfully');
            
        } catch (\Exception $e) {
            toastr()->error('An error occurred while adding the warehouse: ' . $e->getMessage());
        }
    }
    
    public function addLocation()
    {
        $validatedData = $this->validate([
            'warehouse_id' => $this->locationRules['warehouse_id'],
            'location_name' => array_merge(
                $this->locationRules['location_name'],
                [Rule::unique('locations')->where(function ($query) {
                    return $query->where('warehouse_id', $this->warehouse_id);
                })]
            )
        ]);

        try {
            Location::create([
                'location_name' => $this->location_name,
                'warehouse_id' => $this->warehouse_id,
                'position_x' => null,
                'position_y' => null,
            ]);

            $this->reset(['location_name', 'warehouse_id']);
            $this->showLocationForm = false;
            toastr()->success('Location added successfully');
            
            $this->dispatch('locationAdded');
            
        } catch (\Exception $e) {
            toastr()->error('An error occurred while adding the location: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Get filtered locations with pagination
        $filteredLocations = Location::with(['warehouse', 'items'])
            ->withCount('items')
            ->when($this->filter_warehouse_id !== '', function ($query) {
                $query->where('warehouse_id', $this->filter_warehouse_id);
            })
            ->orderBy('location_name')
            ->paginate(10);
    
        // Get warehouses with their locations count and total items count
        $warehousesList = Warehouse::withCount(['locations'])
            ->withCount(['items' => function($query) {
                $query->whereHas('location'); // Only count items that have a location
            }])
            ->orderBy('warehouse_name')
            ->paginate(10);
    
        return view('livewire.warehouse-location', [
            'warehousesList' => $warehousesList,
            'filteredLocations' => $filteredLocations,
        ])->layout('layouts.app');
    }
}