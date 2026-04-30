<div>
    <div class="container my-3">
        <div class="row">
            <div class="col-md-12 m-auto">
                <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold fs-5 mb-0">
                        @if($filteredFamily)
                            {{ $filteredFamily->family_name }} - Total Item(s): {{ $familyItemCount }}
                        @elseif($filteredLocation)
                        {{ $filteredLocation->warehouse->warehouse_name }} > {{ $filteredLocation->location_name }} - Total Item(s): {{ $locationItemCount }}
                        @else
                            Manage Inventory
                        @endif
                    </h5>
                    @if($filteredFamily || $filteredLocation)
                    <a href="{{ url()->previous() }}" class="btn btn-primary btn-sm">Back</a>
                    @endif
                </div>

                    <div class="card-body">
                        <!-- Search and Add Item -->
                        <div class="row align-items-end mb-3 my-3 px-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" wire:model.live.debounce.300ms="itemSearchTerm" class="form-control form-control-sm rounded" placeholder="Search item...">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex justify-content-end">
                                    <a wire:navigate href="{{ route('items.add') }}" class="btn btn-primary">Add Item</a>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Section -->
                        <div class="mb-3 px-3">
                            <div class="row align-items-end">
                                <div class="col-md-3 mb-3">
                                    <label for="categoryFilter" class="form-label">Categories</label>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle w-100" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            {{ count($selectedCategories) > 0 ? 'Selected: ' . implode(', ', $this->getSelectedCategoryNames()) : 'Select Categories' }}
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="categoryDropdown" style="max-height: 240px; overflow-y: auto;">
                                            @foreach($categories as $category)
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="toggleCategory({{ $category->id }})">
                                                        {{ $category->cat_name }}
                                                        @if(in_array($category->id, $selectedCategories)) 
                                                            <span class="text-success">&#10003;</span>
                                                        @endif
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="familyFilter" class="form-label">Families</label>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle w-100" type="button" id="familyDropdown" data-bs-toggle="dropdown" aria-expanded="false" @if($filteredFamily) disabled @endif>
                                            {{ count($selectedFamilies) > 0 ? 'Selected: ' . implode(', ', $this->getSelectedFamilyNames()) : 'Select Families' }}
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="familyDropdown" style="max-height: 240px; overflow-y: auto;">
                                            @foreach($families as $family)
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="toggleFamily({{ $family->id }})">
                                                        {{ $family->family_name }}
                                                        @if(in_array($family->id, $selectedFamilies)) 
                                                            <span class="text-success">&#10003;</span>
                                                        @endif
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="groupFilter" class="form-label">Groups</label>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle w-100" type="button" id="groupDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            {{ count($selectedGroups) > 0 ? 'Selected: ' . implode(', ', $this->getSelectedGroupNames()) : 'Select Groups' }}
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="groupDropdown" style="max-height: 240px; overflow-y: auto;">
                                            @foreach($groups as $group)
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="toggleGroup({{ $group->id }})">
                                                        {{ $group->group_name }}
                                                        @if(in_array($group->id, $selectedGroups)) 
                                                            <span class="text-success">&#10003;</span>
                                                        @endif
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="supplierFilter" class="form-label">Suppliers</label>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle w-100" type="button" id="supplierDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            {{ count($selectedSuppliers) > 0 ? 'Selected: ' . implode(', ', $this->getSelectedSupplierNames()) : 'Select Suppliers' }}
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="supplierDropdown" style="max-height: 240px; overflow-y: auto;">
                                            @foreach($suppliers as $supplier)
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="toggleSupplier({{ $supplier->id }})">
                                                        {{ $supplier->sup_name }}
                                                        @if(in_array($supplier->id, $selectedSuppliers)) 
                                                            <span class="text-success">&#10003;</span>
                                                        @endif
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="quantityFilter" class="form-label">Quantity</label>
                                    <select id="quantityFilter" class="form-select form-select-sm" wire:model.live="quantityFilter">
                                        <option value="">All</option>
                                        <option value="positive">&gt; 0</option>
                                        <option value="zero">= 0</option>
                                        <option value="negative">&lt; 0</option>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="deadStockFilter" class="form-label">Filters</label>
                                    <div>
                                        <button type="button" class="btn btn-outline-danger w-100" wire:click="toggleDeadStockFilter">
                                            {{ $filterDeadStock ? 'Show All Items' : 'Show Dead Stock' }}
                                        </button>
                                    </div>
                                </div>


                                <div class="col-md-1 d-flex justify-content-end mb-3">
                                    <button wire:click="resetFilters" class="btn btn-sm btn-outline-secondary">Reset</button>
                                </div>
                            </div>
                        </div>

                        <!-- Item Table -->
                        <div class="table-responsive mt-3 inventory-table-wrap">
                            <table class="table table-hover table-bordered inventory-list">
                                <thead>
                                    <tr>
                                        <th>Item Code</th>
                                        <th>
                                            <button type="button" wire:click="sortBy('item_name')" class="btn btn-sm p-0 border-0 bg-transparent text-dark text-decoration-none fw-semibold">
                                                Item Name{{ $sortField === 'item_name' ? ($sortDirection === 'asc' ? ' ↑' : ' ↓') : '' }}
                                            </button>
                                        </th>
                                        <th>Quantity</th>
                                        <th>Cost</th>
                                        <th>Cash</th>
                                        <th>Term</th>
                                        <th>Cust.</th>
                                        <th>
                                            <button type="button" wire:click="sortBy('created_at')" class="btn btn-sm p-0 border-0 bg-transparent text-dark text-decoration-none fw-semibold">
                                                Created at{{ $sortField === 'created_at' ? ($sortDirection === 'asc' ? ' ↑' : ' ↓') : '' }}
                                            </button>
                                        </th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                        <tr>
                                            <td><a wire:navigate href="{{ route('items.edit', $item->id) }}" title="{{ $item->item_code }}">{{ $item->item_code }}</a></td>
                                            <td x-data="{ showMemo: false, hoverTimeout: null }"
                                                style="position: relative;"
                                                @mouseenter="hoverTimeout = setTimeout(() => { showMemo = true }, 800)"
                                                @mouseleave="clearTimeout(hoverTimeout); showMemo = false">
                                                <a wire:navigate href="{{ route('items.edit', $item->id) }}"
                                                   class="inventory-item-name-link"
                                                   title="{{ $item->item_name }}"
                                                   style="cursor: pointer;">
                                                    {{ $item->item_name }}
                                                </a>
                                                @if(!empty($item->memo))
                                                    <div x-show="showMemo"
                                                         x-transition
                                                         class="memo-tooltip"
                                                         @click.stop>
                                                        <div class="memo-tooltip-body">{{ $item->memo }}</div>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <a wire:navigate href="{{ route('items.edit', $item->id) }}">
                                                    {{ $item->qty }}
                                                </a>
                                            </td>
                                            <td><a wire:navigate href="{{ route('items.edit', $item->id) }}">{{ $item->cost }}</a></td>
                                            <td><a wire:navigate href="{{ route('items.edit', $item->id) }}">{{ $item->cash_price }}</a></td>
                                            <td><a wire:navigate href="{{ route('items.edit', $item->id) }}">{{ $item->term_price }}</a></td>
                                            <td><a wire:navigate href="{{ route('items.edit', $item->id) }}">{{ $item->cust_price }}</a></td>
                                            <td>
                                                <span>{{ $item->created_at->format('d/m/y H:i') }}</span>
                                            </td>
                                            <td>
                                                <button
                                                    wire:click.prevent="addToRestockList({{ $item->id }})"
                                                    type="button"
                                                    class="btn btn-link btn-sm p-0 border-0"
                                                    title="Add to Restock List"
                                                    aria-label="Add to Restock List">
                                                    <i class="fa-solid fa-file-circle-plus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No items found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{ $items->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prevent dropdown from closing when selecting options
        document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
            dropdown.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        });

        // Prevent default anchor behavior for dropdown items
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function (event) {
                event.preventDefault();
            });
        });
    </script>

    <style>
        .dropdown-toggle {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Fixed table layout for consistent column widths */
        .inventory-table-wrap {
            max-width: 1120px;
            margin: 0 auto;
        }

        .table.inventory-list {
            table-layout: fixed;
            width: 100%;
            --bs-table-border-color: #d0d7e2;
            border-color: var(--bs-table-border-color);
        }

        .table.inventory-list > :not(caption) > * > * {
            border-color: var(--bs-table-border-color);
        }

        .table.inventory-list thead th {
            background-color: #f4f6fa;
            border-bottom-width: 1px;
        }
        
        /* Common styles for all cells */
        .table.inventory-list th, 
        .table.inventory-list td {
            padding: 0.5rem;
            vertical-align: middle;
            word-wrap: break-word;
            min-width: 0; /* Allows columns to shrink below content width */
            font-size: 0.8rem;
            line-height: 1.25;
        }
        
        /* Header specific styles */
        .table.inventory-list th {
            font-size: 0.78rem;
            line-height: 1.4;
            vertical-align: middle;
            white-space: nowrap; /* Prevent wrapping - keep headers on one line */
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Column widths (after removing No column) */
        .table.inventory-list th:nth-child(1),
        .table.inventory-list td:nth-child(1) { width: 15%; } /* Item Code */

        .table.inventory-list th:nth-child(2),
        .table.inventory-list td:nth-child(2) { width: 35%; } /* Item Name */

        .table.inventory-list th:nth-child(3),
        .table.inventory-list td:nth-child(3) { width: 8%; } /* Quantity */

        .table.inventory-list th:nth-child(4),
        .table.inventory-list td:nth-child(4) { width: 7%; } /* Cost */

        .table.inventory-list th:nth-child(5),
        .table.inventory-list td:nth-child(5) { width: 7%; } /* Cash */

        .table.inventory-list th:nth-child(6),
        .table.inventory-list td:nth-child(6) { width: 7%; } /* Term */

        .table.inventory-list th:nth-child(7),
        .table.inventory-list td:nth-child(7) { width: 7%; } /* Cust. */

        .table.inventory-list th:nth-child(8),
        .table.inventory-list td:nth-child(8) { width: 11%; } /* Created/Updated At */

        .table.inventory-list th:nth-child(9),
        .table.inventory-list td:nth-child(9) {
            width: 5%; /* Action — single control */
            text-align: center;
        }

        /* Item Code + Item Name: one line with ellipsis (Item Code had no rules before, so long codes wrapped) */
        .table.inventory-list th:nth-child(1),
        .table.inventory-list td:nth-child(1),
        .table.inventory-list th:nth-child(2),
        .table.inventory-list td:nth-child(2) {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table.inventory-list td:nth-child(1) > a,
        .table.inventory-list td:nth-child(2) > a.inventory-item-name-link {
            display: block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table.inventory-list .btn {
            font-size: 0.78rem;
        }

        /* Center numeric columns for quick scanning */
        .table.inventory-list th:nth-child(n+3):nth-child(-n+7),
        .table.inventory-list td:nth-child(n+3):nth-child(-n+7) {
            text-align: center;
        }
    </style>
</div>
