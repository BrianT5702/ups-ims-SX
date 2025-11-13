<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Item;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\Location;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\RestockList;
use Illuminate\Support\Facades\Storage;

#[Title('UR | Stock List')]
class ItemList extends Component
{
    use WithPagination;

    public $itemSearchTerm = null;
    public $filterBrandId = null;
    public $filterLocationId = null;

    public $filterDeadStock = false;

    public $selectedCategories = [];
    public $selectedBrands = [];
    public $selectedSuppliers = [];

    public $selectedImage = null;

    public function mount($brandId = null, $locationId = null)
    {
        if ($brandId) {
            $this->filterBrandId = $brandId;
            $this->selectedBrands = [$brandId];
        }
        
        if ($locationId) {
            $this->filterLocationId = $locationId;
            $this->filterBrandId = null;
            $this->selectedBrands = [];
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
            ->when($this->selectedBrands, function ($query) {
                $query->whereIn('brand_id', $this->selectedBrands);
            })
            ->when($this->selectedSuppliers, function ($query) {
                $query->whereIn('sup_id', $this->selectedSuppliers);
            })
            ->when($this->filterLocationId, function ($query) {
                $query->where('location_id', $this->filterLocationId);
            })
            ->when($this->filterDeadStock, function ($query) {
                $query->where('updated_at', '<', now()->subYear());
            });;

        return $query->paginate(50);
    }

    public function getSelectedCategoryNames()
    {
        return Category::whereIn('id', $this->selectedCategories)->pluck('cat_name')->toArray();
    }

    public function getSelectedBrandNames()
    {
        return Brand::whereIn('id', $this->selectedBrands)->pluck('brand_name')->toArray();
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

    public function toggleBrand($brandId)
    {
        if (in_array($brandId, $this->selectedBrands)) {
            $this->selectedBrands = array_diff($this->selectedBrands, [$brandId]);
        } else {
            $this->selectedBrands[] = $brandId;
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
         
            $this->reset(['selectedCategories', 'selectedBrands', 'selectedSuppliers', 'filterBrandId', 'filterDeadStock']);
        } elseif ($this->filterBrandId) {
          
            $this->reset(['selectedCategories', 'selectedSuppliers', 'filterLocationId', 'filterDeadStock']);
        } else {
           
            $this->reset(['selectedCategories', 'selectedBrands', 'selectedSuppliers', 'filterLocationId', 'filterBrandId', 'filterDeadStock']);
        }
    }

    public function render()
    {
        $items = $this->fetchItems();

        // Fetch all necessary data for dropdowns
        $categories = Category::orderBy('cat_name')->get();
        $brands = Brand::orderBy('brand_name')->get();
        $suppliers = Supplier::orderBy('sup_name')->get();
        $locations = Location::orderBy('location_name')->get();

        $filteredBrand = $this->filterBrandId ? Brand::findOrFail($this->filterBrandId) : null;
        $filteredLocation = $this->filterLocationId ? Location::findOrFail($this->filterLocationId) : null;

        $brandItemCount = $this->filterBrandId ? Item::where('brand_id', $this->filterBrandId)->count() : null;
        $locationItemCount = $this->filterLocationId ? Item::where('location_id', $this->filterLocationId)->count() : null;

        return view('livewire.item-list', [
            'items' => $items,
            'categories' => $categories,
            'brands' => $brands,
            'suppliers' => $suppliers,
            'locations' => $locations,
            'filteredBrand' => $filteredBrand,
            'filteredLocation' => $filteredLocation,
            'brandItemCount' => $brandItemCount,
            'locationItemCount' => $locationItemCount,
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