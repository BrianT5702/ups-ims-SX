<div>
<div class="container-fluid my-3 px-2 px-md-3">
    <div class="do-form-page">
        <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold fs-5">{{ $isView ? 'View' : ($purchaseOrder ? 'Edit': 'Add' )}} Purchase Order @if(!$isView)<span class="text-muted small fw-normal ms-1">· Arrow keys move between line qty / price / description fields</span>@endif</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="addPO">
                        <div class="compact-form-typography">
                        <div class="do-header-fields">
                        {{-- Left: status (existing PO), supplier + snapshot + created by | Middle: date, remark | Right: PO no, ref --}}
                        <div class="row mb-3 align-items-start g-3 do-header-three-col">
                            <div class="col-xl-4 col-lg-12 d-flex flex-column" id="field-supplier_id" x-data="{ hi: 0 }">
                                @if($purchaseOrder)
                                    <div id="field-status">
                                        <label for="status">Status <span class="text-danger">*</span></label>
                                        <select wire:model.live="status" id="status" class="form-control" {{ $isView || ($purchaseOrder && $purchaseOrder->status === 'Completed') ? 'disabled' : '' }}>
                                            <option value="" disabled>Select a status</option>
                                            @if($status === 'Save to Draft' || $status === 'Pending Approval')
                                                <option value="Save to Draft" {{ $status === 'Save to Draft' ? 'selected' : '' }}>Save to Draft</option>
                                            @endif
                                            @if($purchaseOrder && $status !== 'Completed')
                                                <option value="In Progress" {{ $status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                            @endif
                                            @if($purchaseOrder && ($status === 'Completed'))
                                            <option value="Completed" {{ $status === 'Completed' ? 'selected' : '' }}>Completed</option>
                                            @endif
                                            {{-- LEGACY approval workflow (keep options only for existing rows still in these states)
                                            @if(!$purchaseOrder || $status === 'Pending Approval' || $status === 'Save to Draft')
                                                <option value="Pending Approval" {{ $status === 'Pending Approval' ? 'selected' : '' }}>Pending Approval</option>
                                                <option value="Save to Draft" {{ $status === 'Save to Draft' ? 'selected' : '' }}>Save to Draft</option>
                                            @endif
                                            @if($purchaseOrder && ($status === 'Approved'||$status === 'In Progress'))
                                                <option value="In Progress" {{ $status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                            @endif
                                            @if($purchaseOrder && $status === 'Approved')
                                                <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Approved</option>
                                            @endif
                                            @if($purchaseOrder && $status === 'Rejected')
                                                <option value="Rejected" {{ $status === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                            @endif
                                            --}}
                                            @if($purchaseOrder && $status === 'Pending Approval')
                                                <option value="Pending Approval" {{ $status === 'Pending Approval' ? 'selected' : '' }}>Pending Approval</option>
                                            @endif
                                            @if($purchaseOrder && $status === 'Approved')
                                                <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Approved</option>
                                            @endif
                                            @if($purchaseOrder && $status === 'Rejected')
                                                <option value="Rejected" {{ $status === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                            @endif
                                        </select>
                                    </div>
                                @endif

                                @php
                                    $showSupplierPicker = !$isView && (
                                        !$purchaseOrder
                                        || ($isRevising && $purchaseOrder && $purchaseOrder->status === 'In Progress')
                                    );
                                @endphp
                                @if($showSupplierPicker)
                                    <div class="{{ $purchaseOrder ? 'mt-2' : '' }}">
                                    <label for="supplier">Supplier <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.debounce.100ms="supplierSearchTerm" wire:input.debounce.200ms="searchSuppliers" id="searchSupplier"
                                        class="form-control rounded" placeholder="Search Supplier" autocomplete="off"
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

                                @if($isView || $purchaseOrder)
                                    @php
                                        $supDisplay = ($isRevising && $selectedSupplier instanceof \App\Models\Supplier)
                                            ? $selectedSupplier
                                            : null;
                                    @endphp
                                    <div class="do-customer-detail mt-2">
                                        @if($isView)
                                            <p class="fw-bold mb-1 do-customer-detail-title">{{ $purchaseOrder->supplierSnapshot->sup_name ?? $purchaseOrder->supplier->sup_name }}</p>
                                        @elseif($supDisplay)
                                            <p class="fw-bold mb-1 do-customer-detail-title">{{ $supDisplay->sup_name }}</p>
                                        @endif
                                        <p class="mb-0"><span class="text-muted">Currency:</span> {{ $supDisplay?->currency ?? $purchaseOrder->supplierSnapshot->currency ?? $purchaseOrder->supplier->currency ?? 'RM' }}</p>
                                        <p class="mb-0">{{ $supDisplay?->address_line1 ?? $purchaseOrder->supplierSnapshot->address_line1 ?? $purchaseOrder->supplier->address_line1 }}</p>
                                        <p class="mb-0">{{ $supDisplay?->address_line2 ?? $purchaseOrder->supplierSnapshot->address_line2 ?? $purchaseOrder->supplier->address_line2 }}</p>
                                        @if($supDisplay?->address_line3 ?? $purchaseOrder->supplierSnapshot->address_line3 ?? $purchaseOrder->supplier->address_line3)
                                            <p class="mb-0">{{ $supDisplay?->address_line3 ?? $purchaseOrder->supplierSnapshot->address_line3 ?? $purchaseOrder->supplier->address_line3 }}</p>
                                        @endif
                                        @if($supDisplay?->address_line4 ?? $purchaseOrder->supplierSnapshot->address_line4 ?? $purchaseOrder->supplier->address_line4)
                                            <p class="mb-0">{{ $supDisplay?->address_line4 ?? $purchaseOrder->supplierSnapshot->address_line4 ?? $purchaseOrder->supplier->address_line4 }}</p>
                                        @endif
                                    </div>
                                @endif

                                <div class="do-created-by mt-3 pt-2 border-top do-created-by-sep">
                                    <label for="created_by">Created By</label>
                                    <p class="mb-0"><b>{{ Auth::user()->name }}</b></p>
                                </div>
                            </div>

                            <div class="col-xl-4 col-lg-6 do-header-stack">
                                <div id="field-date">
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
                                <div class="mt-2">
                                    <label for="remark">Remark</label>
                                    <textarea wire:model="remark" id="remark" class="form-control rounded" rows="3" placeholder="Enter Remark (e.g., delivery address)" {{ $isView || ($purchaseOrder && (($purchaseOrder->status === 'Completed') || ($purchaseOrder->status === 'In Progress' && !$isRevising))) ? 'disabled' : ''}}></textarea>
                                </div>
                            </div>

                            <div class="col-xl-4 col-lg-6 do-header-stack">
                                <div id="field-po_num">
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
                                <div class="mt-2">
                                    <label for="ref_num">Reference Number</label>
                                    <input type="text" wire:model="ref_num" id="ref_num" class="form-control rounded" placeholder="Enter Reference Number" {{ $isView || ($purchaseOrder && (($purchaseOrder->status === 'Completed') || ($purchaseOrder->status === 'In Progress' && !$isRevising)))   ? 'disabled' : ''}}>
                                </div>
                            </div>
                        </div>
                        </div>

                        <div class="selected-items mb-3">
                            <h6>Selected Items for PO:</h6>
                            @error('stackedItems')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                            <table class="table table-bordered po-line-items-table {{ $purchaseOrder && $purchaseOrder->status === 'Completed' ? 'po-cols-completed' : '' }}">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        @if($purchaseOrder && $purchaseOrder->status === 'Completed')
                                            <th>Order Quantity</th>
                                            <th class="text-center">Unit Price</th>
                                            <th class="text-center">Amount</th>
                                        @else
                                            <th>Qty on Hand</th>
                                            <th>Order Quantity</th>
                                            @if(!($isEdit && ($purchaseOrder && $purchaseOrder->status === 'In Progress')) || ($purchaseOrder && $purchaseOrder->status === 'In Progress'))
                                                <th class="text-center">Unit Price</th>
                                                <th class="text-center">Amount</th>
                                                
                                            @endif
                                            @if($purchaseOrder && $purchaseOrder->status === 'In Progress')
                                                <th>Total Received</th>
                                                @if(!$isView)
                                                    {{-- LEGACY: per-line partial receipt input
                                                    <th>Receive Qty</th>
                                                    --}}
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
                                        <tr data-po-row-index="{{ $index }}" wire:key="po-line-{{ $index }}-{{ $item['item']['id'] ?? $index }}">
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
                                                    <div x-data="{ showMemo: false, hoverTimeout: null }"
                                                         style="flex: 1; cursor: pointer; position: relative;"
                                                         @mouseenter="hoverTimeout = setTimeout(() => { showMemo = true }, 800)"
                                                         @mouseleave="clearTimeout(hoverTimeout); showMemo = false">
                                                        <span wire:key="item-name-{{ $index }}-{{ $stackedItems[$index]['custom_item_name'] ?? 'default' }}">
                                                            {{ $stackedItems[$index]['custom_item_name'] ?? $item['item']['item_name'] }}
                                                        </span>
                                                        @if(!empty($item['item']['memo']))
                                                            <div x-show="showMemo"
                                                                 x-transition
                                                                 class="memo-tooltip"
                                                                 @click.stop>
                                                                <div class="memo-tooltip-body">{{ $item['item']['memo'] }}</div>
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
                                                {{-- Hide item master details in PO form UI (preview/print still uses item->details + more_description). --}}
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
                                                            data-po-role="desc"
                                                        ></textarea>
                                                    </div>
                                                    @if(!$isView && (!$purchaseOrder || $purchaseOrder->status !== 'Completed'))
                                                    <div x-data="{ open: false }" x-init="
                                                        window.addEventListener('open-edit-name-{{ $index }}', () => { open = true })
                                                    " class="mt-2">
                                                        <div x-show="open" class="card card-body p-2">
                                                            <label class="small mb-1">Edit Item Name (this order only)</label>
                                                            <input type="text" 
                                                                id="custom-name-input-{{ $index }}"
                                                                class="form-control form-control-sm" 
                                                                wire:model.live="stackedItems.{{ $index }}.custom_item_name"
                                                                placeholder="Enter custom item name"
                                                                data-po-role="custom_name"
                                                                value="{{ $stackedItems[$index]['custom_item_name'] ?? '' }}">
                                                            <div class="mt-2 d-flex gap-2">
                                                                <button type="button" class="btn btn-sm btn-primary" @click="$wire.$refresh(); open=false;">Done</button>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="$wire.set('stackedItems.{{ $index }}.custom_item_name', null); $wire.$refresh(); open=false;">Reset</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                @endif
                                            </td>

                                            @if($purchaseOrder && $purchaseOrder->status === 'Completed')
                                                <td>
                                                    {{ $item['item_qty'] }}
                                                    @php
                                                        $currentOnHand = (float) \App\Models\BatchTracking::where('item_id', $item['item']['id'])->sum('quantity');
                                                        $receivedQty = (float) ($item['total_qty_received'] ?? 0);
                                                        $qtyOnHandBeforeThisPO = $currentOnHand - $receivedQty;
                                                        $currentForDisplay = $isRevising ? $qtyOnHandBeforeThisPO : $currentOnHand;
                                                    @endphp
                                                    <div class="text-muted small mt-1">
                                                        Current:{{ number_format((float)$currentForDisplay, 2, '.', '') }}
                                                    </div>
                                                </td>
                                                <td>{{ number_format((float)($item['item_unit_price'] ?? 0), 2) }}</td> <!-- Display Unit Price from model -->
                                                <td>{{ number_format((float)($item['total_price_line_item'] ?? 0), 2) }}</td>
                                                @if(!$isView)
                                                @endif
                                                @else

                                            
                                                @php
                                                    $currentOnHand = (float) \App\Models\BatchTracking::where('item_id', $item['item']['id'])->sum('quantity');
                                                    $receivedQty = (float) ($item['total_qty_received'] ?? 0);
                                                    // Show "qty on hand before this PO's received quantity" so users can revise order qty
                                                    // without being confused by the stock that was already posted.
                                                    $qtyOnHandBeforeThisPO = $currentOnHand - $receivedQty;
                                                @endphp
                                                <td>{{ number_format($qtyOnHandBeforeThisPO, 2, '.', '') }}</td>
                                                <td>
                                                    <input type="number" 
                                                        wire:model.lazy="stackedItems.{{ $index }}.item_qty" 
                                                        class="form-control rounded @error('stackedItems.'.$index.'.item_qty') is-invalid @enderror" 
                                                        data-po-role="qty"
                                                        min="0.01" 
                                                        step="0.01" 
                                                        {{ (
                                                            $isView 
                                                            || ($purchaseOrder && $purchaseOrder->status === 'Approved')
                                                            || (((float)($item['total_qty_received'] ?? 0) > 0.00001) && !$isRevising)
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
                                                        data-po-role="price"
                                                        min="0" 
                                                        {{ (
                                                            $isView 
                                                            || ($purchaseOrder && $purchaseOrder->status === 'Approved')
                                                            || (((float)($item['total_qty_received'] ?? 0) > 0.00001) && !$isRevising)
                                                        ) ? 'disabled' : '' }}>
                                                    @error('stackedItems.'.$index.'.item_unit_price')
                                                        <p class="text-danger">{{ $message }}</p>
                                                    @enderror
                                                </td>
                                                <td>{{ number_format((float)($item['total_price_line_item'] ?? 0), 2) }}</td>
                                                
                                                @endif
                                                @if($purchaseOrder && $purchaseOrder->status === 'In Progress')
                                                    <td>{{ number_format((float)($item['item_qty'] ?? 0), 2, '.', '') }}</td>
                                                    @if(!$isView)
                                                        {{-- LEGACY: Receive Qty column (receiveItems used this; now full remainder is applied on Update Item)
                                                        <td class="col-actions">
                                                        <input type="number" step="0.01"
                                                            wire:model="stackedItems.{{ $index }}.receive_qty"
                                                            class="form-control form-control-sm rounded"
                                                            min="0" max="{{ max(0, round((float)($item['item_qty'] ?? 0) - (float)($item['total_qty_received'] ?? 0), 4)) }}" {{ ($purchaseOrder->status === 'Approved' || $purchaseOrder->status === 'Rejected' || (abs((float)($item['item_qty'] ?? 0) - (float)($item['total_qty_received'] ?? 0)) < 0.00001)) ? 'disabled' : '' }}>
                                                        </td>
                                                        --}}
                                                        @if($isRevising)
                                                            <td>
                                                                <button type="button" class="btn btn-danger btn-sm"
                                                                    wire:click="removeItem({{ $index }})"
                                                                    title="Delete" aria-label="Delete"
                                                                    {{-- allow deletion during revise mode --}}
                                                                    >
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

                            @if(!$isView)
                                @if(
                                    !$purchaseOrder
                                    || ($purchaseOrder && ($purchaseOrder->status === 'Rejected'))
                                    || ($purchaseOrder && ($purchaseOrder->status === 'Save to Draft'))
                                    || ($purchaseOrder && ($purchaseOrder->status === 'In Progress' && $isRevising))
                                )
                                    <div class="row mb-0 mt-3 pt-3 border-top">
                                        <div class="col-md-8 col-lg-6" x-data="{ hi: 0 }">
                                            <label for="po-search-item-bottom" class="fw-semibold">Search Items <span class="text-muted small fw-normal">(F2)</span></label>
                                            <div class="btn-group btn-group-sm my-2" role="group" aria-label="Search by field">
                                                <input type="radio" class="btn-check" name="po_item_search_field" id="po-search-by-code" value="code" wire:model.live="itemSearchField" autocomplete="off">
                                                <label class="btn btn-outline-secondary" for="po-search-by-code">By code</label>
                                                <input type="radio" class="btn-check" name="po_item_search_field" id="po-search-by-name" value="name" wire:model.live="itemSearchField" autocomplete="off">
                                                <label class="btn btn-outline-secondary" for="po-search-by-name">By name</label>
                                            </div>
                                            <input type="text" wire:model.debounce.100ms="itemSearchTerm" wire:input.debounce.200ms="searchItems" id="po-search-item-bottom"
                                                class="form-control rounded"
                                                placeholder="{{ $itemSearchField === 'code' ? 'Search by item code…' : 'Search by item name…' }}" autocomplete="off"
                                                x-on:input="hi = 0"
                                                x-on:keydown.arrow-down.prevent="(() => { const list = $refs.poItemListBottom; const items = list ? list.querySelectorAll('li') : []; if(items.length===0) return; hi = Math.min(hi + 1, items.length - 1); $nextTick(() => { const el = items[hi]; if(!el) return; const elTop = el.offsetTop; const elBottom = elTop + el.offsetHeight; const viewTop = list.scrollTop; const viewBottom = viewTop + list.clientHeight; if (elTop < viewTop) { list.scrollTop = elTop; } else if (elBottom > viewBottom) { list.scrollTop = elBottom - list.clientHeight; } }); })()"
                                                x-on:keydown.arrow-up.prevent="(() => { const list = $refs.poItemListBottom; const items = list ? list.querySelectorAll('li') : []; if(items.length===0) return; hi = Math.max(hi - 1, 0); $nextTick(() => { const el = items[hi]; if(!el) return; const elTop = el.offsetTop; const elBottom = elTop + el.offsetHeight; const viewTop = list.scrollTop; const viewBottom = viewTop + list.clientHeight; if (elTop < viewTop) { list.scrollTop = elTop; } else if (elBottom > viewBottom) { list.scrollTop = elBottom - list.clientHeight; } }); })()"
                                                x-on:keydown.enter.prevent="(() => { const list = $refs.poItemListBottom; const items = list ? list.querySelectorAll('li') : []; const el = items && items[hi]; if(el) el.click(); })()">

                                            @if(count($itemSearchResults) > 0)
                                                <div class="search-results mt-2">
                                                    <ul class="list-group" x-ref="poItemListBottom">
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
                                    </div>
                                @endif
                            @endif

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
                                                    data-po-role="upd_cost"
                                                    min="0">
                                                <div class="text-muted small mt-1">
                                                    Current: {{ number_format((float)($item['item']['cost'] ?? 0), 2) }}
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01"
                                                    wire:model="stackedItems.{{ $index }}.update_cash_price"
                                                    class="form-control form-control-sm rounded"
                                                    data-po-role="upd_cash"
                                                    min="0">
                                                <div class="text-muted small mt-1">
                                                    Current: {{ number_format((float)($item['item']['cash_price'] ?? 0), 2) }}
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01"
                                                    wire:model="stackedItems.{{ $index }}.update_term_price"
                                                    class="form-control form-control-sm rounded"
                                                    data-po-role="upd_term"
                                                    min="0">
                                                <div class="text-muted small mt-1">
                                                    Current: {{ number_format((float)($item['item']['term_price'] ?? 0), 2) }}
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01"
                                                    wire:model="stackedItems.{{ $index }}.update_cust_price"
                                                    class="form-control form-control-sm rounded"
                                                    data-po-role="upd_cust"
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
                                                        ($purchaseOrder->status === 'Rejected' ? 'Save' : 
                                                            ($purchaseOrder->status !== 'Approved' ? 'Update Item' : '')) 
                                                        : 'Save' 
                                                    }}
                                                </button>
                                                {{-- LEGACY top-bar labels: "Send for Approval" (new), "Resubmit for Approval" (rejected) --}}
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
                                    @if($purchaseOrder->status === 'Completed')
                                        <a href="{{ route('purchase-orders.edit', $purchaseOrder->id) }}?update_cost=1" class="btn btn-success me-2">
                                            Update Cost/Price
                                        </a>
                                    @endif
                                    <a href="{{ route('purchase-orders.edit', $purchaseOrder->id) }}" class="btn btn-primary me-2">Edit</a>
                                    <a href="{{ route('print.purchase-order.preview', \App\Support\TenantDatabase::previewRouteParams($purchaseOrder->id)) }}" class="btn btn-info">Preview</a>
                                </div>
                            </div>
                            @endif

                        @if(!$isView)
                        <div class="d-flex justify-content-between align-items-center mt-10">
                            <div>
                                <a href="{{ route('purchase-orders') }}" class="btn btn-secondary">Back</a>
                            </div>
                            <div class="text-end">
                                {{-- LEGACY: approval step (uncomment to restore manager Approve/Reject for Pending Approval POs)
                                @if($purchaseOrder && $status == "Pending Approval")
                                    @can('Approve PO')
                                        <button wire:click="changeStatus('Approved')" class="btn btn-success me-2">Approve</button>
                                        <button wire:click="changeStatus('Rejected')" class="btn btn-danger me-2">Reject</button>
                                    @endcan
                                    <button type="button" class="btn btn-info" wire:click="preview" {{ empty($stackedItems) ? 'disabled' : '' }}>
                                        Preview
                                    </button>
                                @endif
                                --}}
                                @if($purchaseOrder && $status == "Pending Approval")
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
                                    {{-- Hide "Update Item" while the user is revising to avoid confusion. --}}
                                    @if($status === 'Completed' || !$isRevising)
                                        <button type="button" class="btn btn-success me-2" 
                                                wire:click="receiveItems">
                                            {{ $status === 'Completed' ? 'Update Cost/Price' : 'Update Item' }}
                                        </button>
                                    @endif
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
                                    <button type="button" class="btn btn-primary" wire:click="changeStatus('In Progress')" @if(empty($stackedItems)) disabled @endif>
                                        Continue (In Progress)
                                    </button>
                                    {{-- LEGACY: wire:click="changeStatus('Pending Approval')">Send for Approval --}}
                                @endif
                            </div>
                        </div>
                        @endif
                        </div>
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
        .do-form-page {
            max-width: 1080px;
            margin-left: auto;
            margin-right: auto;
        }

        .compact-form-typography label {
            font-size: 0.82em;
            margin-bottom: 0.2rem;
        }
        .compact-form-typography .form-control,
        .compact-form-typography .form-select,
        .compact-form-typography textarea,
        .compact-form-typography input {
            font-size: 0.86em;
        }
        .compact-form-typography p,
        .compact-form-typography b,
        .compact-form-typography span,
        .compact-form-typography small {
            font-size: 0.85em;
        }
        .compact-form-typography .table th,
        .compact-form-typography .table td {
            font-size: 0.82em;
        }
        /* Match DO items grid typography (do-fixed-table) for PO line items */
        .compact-form-typography .po-line-items-table th,
        .compact-form-typography .po-line-items-table td {
            font-size: 0.82em;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .compact-form-typography .po-line-items-table th {
            font-size: 0.85em;
            line-height: 1.2;
            height: auto;
            min-height: 0;
        }
        /* Item name is wrapped in <span>; global .compact-form-typography span uses 0.85em */
        .compact-form-typography .po-line-items-table td:nth-child(3) span {
            font-size: inherit;
        }
        .compact-form-typography .po-line-items-table input[type="text"],
        .compact-form-typography .po-line-items-table input[type="number"],
        .compact-form-typography .po-line-items-table textarea {
            font-size: 0.8em;
            padding: 0.12rem 0.22rem;
        }
        .compact-form-typography .po-line-items-table .form-control,
        .compact-form-typography .po-line-items-table .form-control-sm,
        .compact-form-typography .po-line-items-table .form-select,
        .compact-form-typography .po-line-items-table .form-select-sm {
            font-size: 0.8em;
        }
        .compact-form-typography .po-line-items-table .btn-sm {
            font-size: 0.8em;
        }

        /* Like DO: hide number spinners; arrow keys are used to move between cells, not step values */
        .compact-form-typography .po-line-items-table input[type="number"][data-po-role="qty"]::-webkit-outer-spin-button,
        .compact-form-typography .po-line-items-table input[type="number"][data-po-role="qty"]::-webkit-inner-spin-button,
        .compact-form-typography .po-line-items-table input[type="number"][data-po-role="price"]::-webkit-outer-spin-button,
        .compact-form-typography .po-line-items-table input[type="number"][data-po-role="price"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .compact-form-typography .po-line-items-table input[type="number"][data-po-role="qty"],
        .compact-form-typography .po-line-items-table input[type="number"][data-po-role="price"] {
            -moz-appearance: textfield;
            appearance: textfield;
        }
        .compact-form-typography .selected-items table:not(.po-line-items-table) input[type="number"][data-po-role]::-webkit-outer-spin-button,
        .compact-form-typography .selected-items table:not(.po-line-items-table) input[type="number"][data-po-role]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .compact-form-typography .selected-items table:not(.po-line-items-table) input[type="number"][data-po-role] {
            -moz-appearance: textfield;
            appearance: textfield;
        }

        /* Wider Item Code, narrower Amount; diff moved from Amount → Item Code (vs default 10% / 10%) */
        .compact-form-typography .po-line-items-table:not(.po-cols-completed) th:nth-child(2),
        .compact-form-typography .po-line-items-table:not(.po-cols-completed) td:nth-child(2) {
            width: 13%;
        }
        .compact-form-typography .po-line-items-table:not(.po-cols-completed) th:nth-child(7),
        .compact-form-typography .po-line-items-table:not(.po-cols-completed) td:nth-child(7) {
            width: 7%;
        }
        .compact-form-typography .po-line-items-table.po-cols-completed th:nth-child(2),
        .compact-form-typography .po-line-items-table.po-cols-completed td:nth-child(2) {
            width: 13%;
        }
        .compact-form-typography .po-line-items-table.po-cols-completed th:nth-child(6),
        .compact-form-typography .po-line-items-table.po-cols-completed td:nth-child(6) {
            width: 7%;
        }

        /* # column: wide enough for two-digit line numbers (10–26); nowrap avoids wrap under table-layout:fixed */
        .compact-form-typography .po-line-items-table th:nth-child(1),
        .compact-form-typography .po-line-items-table td:nth-child(1) {
            width: 2%;
            min-width: 2.75em;
            white-space: nowrap;
            text-align: center;
            word-wrap: normal;
            overflow-wrap: normal;
        }
        .compact-form-typography .po-line-items-table th:nth-child(3),
        .compact-form-typography .po-line-items-table td:nth-child(3) {
            width: 31%;
        }

        /* Update Cost/Price table: same # column treatment (no po-line-items-table class) */
        .compact-form-typography .selected-items .table:not(.po-line-items-table) th:nth-child(1),
        .compact-form-typography .selected-items .table:not(.po-line-items-table) td:nth-child(1) {
            width: 5%;
            min-width: 2.75em;
            white-space: nowrap;
            text-align: center;
            word-wrap: normal;
            overflow-wrap: normal;
        }

        .do-header-fields label {
            font-size: 0.8em;
            margin-bottom: 0.1rem;
        }
        .do-header-fields .form-control,
        .do-header-fields .form-select {
            font-size: 0.8em;
        }
        .do-header-fields p,
        .do-header-fields b {
            font-size: 1.0em;
        }
        .do-customer-detail {
            margin-top: 0.4rem;
            padding: 0.35rem 0.5rem 0.35rem 0.65rem;
            border-left: 3px solid #c5d4e8;
            background: #f8fafc;
            border-radius: 0 4px 4px 0;
            font-size: 0.78em;
            line-height: 1.35;
        }
        .do-customer-detail-title {
            font-size: 0.95em;
        }
        .do-created-by p {
            padding-top: 0.12rem;
        }
        .do-created-by-sep {
            border-color: #dee2e6 !important;
        }
        @media (min-width: 1200px) {
            .do-header-three-col .do-header-stack {
                min-height: 100%;
            }
        }

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
        .table th:nth-child(7), .table td:nth-child(7) { width: 10%; } /* Amount / Total Received (layout varies by row) */
        /* LEGACY Receive Qty column: .table th:nth-child(8), .table td:nth-child(8) { width: 8%; } */
        .table th:nth-child(8), .table td:nth-child(8) { width: 5%; } /* Actions */
        .table th:nth-child(9), .table td:nth-child(9) { width: 5%; } /* Actions when extra column */

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
    <script>
        (function () {
            var formSelector = 'form[wire\\:submit\\.prevent="addPO"]';

            function getPoForm() {
                return document.querySelector(formSelector);
            }

            document.addEventListener('keydown', function (e) {
                if (e.key !== 'F2') return;
                var form = e.target.closest(formSelector);
                if (!form) {
                    form = getPoForm();
                }
                if (!form) return;
                var target = form.querySelector('#po-search-item-bottom');
                if (!target || target.disabled) return;
                e.preventDefault();
                target.focus();
                if (typeof target.scrollIntoView === 'function') {
                    target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });

            (function () {
                var registered = false;
                function registerFocusQtyAfterAdd() {
                    if (typeof Livewire === 'undefined' || registered) return;
                    registered = true;
                    Livewire.on('po-focus-qty-row', function (event) {
                        var payload = event && event[0];
                        var rowIndex = payload && payload.rowIndex;
                        if (rowIndex === null || rowIndex === undefined) return;

                        setTimeout(function () {
                            var form = getPoForm();
                            if (!form) return;
                            var row = form.querySelector('.po-line-items-table tbody tr[data-po-row-index="' + rowIndex + '"]');
                            if (!row) return;
                            var qtyInput = row.querySelector('[data-po-role="qty"]:not([disabled])');
                            if (!qtyInput) return;
                            qtyInput.focus();
                            if (typeof qtyInput.select === 'function') {
                                qtyInput.select();
                            }
                            if (typeof qtyInput.scrollIntoView === 'function') {
                                qtyInput.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                            }
                        }, 0);
                    });
                }
                document.addEventListener('livewire:init', registerFocusQtyAfterAdd);
                if (document.readyState !== 'loading' && typeof Livewire !== 'undefined') {
                    registerFocusQtyAfterAdd();
                }
            })();

            // Arrow keys inside PO line grid / update-cost grid (same idea as DO form data-do-role).
            // Left/Right: prev/next field in row; Up/Down: same field in prev/next row.
            // Number inputs: ArrowUp/Down never step the value (only move or no-op).
            (function () {
                function fieldVisible(el) {
                    if (!el || el.disabled) return false;
                    if (typeof el.checkVisibility === 'function') {
                        return el.checkVisibility({ checkOpacity: true, checkVisibilityCSS: true });
                    }
                    return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
                }

                function focusPoField(row, role) {
                    if (!row) return false;
                    var target = row.querySelector('[data-po-role="' + role + '"]:not([disabled])');
                    if (!target || !fieldVisible(target)) return false;
                    target.focus();
                    if (typeof target.select === 'function' && target.tagName === 'INPUT' && target.type !== 'date') {
                        try {
                            target.select();
                        } catch (err) { /* ignore */ }
                    }
                    return true;
                }

                document.addEventListener('keydown', function (e) {
                    if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) return;
                    if (e.defaultPrevented) return;
                    if (e.ctrlKey || e.metaKey || e.altKey) return;

                    var source = e.target;
                    if (!source || !source.matches('[data-po-role]')) return;

                    var form = source.closest(formSelector);
                    if (!form) return;

                    var currentRow = source.closest('tbody tr');
                    if (!currentRow) return;

                    var tableEl = currentRow.closest('table');
                    if (!tableEl) return;

                    var roles;
                    if (tableEl.classList.contains('po-line-items-table')) {
                        roles = ['qty', 'price', 'desc', 'custom_name'];
                    } else {
                        roles = ['upd_cost', 'upd_cash', 'upd_term', 'upd_cust'];
                    }

                    var rows = Array.from(tableEl.querySelectorAll('tbody tr'));
                    var rowIdx = rows.indexOf(currentRow);
                    if (rowIdx === -1) return;

                    var currentRole = source.getAttribute('data-po-role');
                    var roleIdx = roles.indexOf(currentRole);
                    if (roleIdx === -1) return;

                    var moved = false;

                    if (e.key === 'ArrowLeft') {
                        for (var li = roleIdx - 1; li >= 0; li--) {
                            if (focusPoField(currentRow, roles[li])) {
                                moved = true;
                                break;
                            }
                        }
                    } else if (e.key === 'ArrowRight') {
                        for (var ri = roleIdx + 1; ri < roles.length; ri++) {
                            if (focusPoField(currentRow, roles[ri])) {
                                moved = true;
                                break;
                            }
                        }
                    } else if (e.key === 'ArrowUp') {
                        for (var ur = rowIdx - 1; ur >= 0; ur--) {
                            if (focusPoField(rows[ur], currentRole)) {
                                moved = true;
                                break;
                            }
                        }
                    } else if (e.key === 'ArrowDown') {
                        for (var dr = rowIdx + 1; dr < rows.length; dr++) {
                            if (focusPoField(rows[dr], currentRole)) {
                                moved = true;
                                break;
                            }
                        }
                    }

                    var isNumberPoField = source.tagName === 'INPUT' && source.type === 'number' && source.hasAttribute('data-po-role');
                    if (moved) {
                        e.preventDefault();
                    } else if (isNumberPoField && (e.key === 'ArrowUp' || e.key === 'ArrowDown')) {
                        e.preventDefault();
                    }
                });
            })();
        })();
    </script>
</div>