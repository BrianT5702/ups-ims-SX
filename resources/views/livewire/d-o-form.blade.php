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

                            <div class="do-items-table mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Delivery Order Items (Max 24 rows)</h6>
                                    @php
                                        // Calculate current row count
                                        $currentRowCount = 0;
                                        foreach ($stackedItems as $item) {
                                            $currentRowCount += 1; // Base row for each item
                                            
                                            // Count description rows
                                            $desc = $item['more_description'] ?? '';
                                            if (!empty($desc)) {
                                                $lines = explode("\n", $desc);
                                                foreach ($lines as $line) {
                                                    $lineLength = strlen($line);
                                                    $wrappedLines = max(1, ceil($lineLength / 60));
                                                    $currentRowCount += $wrappedLines;
                                                }
                                            }
                                            
                                            // Count item details rows
                                            $details = $item['item']['details'] ?? '';
                                            if (!empty($details)) {
                                                $detailLines = explode("\n", $details);
                                                foreach ($detailLines as $line) {
                                                    $line = trim($line);
                                                    if ($line === '') continue;
                                                    $lineLength = strlen($line);
                                                    $wrappedLines = max(1, ceil($lineLength / 60));
                                                    $currentRowCount += $wrappedLines;
                                                }
                                            }
                                        }
                                        $remainingRows = 24 - $currentRowCount;
                                    @endphp
                                    <small class="text-muted">
                                        Used: <strong>{{ $currentRowCount }}</strong> / 24 rows | 
                                        Remaining: <strong>{{ $remainingRows }}</strong> rows
                                    </small>
                                </div>
                                @error('stackedItems')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                                <table class="table table-bordered do-fixed-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 100px;">QTY</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            // Calculate total rows used by items (including descriptions)
                                            $totalUsedRows = 0;
                                            foreach ($stackedItems as $item) {
                                                $totalUsedRows += 1; // Base row for each item
                                                
                                                // Count description rows (each line = 1 row)
                                                $desc = $item['more_description'] ?? '';
                                                if (!empty($desc)) {
                                                    $lines = explode("\n", $desc);
                                                    foreach ($lines as $line) {
                                                        $lineLength = strlen($line);
                                                        $wrappedLines = max(1, ceil($lineLength / 60));
                                                        $totalUsedRows += $wrappedLines;
                                                    }
                                                }
                                                
                                                // Count item details rows
                                                $details = $item['item']['details'] ?? '';
                                                if (!empty($details)) {
                                                    $detailLines = explode("\n", $details);
                                                    foreach ($detailLines as $line) {
                                                        $line = trim($line);
                                                        if ($line === '') continue;
                                                        $lineLength = strlen($line);
                                                        $wrappedLines = max(1, ceil($lineLength / 60));
                                                        $totalUsedRows += $wrappedLines;
                                                    }
                                                }
                                            }
                                            
                                            // Calculate how many rows we should actually show
                                            // totalUsedRows includes items + descriptions (calculated rows)
                                            // We want to show exactly 24 rows, but limit empty rows based on calculation
                                            $itemRows = count($stackedItems);
                                            
                                            // Calculate available empty rows: 24 - totalUsedRows
                                            // This ensures users can't add beyond the limit
                                            $emptyRowsAvailable = max(0, 24 - $totalUsedRows);
                                            
                                            // Total rows to show: items + available empty rows (capped at 24)
                                            $rowsToShow = min(24, $itemRows + $emptyRowsAvailable);
                                        @endphp
                                        @for($rowIndex = 0; $rowIndex < $rowsToShow; $rowIndex++)
                                            @php
                                                // Map row index to item index (items take 1 visual row each)
                                                $itemIndex = null;
                                                if ($rowIndex < $itemRows) {
                                                    $itemIndex = $rowIndex;
                                                }
                                                $item = $itemIndex !== null ? $stackedItems[$itemIndex] : null;
                                                $isEmptyRow = ($itemIndex === null);
                                            @endphp
                                            <tr class="item-row">
                                                <td style="width: 100px; vertical-align: top;">
                                                    @if($item)
                                                        @if((isset($item['is_text_only']) && $item['is_text_only']) || ($item['item']['id'] ?? null) === null)
                                                            {{-- Text-only item: empty qty column --}}
                                                            <div style="padding: 4px;">
                                                                &nbsp;
                                                            </div>
                                                        @else
                                                            <div class="d-flex flex-column gap-1">
                                                                <input type="number" 
                                                                    wire:model.lazy="stackedItems.{{ $itemIndex }}.item_qty" 
                                                                    class="form-control form-control-sm @error('stackedItems.'.$itemIndex.'.item_qty') is-invalid @enderror" 
                                                                    min="1" 
                                                                    wire:change="updatePriceLine({{ $itemIndex }})" 
                                                                    {{ ($isView || ($deliveryOrder && ($deliveryOrder->status ?? '') === 'Completed')) ? 'disabled' : '' }}
                                                                    style="width: 100%;">
                                                                <small class="text-muted" style="font-size: 0.75em;">
                                                                    {{ $item['item']['um'] ?? 'UNIT' }}
                                                                </small>
                                                                @error('stackedItems.'.$itemIndex.'.item_qty')
                                                                    <div class="text-danger small">!</div>
                                                                @enderror
                                                            </div>
                                                        @endif
                                                    @elseif(!$isView)
                                                        <input type="text" 
                                                            class="form-control form-control-sm" 
                                                            placeholder="Qty"
                                                            disabled
                                                            style="width: 100%; background-color: #f8f9fa;">
                                                    @endif
                                                </td>
                                                <td style="vertical-align: top; position: relative;">
                                                    @if($item)
                                                        @if(isset($item['is_text_only']) && $item['is_text_only'])
                                                            {{-- Text-only item: just show the text --}}
                                                            <div class="d-flex align-items-center">
                                                                <span>{{ $item['custom_item_name'] ?? '' }}</span>
                                                                @if(!$isView)
                                                                    <button type="button" 
                                                                        class="btn btn-sm p-0 px-1 btn-danger flex-shrink-0 ms-2"
                                                                        wire:click="removeItem({{ $itemIndex }})"
                                                                        title="Delete"
                                                                        style="font-size: 0.7rem;">
                                                                        ×
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        @else
                                                        <div x-data="{ 
                                                                showDescription: {{ !empty($stackedItems[$itemIndex]['more_description']) ? 'true' : 'false' }},
                                                    showMemo: false,
                                                                hoverTimeout: null,
                                                                editingName: false
                                                }" 
                                                x-init="
                                                    $watch('showDescription', value => {
                                                                    if (value) {
                                                                        $wire.call('validateDescriptionRowsOnShow', {{ $itemIndex }});
                                                        }
                                                    })
                                                ">
                                                            <div class="d-flex gap-2 align-items-start" style="position: relative;">
                                                                <div style="flex: 1;">
                                                                    <template x-if="!editingName">
                                                                        <div>
                                                                            <span wire:key="item-name-{{ $itemIndex }}-{{ $stackedItems[$itemIndex]['custom_item_name'] ?? 'default' }}">
                                                                                {{ $stackedItems[$itemIndex]['custom_item_name'] ?? $item['item']['item_name'] }}
                                                                            </span>
                                                        @if(!empty($item['item']['memo']))
                                                            <div x-show="showMemo" 
                                                                 x-transition
                                                                 @mouseenter="clearTimeout(hoverTimeout); showMemo = true"
                                                                 @mouseleave="showMemo = false"
                                                                                     style="position: absolute; background: #fff; border: 1px solid #ccc; padding: 6px 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); z-index: 1000; margin-top: 2px; width: auto; max-width: 200px; max-height: 150px; overflow-y: auto; font-size: 0.8em; white-space: pre-wrap; left: 0; top: 100%; word-wrap: break-word; text-align: left; line-height: 1.4;">
                                                                <strong style="font-size: 0.85em; display: block; margin-bottom: 3px;">Memo:</strong>
                                                                <div style="font-size: 0.8em; text-align: left; white-space: pre-wrap; word-wrap: break-word; line-height: 1.4;">{{ $item['item']['memo'] }}</div>
                                                            </div>
                                                        @endif
                                                                        </div>
                                                                    </template>
                                                                    <template x-if="editingName">
                                                                        <div class="d-flex gap-1 align-items-center">
                                                                            <input type="text" 
                                                                                x-ref="nameInput"
                                                                                class="form-control form-control-sm" 
                                                                                wire:model="stackedItems.{{ $itemIndex }}.custom_item_name"
                                                                                placeholder="{{ $item['item']['item_name'] }}"
                                                                                @keydown.enter.prevent="editingName = false"
                                                                                @keydown.escape="editingName = false"
                                                                                style="font-size: 0.85em;">
                                                                            <button type="button" 
                                                                                class="btn btn-sm btn-success p-1 px-2"
                                                                                @click="editingName = false"
                                                                                style="font-size: 0.7rem; line-height: 1;">
                                                                                ✓
                                                                            </button>
                                                                            <button type="button" 
                                                                                class="btn btn-sm btn-outline-secondary p-1 px-2"
                                                                                @click="$wire.set('stackedItems.{{ $itemIndex }}.custom_item_name', null); editingName = false"
                                                                                style="font-size: 0.7rem; line-height: 1;"
                                                                                title="Reset to original">
                                                                                ↺
                                                                            </button>
                                                                        </div>
                                                                    </template>
                                                    </div>
                                                    @if(!$isView)
                                                        <button type="button" 
                                                                        x-show="!editingName"
                                                            class="btn btn-sm p-0 px-1 flex-shrink-0" 
                                                            :class="showDescription ? 'btn-primary' : 'btn-outline-primary'"
                                                            @click="showDescription = !showDescription"
                                                            style="font-size: 0.7rem;">
                                                            <span x-text="showDescription ? '- desc' : '+ desc'"></span>
                                                        </button>
                                                        <button type="button" 
                                                                        x-show="!editingName"
                                                            class="btn btn-sm p-0 px-1 btn-outline-secondary flex-shrink-0"
                                                                        @click="editingName = true; $nextTick(() => $refs.nameInput?.focus())"
                                                                        style="font-size: 0.7rem;">
                                                                        Edit
                                                                    </button>
                                                                    <button type="button" 
                                                                        class="btn btn-sm p-0 px-1 btn-danger flex-shrink-0"
                                                                        wire:click="removeItem({{ $itemIndex }})"
                                                                        title="Delete"
                                                            style="font-size: 0.7rem;">
                                                                        ×
                                                        </button>
                                                    @endif
                                                </div>
                                                @if(!empty($item['item']['details']))
                                                                <div class="mt-1 ms-0 text-muted" style="font-size: 0.85em;">
                                                        @foreach(explode("\n", $item['item']['details']) as $line)
                                                            @if(trim($line) !== '')
                                                                <div>• {{ $line }}</div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                                            @if($isView && !empty($stackedItems[$itemIndex]['more_description']))
                                                                <div class="mt-1 ms-0 text-muted" style="font-size: 0.85em;">
                                                                    @foreach(explode("\n", $stackedItems[$itemIndex]['more_description']) as $line)
                                                            @if(trim($line) !== '')
                                                                <div>• {{ $line }}</div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if(!$isView)
                                                                <div x-show="showDescription" class="mt-2 mb-3 p-2" style="background-color: #f8f9fa; border-radius: 4px; border: 1px solid #dee2e6;">
                                                        <textarea 
                                                                        wire:model="stackedItems.{{ $itemIndex }}.more_description"
                                                            class="form-control form-control-sm"
                                                            rows="3"
                                                                        placeholder="Enter additional description (each line = 1 row)"
                                                                        style="font-size: 0.85em; resize: vertical;"></textarea>
                                                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                                                        <small class="text-muted" style="font-size: 0.75em;">
                                                                            Each line counts as 1 row. Max 24 rows total.
                                                                        </small>
                                                                        <button type="button"
                                                                            wire:click="saveDescriptionAndValidate({{ $itemIndex }})"
                                                                            class="btn btn-sm btn-primary"
                                                                            style="font-size: 0.75em; padding: 4px 12px;">
                                                                            Save
                                                                        </button>
                                                                    </div>
                                                    </div>
                                                                <div class="mt-2 d-flex justify-content-between align-items-center gap-3" style="font-size: 0.85em;">
                                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                                        <span class="text-muted fw-medium" style="white-space: nowrap;">Price:</span>
                                                                        @php
                                                                            $price = $stackedItems[$itemIndex]['item_unit_price'] ?? 0;
                                                                            $tier = $stackedItems[$itemIndex]['pricing_tier'] ?? '';
                                                                        @endphp
                                                                        <select wire:model.live="stackedItems.{{ $itemIndex }}.pricing_tier" 
                                                                                wire:change="selectPricingTier({{ $itemIndex }}, $event.target.value)"
                                                                                class="form-select form-select-sm" 
                                                                                style="width: 180px; font-size: 0.85em; flex-shrink: 0;">
                                                                            <option value="">Custom</option>
                                                                            <option value="Cash Price">Cash: {{ number_format($item['item']['cash_price'] ?? 0, 2) }}</option>
                                                                            <option value="Term Price">Term: {{ number_format($item['item']['term_price'] ?? 0, 2) }}</option>
                                                                            <option value="Customer Price">Customer: {{ number_format($item['item']['cust_price'] ?? 0, 2) }}</option>
                                                                            <option value="Cost">Cost: {{ number_format($item['item']['cost'] ?? 0, 2) }}</option>
                                                                            @if($cust_id && ($item['item']['latest_do_price'] ?? 0) > 0)
                                                                                <option value="Previous Price">Previous: {{ number_format($item['item']['latest_do_price'], 2) }}</option>
                                                                            @endif
                                                                        </select>
                                                                        @if(($tier ?? '') === '')
                                                            <input type="text" 
                                                                                inputmode="decimal"
                                                                                wire:model.lazy="stackedItems.{{ $itemIndex }}.item_unit_price"
                                                                                wire:change="updateUnitPrice({{ $itemIndex }})"
                                                                class="form-control form-control-sm" 
                                                                                placeholder="0.00"
                                                                                style="width: 110px; font-size: 0.85em; text-align: right; flex-shrink: 0;">
                                                                        @else
                                                                            <span class="fw-bold form-control form-control-sm d-inline-block" style="width: 110px; font-size: 0.85em; text-align: right; background-color: #f8f9fa; border: 1px solid #ced4da; border-radius: 0.25rem; padding: 0.25rem 0.5rem; line-height: 1.5; flex-shrink: 0;">{{ number_format($price, 2) }}</span>
                                                                        @endif
                                                            </div>
                                                                    <div class="d-flex align-items-center gap-2" style="flex-shrink: 0;">
                                                                        <span class="text-muted fw-medium" style="white-space: nowrap;">Amount:</span>
                                                                        <span class="fw-bold" style="font-size: 0.95em; min-width: 90px; text-align: right; color: #0d6efd; white-space: nowrap;">{{ number_format($stackedItems[$itemIndex]['amount'] ?? 0, 2) }}</span>
                                                        </div>
                                                    </div>
                                                    @endif
                                                @endif
                                                    @elseif(!$isView && $isEmptyRow)
                                                        {{-- Show empty row input (rows are already limited by rowsToShow calculation above) --}}
                                                            <div x-data="{ 
                                                                    showSearch: false,
                                                                    searchTerm: '',
                                                                    highlightIndex: -1,
                                                                    showResults: false
                                                                }" 
                                                                class="d-flex gap-2 align-items-center" 
                                                                style="position: relative; width: 100%;">
                                                                <input type="text" 
                                                                    x-show="!showSearch"
                                                                    wire:model.lazy="freeFormTextRows.{{ $rowIndex }}"
                                                                    class="form-control form-control-sm flex-grow-1" 
                                                                    placeholder="Type anything here (remarks, notes, etc.)"
                                                                    style="font-size: 0.85em;">
                                                                <div x-show="showSearch" class="position-relative flex-grow-1">
                                                                    <input type="text" 
                                                                        x-ref="searchInput"
                                                                        x-model="searchTerm"
                                                                        class="form-control form-control-sm" 
                                                                        placeholder="Search item code or name..."
                                                                        autocomplete="off"
                                                                        @keydown.escape="showSearch = false; searchTerm = ''; showResults = false"
                                                                        @keydown.arrow-down.prevent="highlightIndex = Math.min(highlightIndex + 1, $wire.itemSearchResults.length - 1)"
                                                                        @keydown.arrow-up.prevent="highlightIndex = Math.max(highlightIndex - 1, -1)"
                                                                        @keydown.enter.prevent="if(highlightIndex >= 0 && $wire.itemSearchResults[highlightIndex]) { $wire.call('addItemToRow', $wire.itemSearchResults[highlightIndex].id, {{ $rowIndex }}); searchTerm = ''; showResults = false; showSearch = false; }"
                                                                        @input="$wire.set('itemSearchTerm', searchTerm); $wire.call('searchItems'); if(searchTerm.length > 0) showResults = true"
                                                                        @focus="if($wire.itemSearchResults.length > 0) showResults = true"
                                                                        @blur="setTimeout(() => showResults = false, 200)"
                                                                        style="font-size: 0.85em; width: 100%;">
                                                                    @if(count($itemSearchResults) > 0)
                                                                        <ul x-show="showResults && searchTerm.length > 0" 
                                                                            class="list-group position-absolute w-100" 
                                                                            style="z-index: 1000; max-height: 200px; overflow-y: auto; margin-top: 2px;"
                                                                            x-cloak>
                                                                            @foreach($itemSearchResults as $idx => $result)
                                                                                <li class="list-group-item list-group-item-action" 
                                                                                    :class="{ 'active': highlightIndex === {{ $idx }} }"
                                                                                    wire:click="addItemToRow({{ $result->id }}, {{ $rowIndex }})"
                                                                                    @click="showSearch = false; searchTerm = ''"
                                                                                    style="cursor: pointer; font-size: 0.85em;">
                                                                                    <span>{{ $result->item_code }} - {{ $result->item_name }}</span>
                                                                                    @if($result->qty > 0)
                                                                                        <span class="badge bg-success ms-2">Qty: {{ $result->qty }}</span>
                                                            @else
                                                                                        <span class="badge bg-warning ms-2">Out of Stock</span>
                                                            @endif
                                                        </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @endif
                                                                </div>
                                                                <button type="button" 
                                                                    x-show="!showSearch"
                                                                    @click="showSearch = true; $nextTick(() => { $refs.searchInput?.focus(); })"
                                                                    class="btn btn-sm btn-outline-primary"
                                                                    style="font-size: 0.7em; padding: 2px 6px; white-space: nowrap; flex-shrink: 0;">
                                                                    + Add Item
                                                                </button>
                                                                <button type="button" 
                                                                    x-show="showSearch"
                                                                    @click="showSearch = false; searchTerm = ''; showResults = false"
                                                                    class="btn btn-sm btn-outline-secondary"
                                                                    style="font-size: 0.7em; padding: 2px 6px; white-space: nowrap; flex-shrink: 0;">
                                                                    Cancel
                                                                </button>
                                                    </div>
                                                @endif
                                            </td>
                                            </tr>
                                        @endfor
                                        @if($totalUsedRows >= 24)
                                            <tr>
                                                <td colspan="2" class="text-center text-danger" style="padding: 8px; font-size: 0.85em;">
                                                    ⚠️ Row limit reached ({{ $totalUsedRows }}/24 rows used). Please remove items or shorten descriptions to add more.
                                            </td>
                                            </tr>
                                            @endif
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
                                    @php
                                        // Check if there are items or free-form text
                                        $hasItems = !empty($stackedItems) && is_array($stackedItems) && count($stackedItems) > 0;
                                        $hasFreeFormText = false;
                                        if (!empty($freeFormTextRows) && is_array($freeFormTextRows)) {
                                            $filtered = array_filter($freeFormTextRows, function($text) {
                                                return !empty(trim($text ?? ''));
                                            });
                                            $hasFreeFormText = count($filtered) > 0;
                                        }
                                        $hasContent = $hasItems || $hasFreeFormText;
                                    @endphp
                                    @if(!$deliveryOrder || $deliveryOrder->status !== 'Completed')
                                        <button type="submit" class="btn btn-success me-2" @if(!$hasContent) disabled @endif>Post</button>
                                    @endif
                                    <button type="button" class="btn btn-secondary me-2" wire:click="saveDraft" @if(!$hasContent) disabled @endif>
                                        @if($deliveryOrder && $deliveryOrder->status === 'Completed')
                                            Restore All
                                        @else
                                            Save Draft
                                        @endif
                                    </button>
                                    <button type="button" class="btn btn-info" wire:click="preview" @if(!$hasContent) disabled @endif>
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
        
        /* Fixed 24-row table layout */
        .do-fixed-table { 
            table-layout: fixed;
            width: 100%;
        }
        
        .do-fixed-table th, .do-fixed-table td {
            padding: 4px 8px;
            vertical-align: top;
            word-wrap: break-word;
        }
        
        .do-fixed-table th {
            font-size: 0.85em;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
        }
        
        .do-fixed-table tbody tr {
            min-height: 30px;
        }
        
        .do-fixed-table .remark-row {
            background-color: #f8f9fa;
        }
        
        .do-fixed-table .item-row:hover {
            background-color: #f0f0f0;
        }

        /* Input fields in table */
        .do-fixed-table input[type="text"],
        .do-fixed-table input[type="number"],
        .do-fixed-table textarea {
            width: 100%;
            padding: 0.25rem;
            font-size: 0.85em;
            border: 1px solid #ddd;
        }
        
        /* Search dropdown */
        .do-fixed-table .list-group {
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        
        .do-fixed-table .list-group-item {
            padding: 6px 10px;
            font-size: 0.85em;
            cursor: pointer;
        }
        
        .do-fixed-table .list-group-item:hover,
        .do-fixed-table .list-group-item.active {
            background-color: #0d6efd;
            color: #fff;
        }
        
        [x-cloak] { display: none !important; }
    </style>
    
</div>