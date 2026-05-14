<div class="list-page-unified-density">
    <div class="container my-2" style="padding-left: 0.25rem; padding-right: 0.25rem;">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header transaction-log-page-header d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="min-w-0 flex-grow-1">
                            @if($filteredFamily)
                                <div class="text-muted fw-semibold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.08em;">Family</div>
                                <h5 class="fw-bold mb-0 list-page-unified-title mt-1">{{ $filteredFamily->family_name }}</h5>
                                <p class="small text-muted mb-0 mt-1">Total item(s): {{ $familyItemCount }}</p>
                            @elseif($filteredLocation)
                                <div class="text-muted fw-semibold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.08em;">Location</div>
                                <h5 class="fw-bold mb-0 list-page-unified-title mt-1">{{ $filteredLocation->warehouse->warehouse_name }} > {{ $filteredLocation->location_name }}</h5>
                                <p class="small text-muted mb-0 mt-1">Total item(s): {{ $locationItemCount }}</p>
                            @else
                                <h5 class="fw-bold mb-0 list-page-unified-title">Inventory List</h5>
                            @endif
                        </div>
                        <div class="d-flex align-items-start gap-2 flex-shrink-0">
                            @if($filteredFamily || $filteredLocation)
                                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Back</a>
                            @endif
                            <a wire:navigate href="{{ route('items.add') }}" class="btn btn-primary btn-sm">Add Item</a>
                        </div>
                    </div>

                    <div class="card-body px-2 pb-3 transaction-log-card-body inventory-list-filters-tight">
                        <div class="row mb-0 g-1 align-items-end list-page-unified-filters">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" wire:model.live.debounce.300ms="itemSearchTerm" class="form-control form-control-sm rounded" placeholder="{{ $itemSearchMode === 'name' ? 'Search item name...' : 'Search item code...' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">Search in</label>
                                <div class="btn-group w-100" role="group" aria-label="Search in">
                                    <button type="button"
                                        wire:click="setItemSearchMode('code')"
                                        class="btn btn-sm {{ $itemSearchMode === 'code' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                        By code
                                    </button>
                                    <button type="button"
                                        wire:click="setItemSearchMode('name')"
                                        class="btn btn-sm {{ $itemSearchMode === 'name' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                        By name
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Section -->
                        <div class="mb-0 inventory-filter-block">
                            <div class="row g-1 align-items-end list-page-unified-filters">
                                <div class="col-md-3">
                                    <label for="categoryFilter" class="form-label">Categories</label>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle w-100" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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

                                <div class="col-md-2">
                                    <label for="familyFilter" class="form-label">Families</label>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle w-100" type="button" id="familyDropdown" data-bs-toggle="dropdown" aria-expanded="false" @if($filteredFamily) disabled @endif>
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

                                <div class="col-md-2">
                                    <label for="groupFilter" class="form-label">Groups</label>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle w-100" type="button" id="groupDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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

                                <div class="col-md-2">
                                    <label for="supplierFilter" class="form-label">Suppliers</label>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle w-100" type="button" id="supplierDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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

                                <div class="col-md-2">
                                    <label for="quantityFilter" class="form-label">Quantity</label>
                                    <select id="quantityFilter" class="form-select form-select-sm" wire:model.live="quantityFilter">
                                        <option value="">All</option>
                                        <option value="positive">&gt; 0</option>
                                        <option value="zero">= 0</option>
                                        <option value="negative">&lt; 0</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="deadStockFilter" class="form-label">Filters</label>
                                    <div class="d-flex gap-2 align-items-stretch justify-content-end flex-nowrap w-100">
                                        <button type="button" class="btn btn-outline-danger btn-sm flex-fill text-truncate" wire:click="toggleDeadStockFilter" title="{{ $filterDeadStock ? 'Show All Items' : 'Show Dead Stock' }}">
                                            {{ $filterDeadStock ? 'Show All Items' : 'Show Dead Stock' }}
                                        </button>
                                        <button wire:click="resetFilters" type="button" class="btn btn-outline-secondary btn-sm transaction-log-reset-btn flex-shrink-0">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Item Table -->
                        <div class="inventory-table-wrap mt-1">
                            <div class="table-responsive list-sticky-table-scroll">
                                <table class="table table-hover inventory-list">
                                <thead>
                                    <tr>
                                        <th>
                                            <button type="button" wire:click="sortBy('item_code')" class="btn btn-sm p-0 border-0 bg-transparent text-dark text-decoration-none fw-semibold inventory-list-sort-btn">
                                                Item Code{{ $sortField === 'item_code' ? ($sortDirection === 'asc' ? ' ↑' : ' ↓') : '' }}
                                            </button>
                                        </th>
                                        <th>
                                            <button type="button" wire:click="sortBy('item_name')" class="btn btn-sm p-0 border-0 bg-transparent text-dark text-decoration-none fw-semibold inventory-list-sort-btn">
                                                Item Name{{ $sortField === 'item_name' ? ($sortDirection === 'asc' ? ' ↑' : ' ↓') : '' }}
                                            </button>
                                        </th>
                                        <th>Quantity</th>
                                        <th>Cost</th>
                                        <th>Cash</th>
                                        <th>Term</th>
                                        <th>Cust.</th>
                                        <th>
                                            <button type="button" wire:click="sortBy('created_at')" class="btn btn-sm p-0 border-0 bg-transparent text-dark text-decoration-none fw-semibold inventory-list-sort-btn">
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
                            </div>
                            <div class="inventory-list-pagination d-flex justify-content-end flex-wrap">
                                {{ $items->links() }}
                            </div>
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

        /* Tighter vertical spacing: search row vs dropdowns vs filters row */
        .list-page-unified-density .transaction-log-card-body.inventory-list-filters-tight {
            padding-top: 0.2rem !important;
        }
        .inventory-list-filters-tight > .row.list-page-unified-filters:first-of-type {
            margin-bottom: 0.2rem;
        }
        .inventory-list-filters-tight .inventory-filter-block {
            margin-top: 0;
        }
        .list-page-unified-density .transaction-log-card-body.inventory-list-filters-tight .list-page-unified-filters .form-label {
            margin-bottom: 0.08rem;
        }
        .inventory-list-filters-tight .row.list-page-unified-filters {
            --bs-gutter-y: 0.35rem;
        }
        
        /* Fixed table layout for consistent column widths */
        .inventory-table-wrap {
            max-width: 1120px;
            margin: 0 auto;
        }

        .table.inventory-list {
            table-layout: fixed;
            width: 100%;
            min-width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 0;
            border: 1px solid #212529;
            --tx-log-cell-px: 0.38rem;
            --tx-log-cell-py: 0.22rem;
        }

        .table.inventory-list th,
        .table.inventory-list td {
            padding: var(--tx-log-cell-py) var(--tx-log-cell-px);
            vertical-align: middle;
            word-wrap: break-word;
            min-width: 0;
            border: 1px solid #dee2e6;
        }

        .table.inventory-list tbody td {
            font-size: 0.78rem;
            line-height: 1.28;
        }

        .table.inventory-list thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 0.82rem;
            line-height: 1.3;
            letter-spacing: 0.01em;
            border-bottom: 2px solid #212529;
            border-top: 1px solid #212529;
            border-left: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table.inventory-list thead th:first-child {
            border-left: 1px solid #212529;
        }

        .table.inventory-list thead th:last-child {
            border-right: 1px solid #212529;
        }

        .table.inventory-list tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table.inventory-list tbody td:first-child {
            border-left: 1px solid #212529;
        }

        .table.inventory-list tbody td:last-child {
            border-right: 1px solid #212529;
        }

        .table.inventory-list tbody tr:last-child td {
            border-bottom: 1px solid #212529;
        }

        .table.inventory-list .inventory-list-sort-btn {
            font-size: 0.82rem;
            line-height: 1.3;
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

        /* Item Code + Item Name: one line with ellipsis */
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

    @include('partials.unified-list-page-styles')
</div>
