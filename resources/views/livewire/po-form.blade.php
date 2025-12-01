<div class="container-fluid my-3">
    <div class="row">
        <div class="col-md-11 m-auto">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold fs-5">{{ $isView ? 'View' : ($purchaseOrder ? 'Edit': 'Add' )}} Purchase Order </h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="addPO">
                        <div class="row mb-3">
                        @if(!$isView)
                        @if(!$purchaseOrder || ($purchaseOrder && (!$purchaseOrder->status === 'Pending Approval' || !$purchaseOrder->status === 'Rejected')))
                        <div class="col-md-4" x-data="{ hi: 0 }">
                            <label for="supplier">Supplier <span class="text-danger">*</span></label>
                                <input type="text" wire:model.debounce.100ms="supplierSearchTerm" wire:input.debounce.200ms="searchSuppliers" id="searchSupplier" 
                                    class="form-control  rounded" placeholder="Search Supplier" {{ $isView ? 'disabled' : ''}} autocomplete="off"
                                    x-on:keydown.arrow-down.prevent="(() => { const list = $refs.supList; const items = list ? list.querySelectorAll('li') : []; if(items.length===0) return; hi = Math.min(hi + 1, items.length - 1); $nextTick(() => { const el = items[hi]; if(!el) return; const elTop = el.offsetTop; const elBottom = elTop + el.offsetHeight; const viewTop = list.scrollTop; const viewBottom = viewTop + list.clientHeight; if (elTop < viewTop) { list.scrollTop = elTop; } else if (elBottom > viewBottom) { list.scrollTop = elBottom - list.clientHeight; } }); })()"
                                    x-on:keydown.arrow-up.prevent="(() => { const list = $refs.supList; const items = list ? list.querySelectorAll('li') : []; if(items.length===0) return; hi = Math.max(hi - 1, 0); $nextTick(() => { const el = items[hi]; if(!el) return; const elTop = el.offsetTop; const elBottom = elTop + el.offsetHeight; const viewTop = list.scrollTop; const viewBottom = viewTop + list.clientHeight; if (elTop < viewTop) { list.scrollTop = elTop; } else if (elBottom > viewBottom) { list.scrollTop = elBottom - list.clientHeight; } }); })()"
                                    x-on:keydown.enter.prevent="(() => { const list = $refs.supList; const items = list ? list.querySelectorAll('li') : []; const el = items && items[hi]; if(el) el.click(); })()">
                                    @error('supplier_id')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                @if(count($supplierSearchResults) > 0)
                                    <div class="search-results mt-2">
                                        <ul class="list-group" x-ref="supList">  
                                            @foreach($supplierSearchResults as $idx => $supResult)
                                                <li class="list-group-item d-flex justify-content-between align-items-center"
                                                    wire:click="selectSupplier({{ $supResult->id }})"
                                                    :class="{ 'active': hi === {{ $idx }} }">
                                                    <span>{{ $supResult->account }} - {{ $supResult->sup_name }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            @endif
                            @endif

                            @if($isView || $purchaseOrder)
                                <div class="col-md-4">
                                    <div>
                                        <p class="fw-bold mb-2">{{ $purchaseOrder->supplierSnapshot->sup_name ?? $purchaseOrder->supplier->sup_name }}</p>
                                        <p class="mb-1"><strong>Currency:</strong> {{ $purchaseOrder->supplierSnapshot->currency ?? $purchaseOrder->supplier->currency ?? 'RM' }}</p>
                                        <p class="mb-1">{{ $purchaseOrder->supplierSnapshot->address_line1 ?? $purchaseOrder->supplier->address_line1 }}</p>
                                        <p class="mb-1">{{ $purchaseOrder->supplierSnapshot->address_line2 ?? $purchaseOrder->supplier->address_line2 }}</p>
                                        @if($purchaseOrder->supplierSnapshot->address_line3 ?? $purchaseOrder->supplier->address_line3)
                                            <p class="mb-1">{{ $purchaseOrder->supplierSnapshot->address_line3 ?? $purchaseOrder->supplier->address_line3 }}</p>
                                        @endif
                                        @if($purchaseOrder->supplierSnapshot->address_line4 ?? $purchaseOrder->supplier->address_line4)
                                            <p class="mb-1">{{ $purchaseOrder->supplierSnapshot->address_line4 ?? $purchaseOrder->supplier->address_line4 }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            
    
                            <div class="col-md-4">
                            <label for="date">Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                wire:model="date" 
                                id="date" 
                                class="form-control rounded" 
                                placeholder="dd/mm/yyyy"
                                {{ $purchaseOrder && ($purchaseOrder->status === 'In Progress' || $purchaseOrder->status === 'Completed') ? 'disabled' : '' }}>
                            @error('date')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="po_num">PO Number <span class="text-danger">*</span></label>
                            <input type="text" 
                                wire:model="po_num" 
                                id="po_num" 
                                class="form-control rounded" 
                                {{ $purchaseOrder && ($purchaseOrder->status === 'In Progress' || $purchaseOrder->status === 'Completed') ? 'disabled' : '' }}>
                            @error('po_num')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>




                        <div class="row mb-3 pt-3">
                            <div class="col-md-6">
                                <label for="ref_num">Reference Number</label>
                                <input type="text" wire:model="ref_num" id="ref_num" class="form-control rounded" placeholder="Enter Reference Number" {{ $isView || ($purchaseOrder && (($purchaseOrder->status === 'Completed') || ($purchaseOrder->status === 'In Progress' && !$isRevising)))   ? 'disabled' : ''}}>
                            </div>

                            <div class="col-md-6">
                                <label for="remark">Remark</label>
                                <textarea wire:model="remark" id="remark" class="form-control rounded" rows="3" placeholder="Enter Remark (e.g., delivery address)" {{ $isView || ($purchaseOrder && (($purchaseOrder->status === 'Completed') || ($purchaseOrder->status === 'In Progress' && !$isRevising))) ? 'disabled' : ''}}></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            @if(!$isView)
                                @if(!$purchaseOrder || ($purchaseOrder && ($purchaseOrder->status === 'Rejected' || ($purchaseOrder->status === 'In Progress' && $isRevising) || $purchaseOrder->status === 'Save to Draft')))
                                    <div class="col-md-6" x-data="{ hi: 0 }">
                                        <label for="search">Search Items</label>
                                        <input type="text" wire:model.debounce.100ms="itemSearchTerm" wire:input.debounce.200ms="searchItems" id="searchItem" 
                                            class="form-control rounded" placeholder="Search by Item Code or Name" {{ $isView ? 'disabled' : ''}} autocomplete="off"
                                            x-on:input="hi = 0"
                                            x-on:keydown.arrow-down.prevent="(() => { const list = $refs.list; const items = list ? list.querySelectorAll('li') : []; if(items.length===0) return; hi = Math.min(hi + 1, items.length - 1); $nextTick(() => { const el = items[hi]; if(!el) return; const elTop = el.offsetTop; const elBottom = elTop + el.offsetHeight; const viewTop = list.scrollTop; const viewBottom = viewTop + list.clientHeight; if (elTop < viewTop) { list.scrollTop = elTop; } else if (elBottom > viewBottom) { list.scrollTop = elBottom - list.clientHeight; } }); })()"
                                            x-on:keydown.arrow-up.prevent="(() => { const list = $refs.list; const items = list ? list.querySelectorAll('li') : []; if(items.length===0) return; hi = Math.max(hi - 1, 0); $nextTick(() => { const el = items[hi]; if(!el) return; const elTop = el.offsetTop; const elBottom = elTop + el.offsetHeight; const viewTop = list.scrollTop; const viewBottom = viewTop + list.clientHeight; if (elTop < viewTop) { list.scrollTop = elTop; } else if (elBottom > viewBottom) { list.scrollTop = elBottom - list.clientHeight; } }); })()"
                                            x-on:keydown.enter.prevent="(() => { const list = $refs.list; const items = list ? list.querySelectorAll('li') : []; const el = items && items[hi]; if(el) el.click(); })()">

                                        @if(count($itemSearchResults) > 0)
                                            <div class="search-results mt-2">
                                                <ul class="list-group" x-ref="list">
                                                    @foreach($itemSearchResults as $idx => $result)
                                                        <li class="list-group-item d-flex justify-content-between align-items-center" data-idx="{{ $idx }}"
                                                            wire:click="addItem({{ $result->id }})"
                                                            :class="{ 'active': hi === {{ $idx }} }"
                                                            style="cursor: pointer;">
                                                            <span>{{ $result->item_code }} - {{ $result->item_name }} <span class="ms-2 badge bg-warning text-dark">Qty: {{ $result->qty }}</span></span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif
                            <div class="col-md-6 mb-3">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select wire:model.live="status" id="status" class="form-control" {{ $isView || ($purchaseOrder && $purchaseOrder->status === 'Completed') ? 'disabled' : '' }}>
                                    <option value="" disabled>Select a status</option>
                                    @if(!$purchaseOrder || $status === 'Pending Approval' || $status === 'Save to Draft')
                                        <option value="Pending Approval" {{ $status === 'Pending Approval' ? 'selected' : '' }}>Pending Approval</option>
                                        <option value="Save to Draft" {{ $status === 'Save to Draft' ? 'selected' : '' }}>Save to Draft</option>
                                    @endif
                                    @if($purchaseOrder && ($status === 'Approved'||$status === 'In Progress'))
                                        <option value="In Progress" {{ $status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                    @endif
                                    @if($purchaseOrder && ($status === 'Completed'))
                                    <option value="Completed" {{ $status === 'Completed' ? 'selected' : '' }}>Completed</option>
                                    @endif
                                    @if($purchaseOrder && $status === 'Approved')
                                        <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Approved</option>
                                    @endif
                                    @if($purchaseOrder && $status === 'Rejected')
                                        <option value="Rejected" {{ $status === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                    @endif
                                </select>
                            </div>
 
                        </div>

                        <div class="selected-items mb-3">
                            <h6>Selected Items for PO:</h6>
                            @error('stackedItems')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        @if($purchaseOrder && $purchaseOrder->status === 'Completed')
                                            <th>Order Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Amount</th>
                                        @else
                                            <th>Qty on Hand</th>
                                            <th>Order Quantity</th>
                                            @if(!($isEdit && ($purchaseOrder && $purchaseOrder->status === 'In Progress')) || ($purchaseOrder && $purchaseOrder->status === 'In Progress'))
                                                <th>Unit Price</th>
                                                <th>Amount</th>
                                                
                                            @endif
                                            @if($status === 'In Progress')
                                                <th>Total Received</th>
                                                @if(!$isView)
                                                    <th>Receive Qty</th>
                                                    @if($isRevising)
                                                        <th>Actions</th>
                                                    @endif
                                                @endif
                                            @elseif(!$isView)
                                                <th class="col-actions">Actions</th>
                                            @endif
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stackedItems as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item['item']['item_code'] }}</td>
                                            <td x-data="{ 
                                                    showDescription: {{ !empty($stackedItems[$index]['more_description']) ? 'true' : 'false' }},
                                                    showMemo: false,
                                                    hoverTimeout: null
                                                }" 
                                                x-init="
                                                    $watch('showDescription', value => {
                                                        if (!value) {
                                                            $wire.set('stackedItems.{{ $index }}.more_description', null);
                                                        }
                                                    })
                                                ">
                                                <div class="d-flex gap-2" style="align-items: flex-start; position: relative;">
                                                    <div style="flex: 1; cursor: pointer; position: relative;" 
                                                         @mouseenter="hoverTimeout = setTimeout(() => { showMemo = true }, 1000)"
                                                         @mouseleave="clearTimeout(hoverTimeout); showMemo = false">
                                                        {{ $stackedItems[$index]['custom_item_name'] ?? $item['item']['item_name'] }}
                                                        @if(!empty($item['item']['memo']))
                                                            <div x-show="showMemo" 
                                                                 x-transition
                                                                 @mouseenter="clearTimeout(hoverTimeout); showMemo = true"
                                                                 @mouseleave="showMemo = false"
                                                                 style="position: absolute; background: #fff; border: 1px solid #ccc; padding: 6px 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); z-index: 1000; margin-top: 2px; width: auto; max-width: 200px; max-height: 150px; overflow-y: auto; font-size: 0.8em; white-space: pre-wrap; left: 0; top: 100%; word-wrap: break-word; text-align: left; line-height: 1.4;"
                                                                 @click.stop>
                                                                <strong style="font-size: 0.85em; display: block; margin-bottom: 3px;">Memo:</strong>
                                                                <div style="font-size: 0.8em; text-align: left; white-space: pre-wrap; word-wrap: break-word; line-height: 1.4;">{{ $item['item']['memo'] }}</div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @if(!$isView && (!$purchaseOrder || $purchaseOrder->status !== 'Completed'))
                                                        <button type="button" 
                                                            class="btn btn-sm p-0 px-1 flex-shrink-0" 
                                                            :class="showDescription ? 'btn-primary' : 'btn-outline-primary'"
                                                            @click="showDescription = !showDescription"
                                                            style="font-size: 0.7rem;">
                                                            <span x-text="showDescription ? '- desc' : '+ desc'"></span>
                                                        </button>
                                                        <button type="button" 
                                                            class="btn btn-sm p-0 px-1 btn-outline-secondary flex-shrink-0"
                                                            @click="$dispatch('open-edit-name-{{ $index }}')"
                                                            style="font-size: 0.7rem;">
                                                            Edit Name
                                                        </button>
                                                    @endif
                                                </div>
                                                @if(!empty($item['item']['details']))
                                                    <div class="mt-1 ms-3 text-muted" style="font-size: 0.85em;">
                                                        @foreach(explode("\n", $item['item']['details']) as $line)
                                                            @if(trim($line) !== '')
                                                                <div>• {{ $line }}</div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if($isView && !empty($stackedItems[$index]['more_description']))
                                                    <div class="mt-1 ms-3 text-muted" style="font-size: 0.85em;">
                                                        @foreach(explode("\n", $stackedItems[$index]['more_description']) as $line)
                                                            @if(trim($line) !== '')
                                                                <div>• {{ $line }}</div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if(!$isView && (!$purchaseOrder || $purchaseOrder->status !== 'Completed'))
                                                    <div x-show="showDescription" class="mt-2">
                                                        <textarea 
                                                            wire:model="stackedItems.{{ $index }}.more_description"
                                                            class="form-control form-control-sm"
                                                            rows="3"
                                                            placeholder="Enter additional description..."
                                                        ></textarea>
                                                    </div>
                                                    @if(!$isView && (!$purchaseOrder || $purchaseOrder->status !== 'Completed'))
                                                    <div x-data="{ open: false }" x-init="
                                                        window.addEventListener('open-edit-name-{{ $index }}', () => { open = true })
                                                    " class="mt-2">
                                                        <div x-show="open" class="card card-body p-2">
                                                            <label class="small mb-1">Edit Item Name (this order only)</label>
                                                            <input type="text" class="form-control form-control-sm" 
                                                                wire:model.defer="stackedItems.{{ $index }}.custom_item_name"
                                                                placeholder="Enter custom item name">
                                                            <div class="mt-2 d-flex gap-2">
                                                                <button type="button" class="btn btn-sm btn-primary" @click="open=false">Done</button>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="$wire.set('stackedItems.{{ $index }}.custom_item_name', null); open=false;">Reset</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                @endif
                                            </td>

                                            @if($purchaseOrder && $purchaseOrder->status === 'Completed')
                                                <td>
                                                    {{ $item['item_qty'] }}
                                                    <div class="text-muted small mt-1">
                                                    Current:{{ \App\Models\BatchTracking::where('item_id', $item['item']['id'])->sum('quantity') }}

                                                    </div>
                                                </td>
                                                <td>{{ number_format((float)($item['item_unit_price'] ?? 0), 2) }}</td> <!-- Display Unit Price from model -->
                                                <td>{{ number_format((float)($item['total_price_line_item'] ?? 0), 2) }}</td>
                                                @if(!$isView)
                                                @endif
                                                @else

                                            
                                                <td>{{ \App\Models\BatchTracking::where('item_id', $item['item']['id'])->sum('quantity') }}
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                        wire:model.lazy="stackedItems.{{ $index }}.item_qty" 
                                                        class="form-control rounded @error('stackedItems.'.$index.'.item_qty') is-invalid @enderror" 
                                                        min="1" 
                                                        {{ (
                                                            $isView 
                                                            || ($purchaseOrder && $purchaseOrder->status === 'In Progress' && !$isRevising)
                                                            || ($purchaseOrder && $purchaseOrder->status === 'Approved')
                                                            || (($item['total_qty_received'] ?? 0) > 0)
                                                        ) ? 'disabled' : '' }}>
                                                    @error('stackedItems.'.$index.'.item_qty')
                                                        <p class="text-danger">{{ $message }}</p>
                                                    @enderror
                                                </td>
                                                @if(!($isEdit && ($purchaseOrder && $purchaseOrder->status === 'In Progress')) || ($purchaseOrder && $purchaseOrder->status === 'In Progress'))
                                                <td>
                                                    <input type="number" 
                                                        step="0.01" 
                                                        wire:model.lazy="stackedItems.{{ $index }}.item_unit_price" 
                                                        class="form-control rounded @error('stackedItems.'.$index.'.item_unit_price') is-invalid @enderror" 
                                                        min="0" 
                                                        {{ (
                                                            $isView 
                                                            || ($purchaseOrder && $purchaseOrder->status === 'In Progress' && !$isRevising) 
                                                            || ($purchaseOrder && $purchaseOrder->status === 'Approved')
                                                            || (($item['total_qty_received'] ?? 0) > 0)
                                                        ) ? 'disabled' : '' }}>
                                                    @error('stackedItems.'.$index.'.item_unit_price')
                                                        <p class="text-danger">{{ $message }}</p>
                                                    @enderror
                                                </td>
                                                <td>{{ number_format((float)($item['total_price_line_item'] ?? 0), 2) }}</td>
                                                
                                                @endif
                                                @if($purchaseOrder && $purchaseOrder->status === 'In Progress')
                                                    <td>{{ $item['total_qty_received'] ?? 0 }}</td>
                                                    @if(!$isView)
                                                        <td class="col-actions">
                                                        <input type="number" step="1"
                                                            wire:model="stackedItems.{{ $index }}.receive_qty"
                                                            class="form-control form-control-sm rounded"
                                                            min="0" max="{{ (($item['total_qty_received'] ?? 0) != 0) ? ($item['item_qty'] - ($item['total_qty_received'] ?? 0)) : $item['item_qty'] }}" {{ ($purchaseOrder->status === 'Approved' || $purchaseOrder->status === 'Rejected' || (($item['total_qty_received'] ?? 0) == $item['item_qty'])) ? 'disabled' : '' }}>
                                                        </td>
                                                        @if($isRevising)
                                                            <td>
                                                                <button type="button" class="btn btn-danger btn-sm"
                                                                    wire:click="removeItem({{ $index }})"
                                                                    title="Delete" aria-label="Delete"
                                                                    {{ ($item['total_qty_received'] ?? 0) > 0 ? 'disabled' : '' }}>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                                        <path d="M5.5 5.5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m5 0a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5M2.5 3a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h2.5a1 1 0 0 1 0 2H2.5a1 1 0 0 1 0-2M3.5 4l1 10.5A2 2 0 0 0 6.49 16h3.02a2 2 0 0 0 1.99-1.5L12.5 4z"/>
                                                                    </svg>
                                                                </button>
                                                            </td>
                                                        @endif
                                                    @endif
                                                @elseif(!$isView)
                                                    <td class="col-actions">
                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                wire:click="removeItem({{ $index }})" 
                                                                title="Delete" aria-label="Delete"
                                                                {{ ($isView || (($purchaseOrder && $purchaseOrder->status === 'Approved') || ($purchaseOrder && $purchaseOrder->status === 'Rejected') || ($purchaseOrder && $purchaseOrder->status === 'In Progress' && !$isRevising))) ? 'disabled' : ''}}>
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                                <path d="M5.5 5.5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m5 0a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5M2.5 3a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h2.5a1 1 0 0 1 0 2H2.5a1 1 0 0 1 0-2M3.5 4l1 10.5A2 2 0 0 0 6.49 16h3.02a2 2 0 0 0 1.99-1.5L12.5 4z"/>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                @endif
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                        
                        @if($purchaseOrder && $purchaseOrder->status === 'Completed' && !$isView)
                        <div class="selected-items mb-3">
                            <h6>Update Cost/Price:</h6>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th>Update Cost</th>
                                        <th>Update Cash Price</th>
                                        <th>Update Term Price</th>
                                        <th>Update Customer Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stackedItems as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item['item']['item_code'] }}</td>
                                            <td>{{ $item['item']['item_name'] }}</td>
                                            <td>
                                                <input type="number" step="0.01"
                                                    wire:model="stackedItems.{{ $index }}.update_cost"
                                                    class="form-control form-control-sm rounded"
                                                    min="0">
                                                <div class="text-muted small mt-1">
                                                    Current: {{ number_format((float)($item['item']['cost'] ?? 0), 2) }}
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01"
                                                    wire:model="stackedItems.{{ $index }}.update_cash_price"
                                                    class="form-control form-control-sm rounded"
                                                    min="0">
                                                <div class="text-muted small mt-1">
                                                    Current: {{ number_format((float)($item['item']['cash_price'] ?? 0), 2) }}
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01"
                                                    wire:model="stackedItems.{{ $index }}.update_term_price"
                                                    class="form-control form-control-sm rounded"
                                                    min="0">
                                                <div class="text-muted small mt-1">
                                                    Current: {{ number_format((float)($item['item']['term_price'] ?? 0), 2) }}
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01"
                                                    wire:model="stackedItems.{{ $index }}.update_cust_price"
                                                    class="form-control form-control-sm rounded"
                                                    min="0">
                                                <div class="text-muted small mt-1">
                                                    Current: {{ number_format((float)($item['item']['cust_price'] ?? 0), 2) }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                        
                            <div class="text-end mb-3">
                                <div class="row justify-content-end">
                                    <div class="col-md-4">
                                        <div class="mb-2 d-flex justify-content-between">
                                            <span>Subtotal</span>
                                            <span>{{ $purchaseOrder ? ($purchaseOrder->supplierSnapshot->currency ?? $purchaseOrder->supplier->currency ?? 'RM') : ($supplier_id ? \App\Models\Supplier::find($supplier_id)->currency ?? 'RM' : 'RM') }} {{ number_format((float)$final_total_price, 2) }}</span>
                                        </div>
                                        <div class="mb-2 d-flex justify-content-between align-items-center">
                                            <span class="mb-0 me-2">Tax</span>
                                            <div class="input-group input-group-sm" style="width: 160px;">
                                                <input type="number" step="0.01" min="0" max="100" id="tax_rate" class="form-control rounded-start"
                                                    wire:model.lazy="tax_rate" {{ ($isView || ($purchaseOrder && ($purchaseOrder->status === 'Approved' || $purchaseOrder->status === 'Completed')) || ($purchaseOrder && $purchaseOrder->status === 'In Progress' && !$isRevising)) ? 'disabled' : '' }}>
                                                <span class="input-group-text rounded-end">%</span>
                                            </div>
                                        </div>
                                        <div class="mb-2 d-flex justify-content-between">
                                            <span>Tax Amount</span>
                                            <span>{{ $purchaseOrder ? ($purchaseOrder->supplierSnapshot->currency ?? $purchaseOrder->supplier->currency ?? 'RM') : ($supplier_id ? \App\Models\Supplier::find($supplier_id)->currency ?? 'RM' : 'RM') }} {{ number_format((float)$tax_amount, 2) }}</span>
                                        </div>
                                        <div class="mb-2 d-flex justify-content-between fw-bold">
                                            <span>Grand Total</span>
                                            <span>{{ $purchaseOrder ? ($purchaseOrder->supplierSnapshot->currency ?? $purchaseOrder->supplier->currency ?? 'RM') : ($supplier_id ? \App\Models\Supplier::find($supplier_id)->currency ?? 'RM' : 'RM') }} {{ number_format((float)$grand_total, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                @if(!$isView)
                                    @if(!$purchaseOrder || ($purchaseOrder && $purchaseOrder->status === 'Rejected'))
                                        <div class="text-end">
                                                <button type="submit" class="btn btn-success me-2" 
                                                        @if(empty($stackedItems)) disabled @endif>
                                                    {{ $purchaseOrder ? 
                                                        ($purchaseOrder->status === 'Rejected' ? 'Resubmit for Approval' : 
                                                            ($purchaseOrder->status !== 'Approved' ? 'Update Item' : '')) 
                                                        : 'Send for Approval' 
                                                    }}
                                                </button>
                                                @if(!$purchaseOrder)
                                                    <button type="button" class="btn btn-secondary me-2" wire:click="saveDraft" @if(empty($stackedItems)) disabled @endif>
                                                        Save Draft
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-info" wire:click.prevent="preview" {{ empty($stackedItems) ? 'disabled' : '' }}>
                                                    Preview
                                                </button>
                                        </div>
                                    @endif
                                @endif

                            </div>
                            @if($isView && $purchaseOrder)
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <a href="{{ route('purchase-orders') }}" class="btn btn-secondary">Back</a>
                                </div>
                                <div class="text-end">
                                    <a href="{{ route('purchase-orders.edit', $purchaseOrder->id) }}" class="btn btn-primary me-2">Edit</a>
                                    <a href="{{ route('print.purchase-order.preview', $purchaseOrder->id) }}" class="btn btn-info">Preview</a>
                                </div>
                            </div>
                            @endif

                        @if(!$isView)
                        <div class="d-flex justify-content-between align-items-center mt-10">
                            <div>
                                <a href="{{ route('purchase-orders') }}" class="btn btn-secondary">Back</a>
                            </div>
                            <div class="text-end">
                                @if($purchaseOrder && $status == "Pending Approval")
                                    @can('Approve PO')
                                        <button wire:click="changeStatus('Approved')" class="btn btn-success me-2">Approve</button>
                                        <button wire:click="changeStatus('Rejected')" class="btn btn-danger me-2">Reject</button>
                                    @endcan
                                    <button type="button" class="btn btn-info" wire:click="preview" {{ empty($stackedItems) ? 'disabled' : '' }}>
                                        Preview
                                    </button>
                                @endif

                                @if(!$isView && $purchaseOrder && ($purchaseOrder->status === 'Approved'))
                                    <button type="button" class="btn btn-success me-2" 
                                            wire:click="changeStatus('In Progress')">
                                            Set to In Progress
                                    </button>
                                    <button type="button" class="btn btn-info" wire:click="preview" {{ empty($stackedItems) ? 'disabled' : '' }}>
                                        Preview
                                    </button>
                                @endif

                                @if(!$isView && $purchaseOrder && ($status === 'In Progress' || $status === 'Completed'))
                                    <button type="button" class="btn btn-success me-2" 
                                            wire:click="receiveItems">
                                        {{ $status === 'Completed' ? 'Update Cost/Price' : 'Update Item' }}
                                    </button>
                                    @if($status === 'In Progress')
                                        <button type="button" class="btn btn-warning me-2" wire:click="toggleRevise">
                                            {{ $isRevising ? 'Cancel Revise' : 'Revise' }}
                                        </button>
                                        @if($isRevising)
                                            <button type="button" class="btn btn-primary me-2" wire:click="saveRevision">
                                                Save Revision
                                            </button>
                                        @endif
                                    @endif
                                    <button type="button" class="btn btn-info" wire:click="preview" {{ empty($stackedItems) ? 'disabled' : '' }}>
                                        Preview
                                    </button>
                                @endif
                                @if(!$isView && $purchaseOrder && $status === 'Save to Draft')
                                    <button type="button" class="btn btn-secondary me-2" wire:click="saveDraft" @if(empty($stackedItems)) disabled @endif>
                                        Save Draft
                                    </button>
                                    <button type="button" class="btn btn-primary" wire:click="changeStatus('Pending Approval')" @if(empty($stackedItems)) disabled @endif>
                                        Send for Approval
                                    </button>
                                @endif
                            </div>
                        </div>
                        @endif
                    </form>
                </div>

                <div x-show="$wire.showBatchModal" class="modal">
        <div class="modal-content">
            <h2>Batch Information</h2>
            
            <div class="form-group">
                <label>Batch Number</label>
                <input type="text" wire:model="batchNumber" readonly>
            </div>

            <div class="modal-footer">
                <button wire:click="$set('showBatchModal', false)">Cancel</button>
                <button wire:click="confirmBatchReceive">Confirm Receipt</button>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>

    <style>
        .search-results {
            position: relative;
        }
        .search-results ul {
            position: absolute;
            z-index: 100;
            background: white;
            width: 100%;
            border: 1px solid #ccc;
            max-height: 200px;
            overflow-y: auto;
        }
        .search-results ul li {
            padding: 10px;
            cursor: pointer;
        }
        .search-results ul li:hover {
            background-color: #f1f1f1;
        }
        /* Ensure keyboard-highlighted list items remain readable when hovered */
        .list-group .active { background-color: #0d6efd; color: #fff; }
        .list-group .active:hover { background-color: #0b5ed7; color: #fff; }
        /* Fixed table layout and column widths like DO form */
        .table { 
            table-layout: fixed;
            width: 100%;
        }
        /* Common styles for all cells */
        .table th, .table td {
            padding: 0.5rem;
            vertical-align: middle;
            word-wrap: break-word;
            min-width: 0; /* Allows columns to shrink below content width */
        }
        /* Header specific styles */
        .table th {
            font-size: 0.85em;
            line-height: 1.2;
            height: 40px; /* Fixed height for headers */
            vertical-align: middle;
            white-space: normal; /* Allow wrapping */
        }
        /* Column widths */
        .table th:nth-child(1), .table td:nth-child(1) { width: 3%; } /* # */
        .table th:nth-child(2), .table td:nth-child(2) { width: 10%; } /* Item Code */
        .table th:nth-child(3), .table td:nth-child(3) { width: 30%; } /* Item Name - largest */
        .table th:nth-child(4), .table td:nth-child(4) { width: 8%; } /* Qty On Hand */
        .table th:nth-child(5), .table td:nth-child(5) { width: 8%; } /* Order Qty */
        .table th:nth-child(6), .table td:nth-child(6) { width: 10%; } /* Unit Price */
        .table th:nth-child(7), .table td:nth-child(7) { width: 10%; } /* Amount */
        .table th:nth-child(7), .table td:nth-child(7) { width: 8%; } /* Total Received */
        .table th:nth-child(8), .table td:nth-child(8) { width: 8%; } /* Receive Qty */
        .table th:nth-child(9), .table td:nth-child(9) { width: 5%; } /* Actions */

        /* Input fields in table */
        .table input[type="text"],
        .table input[type="number"] {
            width: 100%;
            padding: 0.25rem;
            font-size: 0.9em;
        }

        /* Actions column */
        .col-actions { 
            width: 5%; 
            text-align: center;
            white-space: nowrap;
        }
    </style>
</div>
