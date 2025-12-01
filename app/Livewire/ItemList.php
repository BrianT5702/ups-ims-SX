<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Item;
use App\Models\Category;
use App\Models\Family;
use App\Models\Group;
use App\Models\Supplier;
use App\Models\Location;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\RestockList;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

#[Title('UR | Stock List')]
class ItemList extends Component
{
    use WithPagination;

    public $itemSearchTerm = null;
    public $filterFamilyId = null;
    public $filterLocationId = null;

    public $filterDeadStock = false;

    public $selectedCategories = [];
    public $selectedFamilies = [];
    public $selectedGroups = [];
    public $selectedSuppliers = [];

    public $selectedImage = null;

    public function mount($familyId = null, $locationId = null)
    {
        if ($familyId) {
            $this->filterFamilyId = $familyId;
            $this->selectedFamilies = [$familyId];
        }
        
        if ($locationId) {
            $this->filterLocationId = $locationId;
            $this->filterFamilyId = null;
            $this->selectedFamilies = [];
        }
    }

    public function showImage($itemId)
    {
        $item = Item::find($itemId);
        if ($item && $item->image) {
            $this->selectedImage = Storage::url($item->image);
        } else {
            $this->selectedImage = null;
            toastr()->error('No image available for this item.');
        }
    }

    public function closeImageModal()
    {
        $this->selectedImage = null;
    }

    protected $queryString = [
        'itemSearchTerm' => ['except' => ''],
    ];

    public function updatingItemSearchTerm($value)
    {
        $this->resetPage();
        if (!empty($value)) {
            $this->resetFilters();
        }
    }

    public function fetchItems()
    {
        $query = Item::query()
            ->when($this->itemSearchTerm, function ($query) {
                $term = $this->itemSearchTerm;
                $query->where(function ($q) use ($term) {
                    $q->where('item_name', 'like', '%' . $term . '%')
                      ->orWhere('item_code', 'like', '%' . $term . '%');
                });
            })
            ->when($this->selectedCategories, function ($query) {
                $query->whereIn('cat_id', $this->selectedCategories);
            })
            ->when($this->selectedFamilies, function ($query) {
                $query->whereIn('family_id', $this->selectedFamilies);
            })
            ->when($this->selectedGroups, function ($query) {
                $query->whereIn('group_id', $this->selectedGroups);
            })
            ->when($this->selectedSuppliers, function ($query) {
                $query->whereIn('sup_id', $this->selectedSuppliers);
            })
            ->when($this->filterLocationId, function ($query) {
                $query->where('location_id', $this->filterLocationId);
            })
            ->when($this->filterDeadStock, function ($query) {
                $query->where('qty', 0);
            });;

        return $query->paginate(50);
    }

    public function getSelectedCategoryNames()
    {
        return Category::whereIn('id', $this->selectedCategories)->pluck('cat_name')->toArray();
    }

    public function getSelectedFamilyNames()
    {
        return Family::whereIn('id', $this->selectedFamilies)->pluck('family_name')->toArray();
    }

    public function getSelectedGroupNames()
    {
        return Group::whereIn('id', $this->selectedGroups)->pluck('group_name')->toArray();
    }

    public function getSelectedSupplierNames()
    {
        return Supplier::whereIn('id', $this->selectedSuppliers)->pluck('sup_name')->toArray();
    }


    public function toggleCategory($categoryId)
    {
        if (in_array($categoryId, $this->selectedCategories)) {
            $this->selectedCategories = array_diff($this->selectedCategories, [$categoryId]);
        } else {
            $this->selectedCategories[] = $categoryId;
        }
    }

    public function toggleFamily($familyId)
    {
        if (in_array($familyId, $this->selectedFamilies)) {
            $this->selectedFamilies = array_diff($this->selectedFamilies, [$familyId]);
        } else {
            $this->selectedFamilies[] = $familyId;
        }
    }

    public function toggleGroup($groupId)
    {
        if (in_array($groupId, $this->selectedGroups)) {
            $this->selectedGroups = array_diff($this->selectedGroups, [$groupId]);
        } else {
            $this->selectedGroups[] = $groupId;
        }
    }

    public function toggleSupplier($supplierId)
    {
        if (in_array($supplierId, $this->selectedSuppliers)) {
            $this->selectedSuppliers = array_diff($this->selectedSuppliers, [$supplierId]);
        } else {
            $this->selectedSuppliers[] = $supplierId;
        }
    }

    public function toggleDeadStockFilter()
    {
        $this->filterDeadStock = !$this->filterDeadStock;
        $this->resetPage();
    }


    public function resetFilters()
    {
        if ($this->filterLocationId) {
         
            $this->reset(['selectedCategories', 'selectedFamilies', 'selectedGroups', 'selectedSuppliers', 'filterFamilyId', 'filterDeadStock']);
        } elseif ($this->filterFamilyId) {
          
            $this->reset(['selectedCategories', 'selectedGroups', 'selectedSuppliers', 'filterLocationId', 'filterDeadStock']);
        } else {
           
            $this->reset(['selectedCategories', 'selectedFamilies', 'selectedGroups', 'selectedSuppliers', 'filterLocationId', 'filterFamilyId', 'filterDeadStock']);
        }
    }

    public function render()
    {
        $items = $this->fetchItems();

        // Fetch all necessary data for dropdowns
        $categories = Category::orderBy('cat_name')->get();
        $families = Family::orderBy('family_name')->get();
        $groups = Group::orderBy('group_name')->get();
        $suppliers = Supplier::orderBy('sup_name')->get();
        $locations = Location::orderBy('location_name')->get();

        $filteredFamily = $this->filterFamilyId ? Family::findOrFail($this->filterFamilyId) : null;
        $filteredLocation = $this->filterLocationId ? Location::findOrFail($this->filterLocationId) : null;

        $familyItemCount = $this->filterFamilyId ? Item::where('family_id', $this->filterFamilyId)->count() : null;
        $locationItemCount = $this->filterLocationId ? Item::where('location_id', $this->filterLocationId)->count() : null;

        // Get current database connection
        $activeDb = session('active_db', DB::getDefaultConnection());

        return view('livewire.item-list', [
            'items' => $items,
            'categories' => $categories,
            'families' => $families,
            'groups' => $groups,
            'suppliers' => $suppliers,
            'locations' => $locations,
            'filteredFamily' => $filteredFamily,
            'filteredLocation' => $filteredLocation,
            'familyItemCount' => $familyItemCount,
            'locationItemCount' => $locationItemCount,
            'activeDb' => $activeDb,
        ])->layout('layouts.app');
    }

    public function addToRestockList($itemId)
    {
        $item = Item::findOrFail($itemId);
        
        $existingRestockItem = RestockList::where('item_id', $itemId)->first();

        if (!$existingRestockItem) {
            RestockList::create([
                'item_id' => $item->id,
            ]);
        }

        toastr()->success('Item added to restock list.');
    }

    public function showItemTransactions($itemId)
    {
        return redirect()->route('transaction-log.show', ['itemId' => $itemId]);
    }
}