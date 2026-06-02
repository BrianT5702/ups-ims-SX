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
use Illuminate\Support\Facades\DB;
use App\Support\ItemPickerSearch;
use App\Support\InventoryListBrowse;

#[Title('UR | Stock List')]
class ItemList extends Component
{
    use WithPagination;

    public $itemSearchTerm = null;

    /** `code` = search item_code only; `name` = item_name only. */
    public $itemSearchMode = 'code';

    public $filterFamilyId = null;
    public $filterLocationId = null;

    public $filterDeadStock = false;

    /** Quantity filter: null = all, 'zero' = 0, 'positive' = >0, 'negative' = <0 */
    public $quantityFilter = null;

    public $selectedCategories = [];
    public $selectedFamilies = [];
    public $selectedGroups = [];
    public $selectedSuppliers = [];

    public $sortField = 'item_code';

    public $sortDirection = 'asc';

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

        $this->syncSortWithSearchMode();
    }

    private function syncSortWithSearchMode(): void
    {
        if (! in_array($this->itemSearchMode, ['code', 'name'], true)) {
            $this->itemSearchMode = 'code';
        }
        if ($this->itemSearchMode === 'name') {
            $this->sortField = 'item_name';
        } else {
            $this->sortField = 'item_code';
        }
        $this->sortDirection = 'asc';
    }

    protected $queryString = [
        'itemSearchTerm' => ['except' => ''],
        'itemSearchMode' => ['except' => 'code'],
    ];

    public function updatingItemSearchTerm(): void
    {
        $this->resetPage();
    }

    public function setItemSearchMode(string $mode): void
    {
        if (! in_array($mode, ['code', 'name'], true)) {
            return;
        }
        if ($this->itemSearchMode === $mode) {
            return;
        }
        $this->itemSearchMode = $mode;
        $this->resetPage();
        $this->syncSortWithSearchMode();
    }

    public function updatingQuantityFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if (!in_array($field, ['item_code', 'item_name'], true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function fetchItems()
    {
        $query = Item::query()
            ->when($this->itemSearchTerm, function ($query) {
                $raw = trim((string) $this->itemSearchTerm);
                if ($raw === '') {
                    return;
                }
                $norm = preg_replace('#\s*/\s*#', '/', $raw);
                $isFraction = (bool) preg_match('/^\d+\/\d+$/', $norm);
                $escapedRaw = addcslashes($raw, '\%_');
                $escapedNorm = addcslashes($norm, '\%_');
                $byName = $this->itemSearchMode === 'name';

                if ($byName) {
                    if ($isFraction) {
                        $compoundRe = ItemPickerSearch::compoundMixedNumberRegexp($norm);
                        $query->where(function ($q) use ($escapedNorm, $compoundRe) {
                            $q->where('item_name', 'like', '%' . $escapedNorm . '%')
                                ->whereRaw('NOT (item_name REGEXP ?)', [$compoundRe]);
                        });
                    } else {
                        $query->where('item_name', 'like', '%' . $escapedRaw . '%');
                    }
                } else {
                    if ($isFraction) {
                        $compoundRe = ItemPickerSearch::compoundMixedNumberRegexp($norm);
                        $query->where(function ($q) use ($escapedNorm, $compoundRe) {
                            $q->where('item_code', 'like', '%' . $escapedNorm . '%')
                                ->whereRaw('NOT (item_code REGEXP ?)', [$compoundRe]);
                        });
                    } else {
                        $query->where('item_code', 'like', '%' . $escapedRaw . '%');
                    }
                }
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
            })
            ->when($this->quantityFilter === 'zero', function ($query) {
                $query->where('qty', 0);
            })
            ->when($this->quantityFilter === 'positive', function ($query) {
                $query->where('qty', '>', 0);
            })
            ->when($this->quantityFilter === 'negative', function ($query) {
                $query->where('qty', '<', 0);
            });

        $expr = "COALESCE(NULLIF(TRIM(REGEXP_REPLACE(item_name, '^[[:space:]@#*~^$]+', '')), ''), item_name)";

        if ($this->sortField === 'item_code') {
            return $query
                ->orderBy('item_code', $this->sortDirection)
                ->orderBy('id', $this->sortDirection)
                ->paginate(50);
        }

        return $query
            ->orderByRaw($expr . ' ' . strtoupper($this->sortDirection))
            ->orderBy('id', $this->sortDirection)
            ->paginate(50);
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
        $this->itemSearchTerm = null;
        $this->itemSearchMode = 'code';
        $this->resetPage();
        $this->syncSortWithSearchMode();

        if ($this->filterLocationId) {
            $this->reset(['selectedCategories', 'selectedFamilies', 'selectedGroups', 'selectedSuppliers', 'filterFamilyId', 'filterDeadStock', 'quantityFilter']);
        } elseif ($this->filterFamilyId) {
            $this->reset(['selectedCategories', 'selectedGroups', 'selectedSuppliers', 'filterLocationId', 'filterDeadStock', 'quantityFilter']);
        } else {
            $this->reset(['selectedCategories', 'selectedFamilies', 'selectedGroups', 'selectedSuppliers', 'filterLocationId', 'filterFamilyId', 'filterDeadStock', 'quantityFilter']);
        }
    }

    public function render()
    {
        InventoryListBrowse::saveContextFromList($this);

        $items = $this->fetchItems();

        InventoryListBrowse::warmOrderedIdsForList(
            $items->total(),
            trim((string) $this->itemSearchTerm) !== ''
        );

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
}