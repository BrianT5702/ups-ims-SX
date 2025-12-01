<div class="container-fluid my-3">
    <div class="row">
        <div class="col-md-11 m-auto">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold fs-5">{{ $isView ? 'View' : ($quotation ? 'Edit' : 'Add') }} Quotation</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="addQuotation">
                        <div class="row mb-3">
                            <div class="col-md-4" x-data="{ hi: 0 }">
                                @if(!$quotation || !$isView)
                                    <label for="customer">Customer <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.debounce.100ms="customerSearchTerm" wire:input.debounce.200ms="searchCustomers" id="searchCustomer" class="form-control rounded" placeholder="Search Customer" {{ $isView ? 'disabled' : '' }} autocomplete="off" x-on:input="hi = 0"
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
                                                    <li class="list-group-item d-flex justify-content-between align-items-center" wire:click="selectCustomer({{ $custResult->id }})" :class="{ 'active': hi === {{ $idx }} }" style="cursor: pointer;">
                                                        <span>{{ $custResult->account }} - {{ $custResult->cust_name }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                @endif

                                @if($isView || ($quotation && $quotation->customer))
                                    <div>
                                        <p class="fw-bold mb-2">{{ $quotation->customerSnapshot->cust_name ?? $quotation->customer->cust_name }}</p>
                                        <p class="mb-1"><strong>Currency:</strong> {{ $quotation->customerSnapshot->currency ?? $quotation->customer->currency ?? 'RM' }}</p>
                                        <p class="mb-1">{{ $quotation->customerSnapshot->address_line1 ?? $quotation->customer->address_line1 }}</p>
                                        <p class="mb-1">{{ $quotation->customerSnapshot->address_line2 ?? $quotation->customer->address_line2 }}</p>
                                        @if($quotation->customerSnapshot->address_line3 ?? $quotation->customer->address_line3)
                                            <p class="mb-1">{{ $quotation->customerSnapshot->address_line3 ?? $quotation->customer->address_line3 }}</p>
                                        @endif
                                        @if($quotation->customerSnapshot->address_line4 ?? $quotation->customer->address_line4)
                                            <p class="mb-1">{{ $quotation->customerSnapshot->address_line4 ?? $quotation->customer->address_line4 }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="date">Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="date" id="date" class="form-control rounded" placeholder="dd/mm/yyyy" {{ $isView ? 'disabled' : '' }}>
                                @error('date') <p class="text-danger">{{ $message }}</p> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="quotation_num">Quotation Number <span class="text-danger">*</span></label>
                                <input type="text" wire:model="quotation_num" id="quotation_num" class="form-control rounded" {{ $isView ? 'disabled' : '' }} placeholder="Enter Quotation Number">
                                @error('quotation_num') <p class="text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="ref_num">Reference Number</label>
                                <input type="text" wire:model="ref_num" id="ref_num" class="form-control rounded" {{ $isView ? 'disabled' : '' }} placeholder="Enter Reference Number">
                                @error('ref_num') <p class="text-danger">{{ $message }}</p> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="remark">Remark</label>
                                <textarea wire:model="remark" id="remark" class="form-control rounded" rows="3" {{ $isView ? 'disabled' : '' }} placeholder="Enter Remark"></textarea>
                                @error('remark') <p class="text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            @if(!$isView)
                            <div class="col-md-6" x-data="{ hi: 0 }">
                                <label for="search">Search Items</label>
                                <input type="text" wire:model.debounce.100ms="itemSearchTerm" wire:input.debounce.200ms="searchItems" id="searchItem" class="form-control rounded" placeholder="Search by Item Code or Name" {{ $isView ? 'disabled' : ''}} autocomplete="off" x-on:input="hi = 0"
                                        x-on:keydown.arrow-down.prevent="(() => { const list = $refs.itemList; const items = list ? list.querySelectorAll('li') : []; if(items.length===0) return; hi = Math.min(hi + 1, items.length - 1); $nextTick(() => { const el = items[hi]; if(!el) return; const elTop = el.offsetTop; const elBottom = elTop + el.offsetHeight; const viewTop = list.scrollTop; const viewBottom = viewTop + list.clientHeight; if (elTop < viewTop) { list.scrollTop = elTop; } else if (elBottom > viewBottom) { list.scrollTop = elBottom - list.clientHeight; } }); })()"
                                        x-on:keydown.arrow-up.prevent="(() => { const list = $refs.itemList; const items = list ? list.querySelectorAll('li') : []; if(items.length===0) return; hi = Math.max(hi - 1, 0); $nextTick(() => { const el = items[hi]; if(!el) return; const elTop = el.offsetTop; const elBottom = elTop + el.offsetHeight; const viewTop = list.scrollTop; const viewBottom = viewTop + list.clientHeight; if (elTop < viewTop) { list.scrollTop = elTop; } else if (elBottom > viewBottom) { list.scrollTop = elBottom - list.clientHeight; } }); })()"
                                        x-on:keydown.enter.prevent="(() => { const list = $refs.itemList; const items = list ? list.querySelectorAll('li') : []; const el = items && items[hi]; if(el) el.click(); })()">
                                @if(count($itemSearchResults) > 0)
                                    <div class="search-results mt-2">
                                        <ul class="list-group" x-ref="itemList">
                                            @foreach($itemSearchResults as $idx => $result)
                                                <li class="list-group-item d-flex justify-content-between align-items-center" wire:click="addItem({{ $result->id }})" :class="{ 'active': hi === {{ $idx }} }" style="cursor: pointer;">
                                                    <span>{{ $result->item_code }} - {{ $result->item_name }} <span class="ms-2 badge bg-warning text-dark">Qty: {{ $result->qty }}</span></span>
                                                </li>
                                            @endforeach
                                        </ul>
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
                            <h6>Selected Items for Quotation:</h6>
                            @error('stackedItems')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                            <style>
                                .table.quotation-items { table-layout: fixed; }
                                .table.quotation-items th, .table.quotation-items td { vertical-align: top; }
                                .table.quotation-items th:nth-child(1), .table.quotation-items td:nth-child(1) { width: 4%; white-space: nowrap; }
                                .table.quotation-items th:nth-child(2), .table.quotation-items td:nth-child(2) { 
                                    width: 12%; 
                                    word-wrap: break-word;
                                    overflow-wrap: break-word;
                                }
                                .table.quotation-items th:nth-child(3), .table.quotation-items td:nth-child(3) { width: 36%; }
                                .table.quotation-items th:nth-child(4), .table.quotation-items td:nth-child(4) { width: 10%; white-space: nowrap; }
                                .table.quotation-items th:nth-child(5), .table.quotation-items td:nth-child(5) { width: 12%; white-space: nowrap; }
                                .table.quotation-items th:nth-child(6), .table.quotation-items td:nth-child(6) { width: 16%; }
                                .table.quotation-items th:nth-child(7), .table.quotation-items td:nth-child(7) { width: 10%; white-space: nowrap; }
                                .table.quotation-items th:nth-child(8), .table.quotation-items td:nth-child(8) { width: 8%; white-space: nowrap; }
                            </style>
                            <table class="table table-bordered quotation-items">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th>Qty on Hand</th>
                                        <th>Order Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Amount</th>
                                        @if(!$isView)
                                            <th class="col-actions">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stackedItems as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td title="{{ $item['item']['item_code'] }}">{{ $item['item']['item_code'] }}</td>
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
                                        </td>
                                        <td>{{ ($item['item']['qty'] ?? 0) }}</td>
                                        <td>
                                            <input type="number" 
                                                wire:model.lazy="stackedItems.{{ $index }}.item_qty" 
                                                class="form-control rounded @error('stackedItems.'.$index.'.item_qty') is-invalid @enderror" 
                                                min="1" 
                                                wire:change="updatePriceLine({{ $index }})" 
                                                {{ $isView ? 'disabled' : '' }}
                                                style="width: 100%;">
                                            @error('stackedItems.'.$index.'.item_qty')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
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
                                                                <span class="fw-semibold">{{ number_format((float)(($item['item']['latest_quote_price'] ?? 0) ?: 0), 2) }}</span>
                                                                @php $prevDate = $item['item']['latest_quote_date'] ?? null; @endphp
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
                                                        <input type="text" inputmode="decimal" placeholder="0.00" class="form-control form-control-sm rounded"
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
                                            <input type="text" class="form-control rounded" value="{{ number_format((float)($item['amount'] ?? 0), 2) }}" disabled>
                                        </td>
                                        @if(!$isView)
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        wire:click="removeItem({{ $index }})" 
                                                        title="Delete" aria-label="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                        <path d="M5.5 5.5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m5 0a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5M2.5 3a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h2.5a1 1 0 0 1 0 2H2.5a1 1 0 0 1 0-2M3.5 4l1 10.5A2 2 0 0 0 6.49 16h3.02a2 2 0 0 0 1.99-1.5L12.5 4z"/>
                                                    </svg>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No items added</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>


                        <div class="text-end mb-3">
                            <div class="row justify-content-end">
                                <div class="col-md-4">
                                    <div class="mb-2 d-flex justify-content-between fw-bold">
                                        <span>Total</span>
                                        <span>{{ $quotation ? ($quotation->customerSnapshot->currency ?? $quotation->customer->currency ?? 'RM') : ($cust_id ? \App\Models\Customer::find($cust_id)->currency ?? 'RM' : 'RM') }} {{ number_format((float)$total_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(!$isView)
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('quotations') }}" class="btn btn-secondary">Back</a>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-success me-2" wire:click="markSent" @if(empty($stackedItems)) disabled @endif>Sent</button>
                                <button type="button" class="btn btn-secondary me-2" wire:click="saveDraft" @if(empty($stackedItems)) disabled @endif>
                                    Save to Draft
                                </button>
                                <button type="button" class="btn btn-info" wire:click="preview" {{ empty($stackedItems) ? 'disabled' : '' }}>
                                    Preview
                                </button>
                            </div>
                        </div>
                        @endif

                        @if($isView && $quotation)
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <a href="{{ route('quotations') }}" class="btn btn-secondary">Back</a>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('quotations.edit', $quotation->id) }}" class="btn btn-primary me-2">Edit</a>
                                <a href="{{ route('print.quotation.preview', $quotation->id) }}" class="btn btn-info">Preview</a>
                            </div>
                        </div>
                        @endif
                    </form>
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


