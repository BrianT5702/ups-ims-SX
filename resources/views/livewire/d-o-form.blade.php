<div>
    <div class="container-fluid my-3">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold fs-5">Delivery Order</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="addDO">
                        <div class="row mb-3">
                            <div class="col-md-4" x-data="{ hi: 0 }">
                                @if(!$deliveryOrder || !$isView)
                                    <label for="customer">Customer <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.debounce.100ms="customerSearchTerm" 
                                        wire:input.debounce.200ms="searchCustomers" 
                                        id="searchCustomer" 
                                        class="form-control rounded" 
                                        placeholder="Search Customer" 
                                        {{ $isView ? 'disabled' : '' }} 
                                        autocomplete="off"
                                        x-on:keydown.arrow-down.prevent="(() => { const list = $refs.custList; const items = list ? list.querySelectorAll('li') : []; if(items.length===0) return; hi = Math.min(hi + 1, items.length - 1); $nextTick(() => { const el = items[hi]; if(!el) return; const elTop = el.offsetTop; const elBottom = elTop + el.offsetHeight; const viewTop = list.scrollTop; const viewBottom = viewTop + list.clientHeight; if (elTop < viewTop) { list.scrollTop = elTop; } else if (elBottom > viewBottom) { list.scrollTop = elBottom - list.clientHeight; } }); })()"
                                        x-on:keydown.arrow-up.prevent="(() => { const list = $refs.custList; const items = list ? list.querySelectorAll('li') : []; if(items.length===0) return; hi = Math.max(hi - 1, 0); $nextTick(() => { const el = items[hi]; if(!el) return; const elTop = el.offsetTop; const elBottom = elTop + el.offsetHeight; const viewTop = list.scrollTop; const viewBottom = viewTop + list.clientHeight; if (elTop < viewTop) { list.scrollTop = elTop; } else if (elBottom > viewBottom) { list.scrollTop = elBottom - list.clientHeight; } }); })()"
                                        x-on:keydown.enter.prevent="(() => { const list = $refs.custList; const items = list ? list.querySelectorAll('li') : []; const el = items && items[hi]; if(el) el.click(); })()">
                                    @error('cust_id')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                    @if(count($customerSearchResults) > 0)
                                        <div class="search-results mt-2">
                                            <ul class="list-group" x-ref="custList">  
                                                @foreach($customerSearchResults as $idx => $custResult)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center"
                                                        wire:click="selectCustomer({{ $custResult->id }})"
                                                        :class="{ 'active': hi === {{ $idx }} }">
                                                        <span>{{ $custResult->account }} - {{ $custResult->cust_name }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                @endif

                                @if($isView || ($deliveryOrder && $deliveryOrder->customer))
                                    <div>
                                        <p class="fw-bold mb-2">{{ $deliveryOrder->customerSnapshot->cust_name ?? $deliveryOrder->customer->cust_name }}</p>
                                        <p class="mb-1"><strong>Currency:</strong> {{ $deliveryOrder->customerSnapshot->currency ?? $deliveryOrder->customer->currency ?? 'RM' }}</p>
                                        <p class="mb-1">{{ $deliveryOrder->customerSnapshot->address_line1 ?? $deliveryOrder->customer->address_line1 }}</p>
                                        <p class="mb-1">{{ $deliveryOrder->customerSnapshot->address_line2 ?? $deliveryOrder->customer->address_line2 }}</p>
                                        @if($deliveryOrder->customerSnapshot->address_line3 ?? $deliveryOrder->customer->address_line3)
                                            <p class="mb-1">{{ $deliveryOrder->customerSnapshot->address_line3 ?? $deliveryOrder->customer->address_line3 }}</p>
                                        @endif
                                        @if($deliveryOrder->customerSnapshot->address_line4 ?? $deliveryOrder->customer->address_line4)
                                            <p class="mb-1">{{ $deliveryOrder->customerSnapshot->address_line4 ?? $deliveryOrder->customer->address_line4 }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>



                            <div class="col-md-4">
                                <label for="date">Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="date" id="date" class="form-control rounded" 
                                    placeholder="dd/mm/yyyy"
                                    {{ $isView ? 'disabled' : '' }}>
                                @error('date') <p class="text-danger">{{ $message }}</p> @enderror
                            </div>
                            <div class="col-md-4">
                                    <label for="do_num">DO Number <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="do_num" id="do_num" class="form-control rounded" {{ $isView ? 'disabled' : '' }} placeholder="Enter DO Number">
                                    @error('do_num') <p class="text-danger">{{ $message }}</p> @enderror
                                </div>
                        </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="ref_num">Reference Number</label>
                                    <input type="text" wire:model="ref_num" id="ref_num" class="form-control rounded" {{ $isView ? 'disabled' : '' }} placeholder="Enter Reference Number">
                                    @error('ref_num') <p class="text-danger">{{ $message }}</p> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="cust_po">Customer PO Number <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="cust_po" id="cust_po" class="form-control rounded" {{ $isView ? 'disabled' : '' }} placeholder="Enter Cust PO Number" autocomplete="off">
                                    @error('cust_po') <p class="text-danger">{{ $message }}</p> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="remark">Remark</label>
                                    <textarea wire:model="remark" id="remark" class="form-control rounded" rows="3" {{ $isView ? 'disabled' : '' }} placeholder="Enter Remark (e.g., delivery address)"></textarea>
                                    @error('remark') <p class="text-danger">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                            @if(!$isView)
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
                                                        <span>{{ $result->item_code }} - {{ $result->item_name }} 
                                                            @if($result->qty > 0)
                                                                <span class="ms-2 badge bg-success text-white">Qty: {{ $result->qty }}</span>
                                                            @else
                                                                <span class="ms-2 badge bg-warning text-dark">Out of Stock</span>
                                                            @endif
                                                        </span>
                                                    </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @elseif(!empty($itemSearchTerm))
                                            <div class="search-results mt-2">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i> No items found matching your search.
                                                </div>
                                            </div>
                                        @endif
                                    
                                </div>
                                @endif
                                

                                <div class="col-md-3">
                                    <label for="salesman">Salesperson <span class="text-danger">*</span></label>
                                    <select id="salesman" class="form-select rounded" wire:model.live="salesman_id" {{ ($isView || empty($cust_id)) ? 'disabled' : '' }}>
                                        <option value="">{{ empty($cust_id) ? 'Select a customer first' : 'Select Salesperson' }}</option>
                                        @foreach($salesmen as $sm)
                                            <option value="{{ $sm->id }}">{{ $sm->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('salesman_id')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>


                                <div class="col-md-3 pt-3">
                                    <label for="created_by">Created By</label>
                                    <p><b> {{ Auth::user()->name}}</b></p>
                                </div>

                                


                            </div>

                            <div class="selected-items mb-3">
                                <h6>Selected Item for DO:</h6>
                                @error('stackedItems')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item Code</th>
                                            <th>Item Name</th>
                                            <th>Qty On Hand</th>
                                            <th>Order Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Amount</th>
                            @if(!$isView)
                                <th class="col-actions">Actions</th>
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
                                                    @if(!$isView)
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
                                                @if(!$isView)
                                                    <div x-show="showDescription" class="mt-2">
                                                        <textarea 
                                                            wire:model="stackedItems.{{ $index }}.more_description"
                                                            class="form-control form-control-sm"
                                                            rows="3"
                                                            placeholder="Enter additional description..."
                                                        ></textarea>
                                                    </div>
                                                    @if(!$isView)
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
                                            <td>{{ ($item['item']['qty'] ?? 0) }}</td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex align-items-center">
                                                        <input type="number" 
                                                            wire:model.lazy="stackedItems.{{ $index }}.item_qty" 
                                                            class="form-control rounded @error('stackedItems.'.$index.'.item_qty') is-invalid @enderror" 
                                                            min="1" 
                                                            @if($deliveryOrder && $deliveryOrder->status === 'Completed')
                                                                max="{{ $item['item']['qty'] }}"
                                                            @endif
                                                            wire:change="updatePriceLine({{ $index }})" 
                                                            {{ ($isView || ($deliveryOrder && ($deliveryOrder->status ?? '') === 'Completed')) ? 'disabled' : '' }}
                                                            style="width: 100%;">
                                                        
                                                        @error('stackedItems.'.$index.'.item_qty')
                                                            <div class="text-danger small ml-2">!</div>
                                                        @enderror
                                                    </div>
                                                    
                                                </div>
                                            </td>
                                            <td class="price-cell position-relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                                                <div class="w-100" wire:key="price-{{ $index }}">
                                                    @php
                                                        $price = $stackedItems[$index]['item_unit_price'] ?? 0;
                                                        $tier = $stackedItems[$index]['pricing_tier'] ?? '';
                                                    @endphp
                                                    <button type="button" class="btn btn-outline-secondary btn-sm w-100 d-flex justify-content-between align-items-center text-start small" @click="open = !open" :aria-expanded="open" {{ $isView ? 'disabled' : '' }}>
                                                        <span>
                                                            @if(($tier ?? '') === '')
                                                                Custom Price
                                                            @else
                                                                {{ number_format($price ?? 0, 2) }}
                                                            @endif
                                                        </span>
                                                        <span class="ms-2">▼</span>
                                                    </button>
                                                    <ul x-show="open" x-transition.origin.top.right @click.outside="open = false" class="dropdown-menu dropdown-menu-end w-100 p-1 small show" style="display: block; position: absolute; inset: auto 0 auto auto;">
                                                        <li>
                                                            <a class="dropdown-item py-1 d-flex justify-content-between align-items-center" href="#" @click.prevent="$wire.selectPricingTier({{ $index }}, ''); open = false" :class="{ 'active': '{{ $tier }}' === '' }">
                                                                <div class="d-flex flex-column">
                                                                    <span class="text-muted small">Custom Price</span>
                                                                    <span class="fw-semibold">{{ number_format((float)($stackedItems[$index]['item_unit_price'] ?? 0), 2) }}</span>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item py-1 d-flex justify-content-between align-items-center" href="#" @click.prevent="$wire.selectPricingTier({{ $index }}, 'Cash Price'); open = false" :class="{ 'active': '{{ $tier }}' === 'Cash Price' }">
                                                                <div class="d-flex flex-column">
                                                                    <span class="text-muted small">Cash Price</span>
                                                                    <span class="fw-semibold">{{ number_format((float)($item['item']['cash_price'] ?? 0), 2) }}</span>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item py-1 d-flex justify-content-between align-items-center" href="#" @click.prevent="$wire.selectPricingTier({{ $index }}, 'Term Price'); open = false" :class="{ 'active': '{{ $tier }}' === 'Term Price' }">
                                                                <div class="d-flex flex-column">
                                                                    <span class="text-muted small">Term Price</span>
                                                                    <span class="fw-semibold">{{ number_format((float)($item['item']['term_price'] ?? 0), 2) }}</span>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item py-1 d-flex justify-content-between align-items-center" href="#" @click.prevent="$wire.selectPricingTier({{ $index }}, 'Customer Price'); open = false" :class="{ 'active': '{{ $tier }}' === 'Customer Price' }">
                                                                <div class="d-flex flex-column">
                                                                    <span class="text-muted small">Customer Price</span>
                                                                    <span class="fw-semibold">{{ number_format((float)($item['item']['cust_price'] ?? 0), 2) }}</span>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item py-1 d-flex justify-content-between align-items-center" href="#" @click.prevent="$wire.selectPricingTier({{ $index }}, 'Cost'); open = false" :class="{ 'active': '{{ $tier }}' === 'Cost' }">
                                                                <div class="d-flex flex-column">
                                                                    <span class="text-muted small">Cost</span>
                                                                    <span class="fw-semibold">{{ number_format((float)($item['item']['cost'] ?? 0), 2) }}</span>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        @if($cust_id)
                                                        <li>
                                                            <a class="dropdown-item py-1 d-flex justify-content-between align-items-center" href="#" @click.prevent="$wire.selectPricingTier({{ $index }}, 'Previous Price'); open = false" :class="{ 'active': '{{ $tier }}' === 'Previous Price' }">
                                                                <div class="d-flex flex-column">
                                                                    <span class="text-muted small">Previous Price</span>
                                                                    <span class="fw-semibold">{{ number_format((float)(($item['item']['latest_do_price'] ?? 0) ?: 0), 2) }}</span>
                                                                    @php $prevDate = $item['item']['latest_do_date'] ?? null; @endphp
                                                                    @if($prevDate)
                                                                        <span class="text-muted small">{{ \Carbon\Carbon::parse($prevDate)->format('Y-m-d') }}</span>
                                                                    @endif
                                                                </div>
                                                            </a>
                                                        </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                                @if(($stackedItems[$index]['pricing_tier'] ?? '') === '')
                                                    <div class="mt-2">
                                                        <div class="input-group input-group-sm">
                                                            <input type="text" inputmode="decimal" placeholder="0.00" class="form-control form-control-sm"
                                                                x-data
                                                                x-init="$nextTick(() => { const n = parseFloat($el.value || 0); $el.value = isNaN(n) ? '' : n.toFixed(2); })"
                                                                x-on:blur="const n = parseFloat($el.value || 0); $el.value = isNaN(n) ? '' : n.toFixed(2)"
                                                                wire:model.lazy="stackedItems.{{ $index }}.item_unit_price"
                                                                wire:change="updateUnitPrice({{ $index }})" {{ $isView ? 'disabled' : '' }}>
                                                        </div>
                                                    </div>
                                                @endif
                                                @error('stackedItems.'.$index.'.pricing_tier')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                                @error('stackedItems.'.$index.'.item_unit_price')
                                                    <p class="text-danger">{{ $message }}</p>
                                                @enderror
                                            </td>
                                            <td>
                                                {{ number_format((float)($stackedItems[$index]['amount'] ?? 0), 2) ?? 0 }}
                                            </td>
                                            @if(!$isView)
                                            <td class="col-actions">
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                     wire:click="removeItem({{ $index }})" 
                                                     title="Delete" aria-label="Delete"
                                                           {{ $isView ? 'disabled' : ''}}>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                        <path d="M5.5 5.5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m5 0a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5M2.5 3a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h2.5a1 1 0 0 1 0 2H2.5a1 1 0 0 1 0-2M3.5 4l1 10.5A2 2 0 0 0 6.49 16h3.02a2 2 0 0 0 1.99-1.5L12.5 4z"/>
                                                    </svg>
                                                </button>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-end mb-3">
                                <div class="mb-3">
                                    <h6>Total Amount: 
                                        @if($deliveryOrder && $deliveryOrder->id)
                                            {{ $deliveryOrder->customerSnapshot->currency ?? $deliveryOrder->customer->currency ?? 'RM' }}
                                        @elseif($selectedCustomer)
                                            {{ $selectedCustomer->currency ?? 'RM' }}
                                        @else
                                            RM
                                        @endif
                                        {{ number_format((float)$total_amount, 2) }}
                                    </h6>
                                </div>
                            </div>
                            @if(!$isView)
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('delivery-orders') }}" class="btn btn-secondary">Back</a>
                                </div>
                                <div class="text-end">
                                    @if(!$deliveryOrder || $deliveryOrder->status !== 'Completed')
                                        <button type="submit" class="btn btn-success me-2" @if(empty($stackedItems)) disabled @endif>Post</button>
                                    @endif
                                    <button type="button" class="btn btn-secondary me-2" wire:click="saveDraft" @if(empty($stackedItems)) disabled @endif>
                                        @if($deliveryOrder && $deliveryOrder->status === 'Completed')
                                            Restore All
                                        @else
                                            Save Draft
                                        @endif
                                    </button>
                                    <button type="button" class="btn btn-info" wire:click="preview" {{ empty($stackedItems) ? 'disabled' : '' }}>
                                        Preview
                                    </button>
                                </div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        sessionStorage.setItem('returnToDOList', document.referrer);
                                    });
                                </script>
                            </div>
                            @endif
                            @if($isView && $deliveryOrder)
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <a href="{{ route('delivery-orders') }}" class="btn btn-secondary">Back</a>
                                </div>
                                <div class="text-end">
                                    <a href="{{ route('delivery-orders.edit', $deliveryOrder->id) }}" class="btn btn-primary me-2">Edit</a>
                                    <a href="{{ route('print.delivery-order.preview', $deliveryOrder->id) }}" class="btn btn-info">Preview</a>
                                </div>
                            </div>
                            @endif
                        </form>
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
        
        /* Fixed table layout and responsive column widths */
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
        .table th:nth-child(6), .table td:nth-child(6) { width: 12%; } /* Unit Price */
        .table th:nth-child(7), .table td:nth-child(7) { width: 10%; } /* Amount */
        .table th:nth-child(8), .table td:nth-child(8) { width: 5%; } /* Actions */

        /* Input fields in table */
        .table input[type="text"],
        .table input[type="number"] {
            width: 100%;
            padding: 0.25rem;
            font-size: 0.9em;
        }

        /* Price cell specific styles */
        .table td.price-cell { 
            overflow: visible; 
            padding: 0.5rem;
        }
        .price-cell .dropdown-menu { 
            z-index: 2000;
            max-width: 100%;
        }
        /* Ensure keyboard-highlighted list items remain readable when hovered */
        .list-group .active { background-color: #0d6efd; color: #fff; }
        .list-group .active:hover { background-color: #0b5ed7; color: #fff; }
        
        /* Actions column */
        .col-actions { 
            width: 5%; 
            text-align: center;
            white-space: nowrap;
        }
    </style>
</div>