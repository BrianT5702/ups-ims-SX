<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\Location;
use App\Models\Warehouse;
use App\Models\Item;

#[Title('UR | Manage Location')]
class LocationMap extends Component
{
    use WithPagination;

    public $location_name;
    public $locationSearchTerm = null;
    public $itemSearchTerm = '';
    public $selectedWarehouse = null;
    public $activePageNumber = 1;
    public $sortColumn = 'location_name';
    public $sortOrder = 'asc';
    public $isEditMode = false;
    public $unassignedLocations = [];
    public $warehouses = [];
    public $unassignedCounts = [];
    public $searchResults = [];
    public $selectedItem = null;
    public $itemLocations = [];
    public $deletedLocationIds = []; 

    protected $listeners = ['updateLocationPosition'];

    public function mount()
    {
        $this->selectedWarehouse = session('selectedWarehouse', Warehouse::first()->id ?? '');
    
        $this->warehouses = Warehouse::all();
        $this->calculateUnassignedCounts();
        $this->refreshUnassignedLocations();
        $this->clearItemSelection();
    }

    public function updatedItemSearchTerm()
    {
        // If the user starts typing, clear the selected item so search results show
        if ($this->selectedItem) {
            $this->selectedItem = null;
            $this->itemLocations = [];
        }
        $this->searchItems();
    }

    public function searchItems()
    {
        if (strlen($this->itemSearchTerm) >= 2) {
            $query = Item::query()
                ->where(function($q) {
                    $q->where('item_name', 'like', '%' . $this->itemSearchTerm . '%')
                      ->orWhere('item_code', 'like', '%' . $this->itemSearchTerm . '%');
                });

            // Remove warehouse filter when searching
            $this->searchResults = $query->limit(50)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_name' => $item->item_name,
                        'item_code' => $item->item_code,
                        'warehouse_id' => $item->warehouse_id
                    ];
                });
        } else {
            $this->searchResults = [];
        }
    }

    public function selectItem($itemId)
    {
        $item = Item::with(['warehouse', 'location'])->find($itemId);
        if ($item) {
            $this->selectedItem = [
                'id' => $item->id,
                'item_name' => $item->item_name,
                'item_code' => $item->item_code,
                'warehouse_id' => $item->warehouse_id,
                'location_id' => $item->location_id
            ];
            
            // Update warehouse to match the selected item's warehouse
            $this->selectedWarehouse = $item->warehouse_id;
            
            // Reset search results and update search term
            $this->searchResults = [];
            $this->itemSearchTerm = $item->item_name;
            
            // Update locations after warehouse change
            $this->itemLocations = $item->location_id ? [$item->location_id] : [];
            
            // Force a refresh of locations for the new warehouse
            $this->refreshUnassignedLocations();

            // Show toastr if unassigned
            if ($item->location && (is_null($item->location->position_x) || is_null($item->location->position_y))) {
                $message = "The item is in warehouse {$item->warehouse->warehouse_name} and location {$item->location->location_name}. The indicator is not assigned.";
                toastr()->warning($message);
            }
        }
    }


    
    public function clearItemSelection()
    {
        $this->selectedItem = null;
        $this->itemLocations = [];
        $this->itemSearchTerm = '';
        $this->searchResults = [];
        $this->selectedWarehouse = Warehouse::first()->id ?? null;
        // Keep the current warehouse selection when clearing item
        $this->refreshUnassignedLocations();
    }

    public function calculateUnassignedCounts()
    {
        $this->unassignedCounts = Location::whereNull('position_x')
            ->orWhereNull('position_y')
            ->get()
            ->groupBy('warehouse_id')
            ->map(function ($locations) {
                return $locations->count();
            })
            ->toArray();
    }

    public function toggleEditMode()
    {
        $this->isEditMode = !$this->isEditMode;
    }

    public function refreshUnassignedLocations()
    {
        $query = Location::with('warehouse')
            ->where('warehouse_id', $this->selectedWarehouse)
            ->where(function($q) {
                $q->whereNull('position_x')
                  ->orWhereNull('position_y');
            });
    
        if ($this->locationSearchTerm) {
            $query->where('location_name', 'like', '%' . $this->locationSearchTerm . '%');
        }
    
        $locations = $query->get();
        
        if ($locations->isNotEmpty()) {
            $this->unassignedLocations = $locations->map(function ($location) {
                return [
                    'id' => $location->id,
                    'location_name' => $location->location_name,
                    'warehouse_name' => $location->warehouse->warehouse_name ?? 'Unassigned',
                ];
            });
        } else {
            $this->unassignedLocations = collect([]);
        }
    }

    #[On('updateLocationPosition')]
    public function updateLocationPosition($id, $x, $y)
    {
        try {
            $location = Location::findOrFail($id);
            $x = round($x, 2);
            $y = round($y, 2);
            $location->update([
                'position_x' => $x,
                'position_y' => $y,
            ]);
            $this->calculateUnassignedCounts();
            $this->refreshUnassignedLocations();
        } catch (\Exception $e) {
            toastr()->error('Failed to update position.');
        }
    }

    public function fetchLocations()
    {
        $query = Location::query();

        if ($this->locationSearchTerm) {
            $query->where('location_name', 'like', '%' . $this->locationSearchTerm . '%');
        }

        if ($this->selectedWarehouse) {
            $query->where('warehouse_id', $this->selectedWarehouse);
        }

        return $query->orderBy($this->sortColumn, $this->sortOrder)
            ->paginate(8);
    }

    public function deleteLocation(Location $location)
    {
        if ($location) {
            if ($location->items()->exists()) {
                toastr()->error('This location cannot be deleted because items are associated with it.');
                return;
            }

            try {
                $location->delete();
                $this->deletedLocationIds[] = $location->id;
                $this->dispatch('locationDeleted', ['locationId' => $location->id]);
                toastr()->success('Location deleted successfully');
            } catch (\Exception $e) {
                toastr()->error('An error occurred while deleting the location: ' . $e->getMessage());
            }
        }

        $locations = $this->fetchLocations();

        if ($locations->isEmpty() && $this->activePageNumber > 1) {
            $this->gotoPage($this->activePageNumber - 1);
        } else {
            $this->gotoPage($this->activePageNumber);
        }
        
        $this->calculateUnassignedCounts();
        $this->refreshUnassignedLocations();
    }

    public function updatingPage($pageNumber)
    {
        $this->activePageNumber = $pageNumber;
    }

    public function updatedLocationSearchTerm()
    {
        $this->refreshUnassignedLocations();
    }

    public function updatedSelectedWarehouse($value)
    {
        if ($this->selectedItem && $this->selectedItem['warehouse_id'] != $value) {
            // If selected item exists and belongs to a different warehouse, clear the selection
            $this->clearItemSelection();
        }
        $this->refreshUnassignedLocations();
    }

    public function render()
    {
        $locations = $this->fetchLocations();
        return view('livewire.location-map', [
            'locations' => $locations,
            'warehouses' => $this->warehouses,
            'unassignedCounts' => $this->unassignedCounts,
            'searchResults' => $this->searchResults,
            'deletedLocationIds' => $this->deletedLocationIds,
        ])->layout('layouts.app');
    }

}