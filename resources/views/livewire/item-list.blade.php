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
                        <div class="table-responsive mt-3">
                            <table class="table table-hover inventory-list">
                                <thead>
                                    <tr align="center">
                                        <th>No</th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Cost</th>
                                        <th>Cash Price</th>
                                        <th>Term Price</th>
                                        <th>Customer Price</th>
                                        <th>Created/Updated At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                        <tr align="center">
                                            <td>{{ $loop->iteration }}</td>
                                            <td><a wire:navigate href="{{ route('items.edit', $item->id) }}">{{ $item->item_code }}</a></td>
                                            <td x-data="{ 
                                                    showMemo: false,
                                                    hoverTimeout: null
                                                }" 
                                                style="position: relative;"
                                                @mouseenter="hoverTimeout = setTimeout(() => { showMemo = true }, 1000)"
                                                @mouseleave="clearTimeout(hoverTimeout); showMemo = false">
                                                <a wire:navigate href="{{ route('items.edit', $item->id) }}" 
                                                   style="cursor: pointer;">{{ $item->item_name }}</a>
                                                @if(!empty($item->memo))
                                                    <div x-show="showMemo" 
                                                         x-transition
                                                         @mouseenter="clearTimeout(hoverTimeout); showMemo = true"
                                                         @mouseleave="showMemo = false"
                                                         style="position: absolute; background: #fff; border: 1px solid #ccc; padding: 6px 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); z-index: 1000; margin-top: 2px; width: auto; max-width: 200px; max-height: 150px; overflow-y: auto; font-size: 0.8em; white-space: pre-wrap; left: 0; top: 100%; word-wrap: break-word; text-align: left; line-height: 1.4;"
                                                         @click.stop>
                                                        <strong style="font-size: 0.85em; display: block; margin-bottom: 3px;">Memo:</strong>
                                                        <div style="font-size: 0.8em; text-align: left; white-space: pre-wrap; word-wrap: break-word; line-height: 1.4;">{{ $item->memo }}</div>
                                                    </div>
                                                @endif
                                            </td>
                                            <td><a wire:navigate href="{{ route('items.edit', $item->id) }}">{{ ($activeDb === 'ups' || $activeDb === 'ucs') ? 0 : $item->qty }}</a></td>
                                            <td><a wire:navigate href="{{ route('items.edit', $item->id) }}">{{ $item->cost }}</a></td>
                                            <td><a wire:navigate href="{{ route('items.edit', $item->id) }}">{{ $item->cash_price }}</a></td>
                                            <td><a wire:navigate href="{{ route('items.edit', $item->id) }}">{{ $item->term_price }}</a></td>
                                            <td><a wire:navigate href="{{ route('items.edit', $item->id) }}">{{ $item->cust_price }}</a></td>
                                            <td>
                                                <span>{{ $item->created_at->format('Y-m-d H:i') }}</span><br>
                                                <span class="small">Updated {{ $item->updated_at->diffForHumans() }}</span>
                                            </td>
                                            <td>
                                                <button wire:click.prevent="addToRestockList({{ $item->id }})">
                                                    <i class="fa-solid fa-file-circle-plus"></i>
                                                </button>
                                                <div class="py-2"></div>
                                                <button wire:click.prevent="showItemTransactions({{ $item->id }})">
                                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                                </button>
                                                <div class="py-2"></div>
                                                <button wire:click.prevent="showImage({{ $item->id }})">
                                                    <i class="fa-solid fa-image"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center">No items found.</td>
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

    @if($selectedImage)
    <div class="modal fade show" tabindex="-1" style="display: block;" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Item Image</h5>
                    <button type="button" class="btn-close" wire:click="closeImageModal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center">
                    <img src="{{ $selectedImage }}" class="img-fluid" alt="Item Image" style="max-height: 80vh;">
                </div>
            </div>
        </div>
    </div>
    @endif


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
        .table.inventory-list { 
            table-layout: fixed;
            width: 100%;
        }
        
        /* Common styles for all cells */
        .table.inventory-list th, 
        .table.inventory-list td {
            padding: 0.5rem;
            vertical-align: middle;
            word-wrap: break-word;
            min-width: 0; /* Allows columns to shrink below content width */
        }
        
        /* Header specific styles */
        .table.inventory-list th {
            font-size: 0.9em;
            line-height: 1.4;
            vertical-align: middle;
            white-space: nowrap; /* Prevent wrapping - keep headers on one line */
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Column widths */
        .table.inventory-list th:nth-child(1), 
        .table.inventory-list td:nth-child(1) { width: 4%; } /* No */
        
        .table.inventory-list th:nth-child(2), 
        .table.inventory-list td:nth-child(2) { width: 10%; } /* Item Code */
        
        .table.inventory-list th:nth-child(3), 
        .table.inventory-list td:nth-child(3) { width: 25%; } /* Item Name - wider */
        
        .table.inventory-list th:nth-child(4), 
        .table.inventory-list td:nth-child(4) { width: 7%; } /* Quantity */
        
        .table.inventory-list th:nth-child(5), 
        .table.inventory-list td:nth-child(5) { width: 8%; } /* Cost */
        
        .table.inventory-list th:nth-child(6), 
        .table.inventory-list td:nth-child(6) { width: 9%; } /* Cash Price */
        
        .table.inventory-list th:nth-child(7), 
        .table.inventory-list td:nth-child(7) { width: 9%; } /* Term Price */
        
        .table.inventory-list th:nth-child(8), 
        .table.inventory-list td:nth-child(8) { width: 10%; } /* Customer Price */
        
        .table.inventory-list th:nth-child(9), 
        .table.inventory-list td:nth-child(9) { width: 12%; } /* Created/Updated At */
        
        .table.inventory-list th:nth-child(10), 
        .table.inventory-list td:nth-child(10) { width: 5%; } /* Action - narrower, right-aligned */
        
        /* Action column alignment */
        .table.inventory-list th:nth-child(10),
        .table.inventory-list td:nth-child(10) {
            text-align: right;
            padding-right: 1rem;
        }
    </style>
</div>
