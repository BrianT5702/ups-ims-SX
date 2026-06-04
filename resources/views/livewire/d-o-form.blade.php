<div>
    <script>
        (function () {
            function saveDoFormReturnUrl() {
                var path = window.location.pathname || '';
                var normalized = (path.replace(/\/+$/, '') || '/');
                if (normalized === '/delivery-orders') return;
                if (path.indexOf('/delivery-orders') === -1) return;
                sessionStorage.setItem('returnToDOList', window.location.href);
            }
            saveDoFormReturnUrl();
            document.addEventListener('DOMContentLoaded', saveDoFormReturnUrl);
        })();
    </script>
    <div class="container-fluid my-3 px-2 px-md-3">
        <div class="do-form-page">
            <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold fs-5">Delivery Order</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="addDO">
                        @php
                            $activeDb = strtolower(session('active_db') ?: config('database.default'));
                            $showInvoiceNoField = in_array($activeDb, ['ups', 'ucs'], true);
                        @endphp
                        <div class="do-header-fields">
                        {{-- One row: left = customer + currency/address + created by | middle = date, salesperson, cust PO | right = DO, ref, invoice --}}
                        <div class="row mb-3 align-items-start g-3 do-header-three-col">
                            {{-- LEFT --}}
                            <div class="col-xl-4 col-lg-12 d-flex flex-column" id="field-cust_id" x-data="{ hi: 0 }">
                                @if(!$deliveryOrder || !$isView)
                                    <label for="customer">Customer <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.debounce.300ms.live="customerSearchTerm"
                                        id="searchCustomer"
                                        class="form-control rounded"
                                        placeholder="Search customer"
                                        {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                        autocomplete="off"
                                        x-on:input="hi = 0"
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
                                    <div class="do-customer-detail mt-2">
                                        @if($isView)
                                            <p class="fw-bold mb-1 do-customer-detail-title">{{ $deliveryOrder->customerSnapshot->cust_name ?? $deliveryOrder->customer->cust_name }}</p>
                                        @endif
                                        <p class="mb-0"><span class="text-muted">Currency:</span> {{ $deliveryOrder->customerSnapshot->currency ?? $deliveryOrder->customer->currency ?? 'RM' }}</p>
                                        <p class="mb-0">{{ $deliveryOrder->customerSnapshot->address_line1 ?? $deliveryOrder->customer->address_line1 }}</p>
                                        <p class="mb-0">{{ $deliveryOrder->customerSnapshot->address_line2 ?? $deliveryOrder->customer->address_line2 }}</p>
                                        @if($deliveryOrder->customerSnapshot->address_line3 ?? $deliveryOrder->customer->address_line3)
                                            <p class="mb-0">{{ $deliveryOrder->customerSnapshot->address_line3 ?? $deliveryOrder->customer->address_line3 }}</p>
                                        @endif
                                        @if($deliveryOrder->customerSnapshot->address_line4 ?? $deliveryOrder->customer->address_line4)
                                            <p class="mb-0">{{ $deliveryOrder->customerSnapshot->address_line4 ?? $deliveryOrder->customer->address_line4 }}</p>
                                        @endif
                                    </div>
                                @endif

                                <div class="do-created-by mt-3 pt-2 border-top do-created-by-sep">
                                    <label for="created_by">Created By</label>
                                    <p class="mb-0"><b>{{ Auth::user()->name }}</b></p>
                                </div>
                            </div>

                            {{-- MIDDLE --}}
                            <div class="col-xl-4 col-lg-6 do-header-stack">
                                <div id="field-date">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="date" id="date" class="form-control rounded" 
                                        placeholder="dd/mm/yyyy"
                                        {{ ($isView || $this->isPosted) ? 'disabled' : '' }}>
                                    @error('date') <p class="text-danger">{{ $message }}</p> @enderror
                                </div>
                                <div class="mt-2" id="field-salesman_id">
                                    <label for="salesman">Salesperson <span class="text-danger">*</span></label>
                                    <select id="salesman" class="form-select rounded" wire:model.live="salesman_id" {{ ($isView || $this->isPosted || empty($cust_id)) ? 'disabled' : '' }}>
                                        <option value="">{{ empty($cust_id) ? 'Select a customer first' : 'Select Salesperson' }}</option>
                                        @foreach($salesmen as $sm)
                                            <option value="{{ $sm->id }}">{{ $sm->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('salesman_id')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="mt-2">
                                    <label for="cust_po">Customer PO Number</label>
                                    <input type="text" wire:model="cust_po" id="cust_po" class="form-control rounded" {{ ($isView || $this->isPosted) ? 'disabled' : '' }} placeholder="Enter Cust PO Number" autocomplete="off">
                                    @error('cust_po') <p class="text-danger">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- RIGHT --}}
                            <div class="col-xl-4 col-lg-6 do-header-stack">
                                <div id="field-do_num">
                                    <label for="do_num">DO Number <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="do_num" id="do_num" class="form-control rounded" {{ ($isView || $this->isPosted) ? 'disabled' : '' }} placeholder="Enter DO Number">
                                    @error('do_num') <p class="text-danger">{{ $message }}</p> @enderror
                                </div>
                                <div class="mt-2">
                                    <label for="ref_num">Reference Number</label>
                                    <input type="text" wire:model="ref_num" id="ref_num" class="form-control rounded" {{ ($isView || $this->isPosted) ? 'disabled' : '' }} placeholder="Enter Reference Number">
                                    @error('ref_num') <p class="text-danger">{{ $message }}</p> @enderror
                                </div>
                                @if($showInvoiceNoField)
                                    <div class="mt-2" id="field-invoice_no">
                                        <label for="invoice_no">Invoice No</label>
                                        <input type="text" wire:model="invoice_no" id="invoice_no" class="form-control rounded" {{ ($isView || $this->isPosted) ? 'disabled' : '' }} placeholder="Enter Invoice No" autocomplete="off">
                                        @error('invoice_no') <p class="text-danger">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                        </div>

                            <div class="do-items-table mb-3" id="field-items">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Delivery Order Items (Max 24 rows + NOTES)</h6>
                                    @php
                                        $currentRowCount = $this->getCurrentRowCount();
                                        $remainingRows = $this->getRemainingRowCount();
                                    @endphp
                                    <small class="text-muted">
                                        Used: <strong>{{ $currentRowCount }}</strong> / 24 rows | 
                                        Remaining: <strong>{{ $remainingRows }}</strong> rows
                                    </small>
                                </div>
                                @error('stackedItems')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                                <div class="do-table-shell">
                                <table class="table table-bordered do-fixed-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 30px;" class="text-center">#</th>
                                            <th style="width: 90px;" class="text-end">QTY</th>
                                            <th style="width: 90px;">UNIT</th>
                                            <th>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>Description</span>
                                                    @if((!$deliveryOrder || !$deliveryOrder->id) && !$isView)
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-primary"
                                                                wire:click="openDuplicateModal"
                                                                title="Duplicate items from an existing DO"
                                                                data-do-duplicate-button="1">
                                                            Duplicate DO (Ctrl+X)
                                                        </button>
                                                    @endif
                                                </div>
                                            </th>
                                            <th style="width: 165px;" class="text-center">Price</th>
                                            <th style="width: 135px;" class="text-center">Amount</th>
                                        </tr>
                                    </thead>
                                    @php
                                        // Build a map from row index to item index (for absolute row positioning)
                                        $rowToItemMap = [];
                                            $regularItemIndex = 0;
                                            
                                            foreach ($stackedItems as $idx => $item) {
                                                // Both text-only and regular items can have original_row_index
                                                if (isset($item['original_row_index']) && $item['original_row_index'] !== null) {
                                                    // Item has stored row position: use it (but skip row 24 - it's for NOTES)
                                                    $originalRow = $item['original_row_index'];
                                                    if ($originalRow < 24) {
                                                        $rowToItemMap[$originalRow] = $idx;
                                                    } else {
                                                        // Item is at row 24 or beyond - reassign to available row 0-23
                                                        while (isset($rowToItemMap[$regularItemIndex]) && $regularItemIndex < 24) {
                                                            $regularItemIndex++;
                                                        }
                                                        if ($regularItemIndex < 24) {
                                                            $rowToItemMap[$regularItemIndex] = $idx;
                                                            $regularItemIndex++;
                                                        }
                                                    }
                                                } else {
                                                    // Item doesn't have row position: find first available row
                                                    while (isset($rowToItemMap[$regularItemIndex]) && $regularItemIndex < 24) {
                                                        $regularItemIndex++;
                                                    }
                                                    if ($regularItemIndex < 24) {
                                                        $rowToItemMap[$regularItemIndex] = $idx;
                                                        $regularItemIndex++;
                                                    }
                                                }
                                            }
                                            
                                        $maxItemRowIndex = !empty($rowToItemMap) ? max(array_keys($rowToItemMap)) : -1;
                                        $rowsToShow = $this->getFormRowsToShow($maxItemRowIndex, count($rowToItemMap));
                                    @endphp
                                    <tbody wire:key="do-form-tbody-{{ $rowsToShow }}-{{ $this->getCurrentRowCount() }}">
                                        @for($rowIndex = 0; $rowIndex < $rowsToShow; $rowIndex++)
                                            @php
                                                // Map row index to item index (preserve absolute row positions)
                                                $itemIndex = $rowToItemMap[$rowIndex] ?? null;
                                                $item = $itemIndex !== null ? $stackedItems[$itemIndex] : null;
                                                $isEmptyRow = ($itemIndex === null);
                                                $freeFormRowData = $freeFormTextRows[$rowIndex] ?? null;
                                                $freeFormQty = is_array($freeFormRowData) ? (float) ($freeFormRowData['qty'] ?? 0) : 0;
                                                $freeFormPrice = is_array($freeFormRowData) ? (float) ($freeFormRowData['price'] ?? 0) : 0;
                                                $freeFormAmount = $freeFormQty * $freeFormPrice;
                                                $canMoveUp = $item && $rowIndex > 0 && !isset($rowToItemMap[$rowIndex - 1]);
                                                $canMoveDown = $item && $rowIndex < 23 && !isset($rowToItemMap[$rowIndex + 1]);
                                            @endphp
                                            <tr class="item-row" data-row-index="{{ $rowIndex }}" wire:key="do-form-row-{{ $rowIndex }}-{{ $itemIndex === null ? 'empty' : $itemIndex }}">
                                                <td class="text-center text-muted do-row-number-cell" style="width: 30px; vertical-align: top; font-size: 0.65em;">
                                                    {{ $rowIndex + 1 }}
                                                </td>
                                                <td class="do-qty-cell" style="width: 62px; vertical-align: top;">
                                                    @if($item)
                                                        @if(isset($item['is_choice']) && $item['is_choice'])
                                                            @php
                                                                $choiceOptQtys = [];
                                                                foreach (($item['choice_options'] ?? []) as $_co) {
                                                                    $choiceOptQtys[] = (float) ($_co['qty'] ?? 1);
                                                                }
                                                                $choiceQtySeen = [];
                                                                $choiceQtyUnique = [];
                                                                foreach ($choiceOptQtys as $_q) {
                                                                    $k = round($_q, 6);
                                                                    if (isset($choiceQtySeen[$k])) {
                                                                        continue;
                                                                    }
                                                                    $choiceQtySeen[$k] = true;
                                                                    $choiceQtyUnique[] = $_q;
                                                                }
                                                                $choiceQtyLabel = count($choiceQtyUnique) === 1
                                                                    ? (string) $choiceQtyUnique[0]
                                                                    : implode(' / ', $choiceQtyUnique);
                                                            @endphp
                                                            <input type="text"
                                                                class="form-control form-control-sm"
                                                                value="{{ $choiceQtyLabel }}"
                                                                disabled
                                                                title="Quantity for each OR option (applies after you pick one)"
                                                                style="max-width: 72px; background-color: #f8f9fa;"
                                                                data-do-role="qty">
                                                        @elseif((isset($item['is_text_only']) && $item['is_text_only']) || ($item['item']['id'] ?? null) === null)
                                                            <input type="text"
                                                                wire:model.lazy="stackedItems.{{ $itemIndex }}.item_qty"
                                                                class="form-control form-control-sm"
                                                                inputmode="decimal"
                                                                autocomplete="off"
                                                                {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                                style="max-width: 56px;"
                                                                data-do-role="qty">
                                                        @else
                                                            <input type="number" 
                                                                wire:model.lazy="stackedItems.{{ $itemIndex }}.item_qty" 
                                                                class="form-control form-control-sm @error('stackedItems.'.$itemIndex.'.item_qty') is-invalid @enderror" 
                                                                min="0.1" step="0.01" inputmode="decimal"
                                                                wire:change="updatePriceLine({{ $itemIndex }})" 
                                                                {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                                style="max-width: 56px;"
                                                                data-do-role="qty">
                                                            @error('stackedItems.'.$itemIndex.'.item_qty')
                                                                <div class="text-danger small text-end">!</div>
                                                            @enderror
                                                        @endif
                                                    @elseif(!$isView && $isEmptyRow)
                                                        <input type="text"
                                                            wire:model.lazy="freeFormTextRows.{{ $rowIndex }}.qty" {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                            class="form-control form-control-sm"
                                                            inputmode="decimal"
                                                            autocomplete="off"
                                                            style="max-width: 56px;"
                                                            data-do-role="qty">
                                                    @elseif(!$isView)
                                                        <input type="text" class="form-control form-control-sm" placeholder="Qty" disabled
                                                            style="width: 100%; background-color: #f8f9fa;">
                                                    @endif
                                                </td>
                                                <td style="width: 80px; vertical-align: top;">
                                                    @if($item)
                                                        @if(isset($item['is_choice']) && $item['is_choice'])
                                                            <input type="text" class="form-control form-control-sm" value="" placeholder="UOM" disabled
                                                                style="max-width: 86px; padding: 0.15rem 0.25rem; background-color: #f8f9fa;">
                                                        @elseif((isset($item['is_text_only']) && $item['is_text_only']) || ($item['item']['id'] ?? null) === null)
                                                            <input type="text" wire:model="stackedItems.{{ $itemIndex }}.custom_um"
                                                                class="form-control form-control-sm"
                                                                data-do-role="uom"
                                                                {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                                style="max-width: 86px; padding: 0.15rem 0.25rem;">
                                                        @else
                                                            <input type="text" wire:model="stackedItems.{{ $itemIndex }}.custom_um"
                                                                class="form-control form-control-sm"
                                                                placeholder="{{ ($item['item']['um'] ?? 'UNIT') === 'UNIT' ? 'UNITS' : ($item['item']['um'] ?? 'UOM') }}"
                                                                data-do-role="uom"
                                                                {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                                style="max-width: 86px; padding: 0.15rem 0.25rem;">
                                                        @endif
                                                    @elseif(!$isView && $isEmptyRow)
                                                        <input type="text" wire:model.lazy="freeFormTextRows.{{ $rowIndex }}.um" {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                            class="form-control form-control-sm"
                                                            data-do-role="uom"
                                                            style="max-width: 86px; padding: 0.15rem 0.25rem;">
                                                    @elseif(!$isView)
                                                        <input type="text" class="form-control form-control-sm" placeholder="UOM" disabled
                                                            style="width: 100%; background-color: #f8f9fa;">
                                                    @endif
                                                </td>
                                                <td style="vertical-align: top; position: relative;">
                                                    @if($item)
                                                        @if(isset($item['is_choice']) && $item['is_choice'])
                                                            <div class="d-flex gap-2 align-items-center" style="position: relative;">
                                                                <div style="flex: 1;">
                                                                    <select class="form-select form-select-sm @if(empty($item['choice_selected_item_id'])) is-invalid @endif"
                                                                            wire:change="resolveChoiceRow({{ $itemIndex }}, $event.target.value)"
                                                                            {{ ($isView || $this->isPosted) ? 'disabled' : '' }}>
                                                                        <option value="">Select one option...</option>
                                                                        @foreach(($item['choice_options'] ?? []) as $opt)
                                                                            <option value="{{ $opt['item_id'] }}">{{ $opt['item_name'] }} (Qty: {{ $opt['qty'] ?? 1 }})</option>
                                                                        @endforeach
                                                                    </select>
                                                                    @if(empty($item['choice_selected_item_id']))
                                                                        <small class="text-danger">Please select one option before posting.</small>
                                                                    @endif
                                                                </div>
                                                                @if(!$isView && !$this->isPosted)
                                                                    <button type="button"
                                                                        class="btn btn-sm p-0 px-1 btn-outline-secondary flex-shrink-0"
                                                                        wire:click="moveItemUp({{ $itemIndex }})"
                                                                        {{ $canMoveUp ? '' : 'disabled' }}
                                                                        title="Move up"
                                                                        style="font-size: 0.7rem;">
                                                                        ▲
                                                                    </button>
                                                                    <button type="button"
                                                                        class="btn btn-sm p-0 px-1 btn-outline-secondary flex-shrink-0"
                                                                        wire:click="moveItemDown({{ $itemIndex }})"
                                                                        {{ $canMoveDown ? '' : 'disabled' }}
                                                                        title="Move down"
                                                                        style="font-size: 0.7rem;">
                                                                        ▼
                                                                    </button>
                                                                    <button type="button" 
                                                                        class="btn btn-sm p-0 px-1 btn-danger flex-shrink-0"
                                                                        wire:click="removeItem({{ $itemIndex }})" {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                                        title="Delete"
                                                                        style="font-size: 0.7rem;">
                                                                        ×
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        @elseif(isset($item['is_text_only']) && $item['is_text_only'])
                                                            {{-- Text-only item: show text with delete button on the right --}}
                                                            <div class="d-flex gap-2 align-items-center" style="position: relative;">
                                                                <div style="flex: 1;">
                                                                    @if(!$isView && !$this->isPosted)
                                                                        <input type="text"
                                                                            wire:model.defer="stackedItems.{{ $itemIndex }}.custom_item_name"
                                                                            class="form-control form-control-sm"
                                                                            placeholder="Detail/text"
                                                                            style="font-size: 0.85em; padding: 0.15rem 0.25rem;">
                                                                    @else
                                                                        <span class="do-item-name-text">{{ $item['custom_item_name'] ?? '' }}</span>
                                                                    @endif
                                                                </div>
                                                                @if(!$isView && !$this->isPosted)
                                                                    <button type="button"
                                                                        class="btn btn-sm p-0 px-1 btn-outline-secondary flex-shrink-0"
                                                                        wire:click="moveItemUp({{ $itemIndex }})"
                                                                        {{ $canMoveUp ? '' : 'disabled' }}
                                                                        title="Move up"
                                                                        style="font-size: 0.7rem;">
                                                                        ▲
                                                                    </button>
                                                                    <button type="button"
                                                                        class="btn btn-sm p-0 px-1 btn-outline-secondary flex-shrink-0"
                                                                        wire:click="moveItemDown({{ $itemIndex }})"
                                                                        {{ $canMoveDown ? '' : 'disabled' }}
                                                                        title="Move down"
                                                                        style="font-size: 0.7rem;">
                                                                        ▼
                                                                    </button>
                                                                    <button type="button" 
                                                                        class="btn btn-sm p-0 px-1 btn-danger flex-shrink-0"
                                                                        wire:click="removeItem({{ $itemIndex }})" {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
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
                                                                editingName: false,
                                                                displayName: @js($stackedItems[$itemIndex]['custom_item_name'] ?? $item['item']['item_name'])
                                                }" 
                                                x-init="
                                                    $watch('showDescription', value => {
                                                                    if (value) {
                                                                        $wire.call('validateDescriptionRowsOnShow', {{ $itemIndex }});
                                                        }
                                                    });
                                                    // Update displayName when editingName changes to false
                                                    $watch('editingName', value => {
                                                        if (!value) {
                                                            // When editing ends, update displayName from Livewire
                                                            $nextTick(() => {
                                                                const livewireValue = $wire.get('stackedItems.{{ $itemIndex }}.custom_item_name');
                                                                displayName = livewireValue || '{{ $item['item']['item_name'] }}';
                                                            });
                                                        }
                                                    });
                                                ">
                                                            <div class="d-flex gap-2 align-items-start" style="position: relative;">
                                                                <div x-data="{ showMemo: false, hoverTimeout: null }"
                                                                     style="flex: 1; position: relative; cursor: pointer;"
                                                                     @mouseenter="hoverTimeout = setTimeout(() => { showMemo = true }, 800)"
                                                                     @mouseleave="clearTimeout(hoverTimeout); showMemo = false">
                                                                    <template x-if="!editingName">
                                                                        <div>
                                                                            <span class="do-item-name-text" x-text="displayName"></span>
                                                                            @if(!empty($item['item']['memo']))
                                                                                <div x-show="showMemo"
                                                                                     x-transition
                                                                                     class="memo-tooltip"
                                                                                     @click.stop>
                                                                                    <div class="memo-tooltip-body">{{ $item['item']['memo'] }}</div>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </template>
                                                                    <template x-if="editingName">
                                                                        <div class="d-flex gap-1 align-items-center">
                                                                            <input type="text" 
                                                                                x-ref="nameInput"
                                                                                class="form-control form-control-sm" 
                                                                                wire:model.defer="stackedItems.{{ $itemIndex }}.custom_item_name" {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                                                placeholder="{{ $item['item']['item_name'] }}"
                                                                                @keydown.enter.prevent="const newValue = $refs.nameInput.value || '{{ $item['item']['item_name'] }}'; $wire.set('stackedItems.{{ $itemIndex }}.custom_item_name', newValue); displayName = newValue; editingName = false"
                                                                                @keydown.escape="editingName = false"
                                                                                style="font-size: 0.85em;">
                                                                            <button type="button" 
                                                                                class="btn btn-sm btn-success p-1 px-2"
                                                                                @click="const newValue = $refs.nameInput.value || '{{ $item['item']['item_name'] }}'; $wire.set('stackedItems.{{ $itemIndex }}.custom_item_name', newValue); displayName = newValue; editingName = false"
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
                                                    @if(!$isView && !$this->isPosted)
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
                                                                        class="btn btn-sm p-0 px-1 btn-outline-secondary flex-shrink-0"
                                                                        wire:click="moveItemUp({{ $itemIndex }})"
                                                                        {{ $canMoveUp ? '' : 'disabled' }}
                                                                        title="Move up"
                                                                        style="font-size: 0.7rem;">
                                                                        ▲
                                                                    </button>
                                                                    <button type="button"
                                                                        class="btn btn-sm p-0 px-1 btn-outline-secondary flex-shrink-0"
                                                                        wire:click="moveItemDown({{ $itemIndex }})"
                                                                        {{ $canMoveDown ? '' : 'disabled' }}
                                                                        title="Move down"
                                                                        style="font-size: 0.7rem;">
                                                                        ▼
                                                                    </button>
                                                                    <button type="button" 
                                                                        class="btn btn-sm p-0 px-1 btn-danger flex-shrink-0"
                                                                        wire:click="removeItem({{ $itemIndex }})" {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                                        title="Delete"
                                                            style="font-size: 0.7rem;">
                                                                        ×
                                                        </button>
                                                    @endif
                                                </div>
                                                            @if($isView && !empty($stackedItems[$itemIndex]['more_description']))
                                                                <div class="ms-0 text-muted" style="font-size: 0.85em; margin-top: 14px; margin-bottom: 14px;">
                                                                    @foreach(explode("\n", $stackedItems[$itemIndex]['more_description']) as $line)
                                                            @if(trim($line) !== '')
                                                                <div>• {{ $line }}</div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if(!$isView)
                                                                <div x-show="showDescription" class="mt-1 mb-1 p-1" style="background-color: #f8f9fa; border-radius: 4px; border: 1px solid #dee2e6;">
                                                        <textarea 
                                                                        wire:model.defer="stackedItems.{{ $itemIndex }}.more_description" {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                            class="form-control form-control-sm"
                                                            rows="1"
                                                                        placeholder="Enter additional description"
                                                                        style="font-size: 0.78em; resize: vertical; min-height: 28px; padding: 0.15rem 0.3rem; line-height: 1.15;"></textarea>
                                                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                                                        <small class="text-muted" style="font-size: 0.7em;">
                                                                            Formula 1+N rows. Max 24 rows total.
                                                                        </small>
                                                                        <button type="button"
                                                                            wire:click="saveDescriptionAndValidate({{ $itemIndex }})" {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                                            class="btn btn-sm btn-primary"
                                                                            style="font-size: 0.7em; padding: 2px 9px;">
                                                                            Save
                                                                        </button>
                                                                    </div>
                                                    </div>
                                                    @endif
                                                        </div>
                                                @endif
                                                    @elseif(!$isView && $isEmptyRow)
                                                        {{-- Empty row: text line + open item picker (modal, F2) --}}
                                                        <div class="d-flex gap-2 align-items-center" style="position: relative; width: 100%;">
                                                            <input type="text"
                                                                wire:model.lazy="freeFormTextRows.{{ $rowIndex }}.text" {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                                class="form-control form-control-sm flex-grow-1"
                                                                placeholder="Type anything here"
                                                                style="font-size: 0.85em;"
                                                                data-do-role="desc">
                                                            <button type="button"
                                                                {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                                class="btn btn-sm btn-outline-primary"
                                                                style="font-size: 0.7em; padding: 2px 6px; white-space: nowrap; flex-shrink: 0;"
                                                                data-do-add-item-button="1"
                                                                data-do-open-item-picker="{{ $rowIndex }}">
                                                                + Add Item (F2)
                                                            </button>
                                                        </div>
                                                @endif
                                            </td>
                                            <td style="vertical-align: top;">
                                                @if($item && !(isset($item['is_choice']) && $item['is_choice']) && !(isset($item['is_text_only']) && $item['is_text_only']) && (($item['item']['id'] ?? null) !== null))
                                                    @php
                                                        $price = $stackedItems[$itemIndex]['item_unit_price'] ?? 0;
                                                        $tier = $stackedItems[$itemIndex]['pricing_tier'] ?? '';
                                                        $cashPrice = (float) ($item['item']['cash_price'] ?? 0);
                                                        $termPrice = (float) ($item['item']['term_price'] ?? 0);
                                                        $customerPrice = (float) ($item['item']['cust_price'] ?? 0);
                                                        $costPrice = (float) ($item['item']['cost'] ?? 0);
                                                        $previousPrice = (float) ($item['item']['latest_do_price'] ?? 0);
                                                    @endphp
                                                    <div class="d-flex align-items-center gap-1">
                                                        <select wire:model.live="stackedItems.{{ $itemIndex }}.pricing_tier"
                                                                wire:change="selectPricingTier({{ $itemIndex }}, $event.target.value)"
                                                                class="form-select form-select-sm do-price-tier-select" {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                                style="width: 70px; font-size: 0.75em; flex-shrink: 0;">
                                                            <option value="">Custom</option>
                                                            <option value="Cash Price">Cash {{ number_format($cashPrice, 2) }}</option>
                                                            <option value="Term Price">Term {{ number_format($termPrice, 2) }}</option>
                                                            <option value="Customer Price">Customer {{ number_format($customerPrice, 2) }}</option>
                                                            <option value="Cost">Cost {{ number_format($costPrice, 2) }}</option>
                                                            @if($cust_id && $previousPrice > 0)
                                                                <option value="Previous Price">Previous {{ number_format($previousPrice, 2) }}</option>
                                                            @endif
                                                        </select>
                                                        @if(($tier ?? '') === '')
                                                            <input type="text"
                                                                inputmode="decimal"
                                                                wire:model.lazy="stackedItems.{{ $itemIndex }}.item_unit_price"
                                                                wire:change="updateUnitPrice({{ $itemIndex }})"
                                                                class="form-control form-control-sm" {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                                placeholder="0.00"
                                                                style="width: 78px; font-size: 0.76em; text-align: right; flex-shrink: 0;">
                                                        @else
                                                            <span class="fw-bold form-control form-control-sm d-inline-block"
                                                                style="width: 78px; font-size: 0.76em; text-align: right; background-color: #f8f9fa; border: 1px solid #ced4da; border-radius: 0.25rem; padding: 0.12rem 0.25rem; line-height: 1.15; flex-shrink: 0;">
                                                                {{ number_format($price, 2) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @elseif($item && ((isset($item['is_text_only']) && $item['is_text_only']) || (($item['item']['id'] ?? null) === null)))
                                                    <input type="text"
                                                        inputmode="decimal"
                                                        wire:model.lazy="stackedItems.{{ $itemIndex }}.item_unit_price"
                                                        wire:change="updateUnitPrice({{ $itemIndex }})"
                                                        class="form-control form-control-sm" {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                        placeholder="0.00"
                                                        data-do-role="price"
                                                        style="width: 78px; font-size: 0.76em; text-align: right; margin-left: auto; display: block;">
                                                @elseif(!$isView && $isEmptyRow)
                                                    <input type="text"
                                                        inputmode="decimal"
                                                        wire:model.lazy="freeFormTextRows.{{ $rowIndex }}.price" {{ ($isView || $this->isPosted) ? 'disabled' : '' }}
                                                        class="form-control form-control-sm"
                                                        placeholder="0.00"
                                                        data-do-role="price"
                                                        style="width: 78px; font-size: 0.76em; text-align: right; margin-left: auto; display: block;">
                                                @endif
                                            </td>
                                            <td class="text-end" style="vertical-align: top;">
                                                @if($item)
                                                    <span class="fw-bold do-amount-cell" style="font-size: 0.8em; color: #0d6efd; white-space: nowrap;">
                                                        {{ number_format($stackedItems[$itemIndex]['amount'] ?? 0, 2) }}
                                                    </span>
                                                @elseif(!$isView && $isEmptyRow && ($freeFormQty > 0 || $freeFormPrice > 0))
                                                    <span class="fw-bold do-amount-cell" style="font-size: 0.8em; color: #0d6efd; white-space: nowrap;">
                                                        {{ number_format($freeFormAmount, 2) }}
                                                    </span>
                                                @endif
                                            </td>
                                            </tr>
                                        @endfor
                                        {{-- Hidden row 24 (NOTES) - only shown in preview/print, hidden in form --}}
                                        <tr class="item-row" style="display: none;">
                                            <td class="text-center text-muted" style="width: 44px; vertical-align: top; padding: 4px 8px;">
                                                25
                                            </td>
                                            <td class="do-qty-cell" style="width: 62px; vertical-align: top; padding: 4px 8px;">
                                                &nbsp;
                                            </td>
                                            <td style="width: 80px; vertical-align: top; padding: 4px 8px;">
                                                &nbsp;
                                            </td>
                                            <td style="vertical-align: top; padding: 4px 8px;">
                                                &nbsp;
                                            </td>
                                            <td style="vertical-align: top; padding: 4px 8px;">
                                                &nbsp;
                                            </td>
                                            <td style="vertical-align: top; padding: 4px 8px;">
                                                &nbsp;
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                </div>
                            </div>

                            <div class="text-end mb-3">
                                <div class="mb-3">
                                    @php
                                        $freeFormPreviewAmountTotal = 0;
                                        if (!empty($freeFormTextRows) && is_array($freeFormTextRows)) {
                                            foreach ($freeFormTextRows as $rowData) {
                                                if (!is_array($rowData)) {
                                                    continue;
                                                }
                                                $qty = (float) ($rowData['qty'] ?? 0);
                                                $price = (float) ($rowData['price'] ?? 0);
                                                $freeFormPreviewAmountTotal += ($qty * $price);
                                            }
                                        }
                                        $displayTotalAmount = (float) ($total_amount ?? 0) + $freeFormPreviewAmountTotal;
                                    @endphp
                                    <h6>Total Amount: 
                                        @if($deliveryOrder && $deliveryOrder->id)
                                            {{ $deliveryOrder->customerSnapshot->currency ?? $deliveryOrder->customer->currency ?? 'RM' }}
                                        @elseif($selectedCustomer)
                                            {{ $selectedCustomer->currency ?? 'RM' }}
                                        @else
                                            RM
                                        @endif
                                        {{ number_format($displayTotalAmount, 2) }}
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
                                            foreach ($freeFormTextRows as $rowData) {
                                                $text = is_array($rowData) ? ($rowData['text'] ?? '') : $rowData;
                                                if (!empty(trim($text ?? ''))) {
                                                    $hasFreeFormText = true;
                                                    break;
                                                }
                                            }
                                        }
                                        $hasContent = $hasItems || $hasFreeFormText;
                                        $canSaveExistingDoEmpty = $deliveryOrder && $deliveryOrder->id;
                                        $canPostOrSave = $hasContent || $canSaveExistingDoEmpty;
                                    @endphp
                                    @if(!$deliveryOrder || $deliveryOrder->status !== 'Completed')
                                        <button type="submit" class="btn btn-success me-2" @if(!$canPostOrSave) disabled @endif>Post</button>
                                    @endif
                                    <button type="button" class="btn btn-secondary me-2" wire:click="saveDraft" @if(!$canPostOrSave && !($deliveryOrder && $deliveryOrder->status === 'Completed')) disabled @endif>
                                        @if($deliveryOrder && $deliveryOrder->status === 'Completed')
                                            Restore All
                                        @else
                                            Save Draft
                                        @endif
                                    </button>
                                    <button type="button" class="btn btn-info" wire:click="preview" @if(!$canPostOrSave) disabled @endif>
                                        Preview
                                    </button>
                                </div>
                            </div>
                            @endif
                            @if($isView && $deliveryOrder)
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <a href="{{ route('delivery-orders') }}" class="btn btn-secondary">Back</a>
                                </div>
                                <div class="text-end">
                                    <a href="{{ route('delivery-orders.edit', ['deliveryOrder' => $deliveryOrder->id, 'restore' => 1]) }}" class="btn btn-primary me-2">Edit</a>
                                    <a href="{{ route('print.delivery-order.preview', ['id' => $deliveryOrder->id, 'return' => request()->fullUrl()]) }}" class="btn btn-info">Preview</a>
                                </div>
                            </div>
                            @endif
                        </form>

                        {{-- Global loading overlay for heavier DO actions (e.g. Duplicate DO) --}}
                        <div wire:loading.delay
                             wire:target="openDuplicateModal, confirmDuplicate"
                             class="do-loading-backdrop">
                            <div class="do-loading-content">
                                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                                <div class="mt-2 fw-semibold">Loading duplicate DO list...</div>
                                <div class="text-muted small">Please wait a moment.</div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>

    {{-- Duplicate DO Modal --}}
    @if($showDuplicateModal)
    <div class="modal fade show" tabindex="-1" style="display: block;" aria-modal="true" role="dialog">
        <div class="modal-backdrop fade show" style="z-index: 1040;"></div>
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="z-index: 1045;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Duplicate DO - Copy items from existing Delivery Order</h5>
                    <button type="button" class="btn-close" wire:click="closeDuplicateModal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0" style="min-height: 400px;">
                        {{-- Left: DO List --}}
                        <div class="col-md-4 border-end" style="max-height: 500px; overflow-y: auto;">
                            <div class="p-3">
                                <input type="text" wire:model.live.debounce.300ms="duplicateDoSearchTerm" class="form-control form-control-sm mb-2" placeholder="Search DO number or customer...">
                                <div class="list-group list-group-flush">
                                    @forelse($duplicateDoList as $do)
                                        <a href="javascript:void(0)" 
                                           class="list-group-item list-group-item-action py-2 {{ $duplicateSelectedDoId == $do->id ? 'active' : '' }}"
                                           wire:click="selectDoForDuplicate({{ $do->id }})">
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ $do->do_num }}</strong>
                                                <small>{{ $do->date ? \Carbon\Carbon::parse($do->date)->format('d/m/Y') : '-' }}</small>
                                            </div>
                                            <small class="text-muted">{{ $do->customerSnapshot->cust_name ?? $do->customer->cust_name ?? '-' }}</small>
                                            <div><small>{{ $do->customerSnapshot->currency ?? $do->customer->currency ?? 'RM' }} {{ number_format($do->total_amount ?? 0, 2) }}</small></div>
                                        </a>
                                    @empty
                                        <div class="list-group-item text-muted text-center py-4">No delivery orders found</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        {{-- Right: Preview --}}
                        <div class="col-md-8" style="min-height: 400px;">
                            @if($duplicateSelectedDoId)
                                <iframe src="{{ route('print.delivery-order.preview', $duplicateSelectedDoId) }}" 
                                        class="w-100 border-0" 
                                        style="height: 500px; min-height: 400px;"
                                        title="DO Preview"></iframe>
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 text-muted p-4">
                                    <div class="text-center">
                                        <p class="mb-0">Select a Delivery Order from the list to preview</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeDuplicateModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="confirmDuplicate" @if(!$duplicateSelectedDoId) disabled @endif>Confirm</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Instant (client-only) mode step for F2: shown before any Livewire round-trip; wire:ignore keeps Livewire from stripping it. --}}
    <div id="do-client-item-picker-modal" class="do-client-item-picker-modal" style="display: none;" wire:ignore>
        <div class="modal-backdrop fade show do-client-item-picker-backdrop" data-do-client-picker-close="1"></div>
        <div class="do-client-item-picker-center">
            <div class="modal-content modal-xl shadow">
                <div class="modal-header py-2">
                    <h5 class="modal-title mb-0" id="do-client-item-picker-title">Add item</h5>
                    <button type="button" class="btn-close" data-do-client-picker-close="1" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="border rounded p-3 bg-light"
                         data-do-client-picker-mode-select="1"
                         data-do-picker-active-index="0">
                        <label class="form-label small text-muted mb-2">Choose search mode first</label>
                        <div class="list-group">
                            <button type="button"
                                    class="list-group-item list-group-item-action active"
                                    id="do-client-item-picker-choice-code"
                                    data-do-picker-choice="code"
                                    data-do-client-picker-choose="code">
                                1. Search by Item Code
                            </button>
                            <button type="button"
                                    class="list-group-item list-group-item-action"
                                    id="do-client-item-picker-choice-name"
                                    data-do-picker-choice="name"
                                    data-do-client-picker-choose="name">
                                2. Search by Item Name
                            </button>
                        </div>
                        <p class="small text-muted mb-0 mt-2">
                            Use <strong>Arrow Up/Down</strong> to switch, then press <strong>Enter</strong>.
                            Or press <strong>1</strong> / <strong>2</strong> to choose directly.
                        </p>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-do-client-picker-close="1">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <livewire:d-o-item-picker />

    <style>
        :root {
            --do-grid-border: #d6deea;
            --do-grid-border-strong: #bcc8d9;
            --do-row-divider: #aebcd0;
            --do-row-alt: #fbfcfe;
            --do-row-focus: #f1f6ff;
            --do-row-focus-accent: #0d6efd;
        }

        .search-results {
            position: relative;
        }

        /* Slightly denser top form area (customer/date/do no, etc.) */
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

        /* Item picker modal: clear grid lines */
        .do-item-picker-table-wrap .do-item-picker-table th,
        .do-item-picker-table-wrap .do-item-picker-table td {
            border: 1px solid #c5cdd6 !important;
            vertical-align: middle;
        }
        .do-item-picker-table-wrap .do-item-picker-table thead th {
            border-bottom: 2px solid #aeb8c4 !important;
        }
        .do-item-picker-modal.do-item-picker-modal-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        .do-item-picker-table-wrap tr.do-item-picker-row.table-active > td {
            --bs-table-bg-state: var(--bs-table-active-bg);
            background-color: var(--bs-table-active-bg) !important;
            box-shadow: inset 3px 0 0 var(--bs-primary);
        }
        
        /* Cap form width on large monitors — full fluid width felt too wide */
        .do-form-page {
            max-width: 1080px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Fixed 23-row table layout */
        .do-table-shell {
            border: 1px solid #c8d3e2;
            border-radius: 8px;
            overflow: auto;
            background: #fff;
        }

        .do-fixed-table { 
            table-layout: fixed;
            width: 100%;
            border: 0;
            border-collapse: collapse;
            background: #fff;
            margin-bottom: 0;
        }
        
        .do-fixed-table th, .do-fixed-table td {
            padding: 4px 6px;
            vertical-align: middle;
            word-wrap: break-word;
            border-left: 1px solid #e7ecf4;
            border-right: 1px solid #e7ecf4;
            border-top: 0;
            border-bottom: 0;
            font-size: 0.82em;
        }
        
        .do-fixed-table th {
            font-size: 0.85em;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid var(--do-grid-border-strong);
            background: #f3f7fc;
            position: sticky;
            top: 0;
            z-index: 2;
            box-shadow: inset 0 -1px 0 var(--do-grid-border-strong);
            letter-spacing: 0.02em;
        }
        
        .do-fixed-table tbody tr {
            min-height: 24px;
            border-bottom: 1.6px solid var(--do-row-divider);
        }

        .do-fixed-table td:nth-child(2),
        .do-fixed-table td:nth-child(3),
        .do-fixed-table td:nth-child(5),
        .do-fixed-table td:nth-child(6) {
            background: #fcfdff;
        }

        /* QTY header and values align right toward UNIT */
        .do-fixed-table th:nth-child(2) {
            text-align: right;
        }

        .do-fixed-table td.do-qty-cell {
            text-align: right;
            vertical-align: top;
        }

        .do-fixed-table td:nth-child(5),
        .do-fixed-table td:nth-child(6) {
            text-align: center;
        }

        .do-fixed-table td:nth-child(6) {
            text-align: right;
            padding-right: 8px;
        }

        /* Tighten Description column so input aligns closer to cell edges */
        .do-fixed-table td:nth-child(4) {
            padding-left: 3px;
            padding-right: 3px;
        }

        .do-fixed-table td:nth-child(4) .form-control,
        .do-fixed-table td:nth-child(4) .form-control-sm {
            margin-left: 0;
            margin-right: 0;
        }
        
        .do-fixed-table .remark-row {
            background-color: #f8f9fa;
        }
        
        .do-fixed-table tbody .item-row:nth-child(even) {
            background-color: var(--do-row-alt);
        }

        .do-fixed-table .item-row:hover {
            background-color: #f7faff;
        }

        .do-fixed-table tbody tr:last-child {
            border-bottom: 0;
        }

        /* Strong focus cue to match "operator-first" row tracking */
        .do-fixed-table .item-row:focus-within {
            background-color: var(--do-row-focus) !important;
            box-shadow: inset 3px 0 0 var(--do-row-focus-accent);
        }

        /* Subtle section separators for easier scanning */
        .do-fixed-table th:nth-child(1),
        .do-fixed-table td:nth-child(1),
        .do-fixed-table th:nth-child(2),
        .do-fixed-table td:nth-child(2),
        .do-fixed-table th:nth-child(3),
        .do-fixed-table td:nth-child(3),
        .do-fixed-table th:nth-child(5),
        .do-fixed-table td:nth-child(5),
        .do-fixed-table th:nth-child(6),
        .do-fixed-table td:nth-child(6) {
            border-right: 1px solid #d2dcea;
        }

        .do-fixed-table th:nth-child(4),
        .do-fixed-table td:nth-child(4) {
            border-right: 1px solid #b8c6da;
        }

        /* Input fields in table */
        .do-fixed-table input[type="text"],
        .do-fixed-table input[type="number"],
        .do-fixed-table textarea {
            width: 100%;
            padding: 0.12rem 0.22rem;
            font-size: 0.8em;
            border: 1px solid transparent;
            border-radius: 0.2rem;
            background: transparent;
        }

        /*
         * QTY values: browsers treat input width:auto like "fill the cell", so margin-left:auto
         * does not pull the field right. Use a fixed width + inline-block inside text-align:right td.
         */
        .do-fixed-table td.do-qty-cell input[data-do-role="qty"] {
            display: inline-block !important;
            width: 4.5rem !important;
            max-width: 72px !important;
            min-width: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            text-align: right !important;
            box-sizing: border-box;
            vertical-align: top;
        }

        .do-fixed-table td.do-qty-cell input[type="number"][data-do-role="qty"]::-webkit-outer-spin-button,
        .do-fixed-table td.do-qty-cell input[type="number"][data-do-role="qty"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .do-fixed-table td.do-qty-cell input[type="number"][data-do-role="qty"] {
            -moz-appearance: textfield;
            appearance: textfield;
        }

        .do-fixed-table input[type="text"]:hover,
        .do-fixed-table input[type="number"]:hover,
        .do-fixed-table textarea:hover,
        .do-fixed-table select:hover {
            border-color: #d6deea;
            background: #fff;
        }

        .do-fixed-table .form-control-sm,
        .do-fixed-table .form-select-sm {
            min-height: calc(1.2em + 0.24rem + 2px);
            padding-top: 0.12rem;
            padding-bottom: 0.12rem;
            line-height: 1.1;
        }

        .do-fixed-table .form-select,
        .do-fixed-table .form-select-sm,
        .do-fixed-table .form-control-sm,
        .do-fixed-table .btn-sm {
            font-size: 0.8em;
        }

        .do-item-name-text {
            font-size: 0.84em;
            line-height: 1.2;
        }

        .do-row-number-cell {
            font-weight: 600;
            color: #73829a !important;
            background: #f7f9fc;
        }

        .do-amount-cell {
            font-variant-numeric: tabular-nums;
            font-family: "Segoe UI", Tahoma, sans-serif;
        }

        .do-qty-row {
            flex-wrap: nowrap;
        }

        /* Keep row height tight; show move controls only when needed */
        .do-move-actions {
            display: none !important;
        }

        .item-row:hover .do-move-actions,
        .item-row:focus-within .do-move-actions {
            display: flex !important;
        }

        .do-price-row {
            margin-top: 2px !important;
            line-height: 1.05;
            flex-wrap: nowrap !important;
            white-space: nowrap;
            overflow-x: auto;
        }

        .do-price-tier-select {
            padding-right: 1.15rem !important;
            background-position: right 0.3rem center;
            text-overflow: ellipsis;
        }

        [data-do-add-item-button] {
            border: 1px solid #cfd8e6 !important;
            background: #ffffff !important;
            color: #5a6f8f !important;
            padding: 1px 4px !important;
        }

        [data-do-add-item-button]:hover {
            border-color: #b8c9de !important;
            background: #f5f9ff !important;
            color: #3f5f87 !important;
        }

        .do-fixed-table td:nth-child(4) .btn.btn-sm {
            border-radius: 4px;
        }

        .do-fixed-table input[type="text"]:focus,
        .do-fixed-table input[type="number"]:focus,
        .do-fixed-table textarea:focus,
        .do-fixed-table select:focus {
            outline: none;
            border-color: #3d7be0;
            box-shadow: 0 0 0 0.12rem rgba(13, 110, 253, 0.18);
            background: #fff;
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

        .do-loading-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .do-loading-content {
            background: #ffffff;
            padding: 1.5rem 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            text-align: center;
        }

        .do-client-item-picker-modal {
            position: fixed;
            inset: 0;
            z-index: 20070;
        }
        .do-client-item-picker-backdrop {
            position: fixed;
            inset: 0;
            z-index: 20071;
            background: rgba(0, 0, 0, 0.45);
        }
        .do-client-item-picker-center {
            position: fixed;
            inset: 0;
            z-index: 20072;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            pointer-events: none;
        }
        .do-client-item-picker-center .modal-content {
            pointer-events: auto;
            width: 100%;
            max-width: 720px;
        }
        @media (max-width: 1200px) {
            .do-fixed-table th,
            .do-fixed-table td {
                padding: 2px 5px;
            }
        }
    </style>

    <script>
        (function () {
            function openClientPickerForRow(rowIdx) {
                var clientModal = document.getElementById('do-client-item-picker-modal');
                if (!clientModal || typeof Livewire === 'undefined') return;
                if (document.querySelector('.do-item-picker-modal:not(.d-none)')) {
                    Livewire.dispatch('do-item-picker-close');
                }

                clientModal.setAttribute('data-row-index', String(rowIdx));
                var titleEl = document.getElementById('do-client-item-picker-title');
                if (titleEl) titleEl.textContent = 'Add item — row ' + (rowIdx + 1);

                var modeBox = clientModal.querySelector('[data-do-client-picker-mode-select="1"]');
                if (modeBox) {
                    modeBox.setAttribute('data-do-picker-active-index', '0');
                    modeBox.style.display = '';
                }
                var modeBtns = clientModal.querySelectorAll('[data-do-client-picker-choose]');
                modeBtns.forEach(function (b, i) { b.classList.toggle('active', i === 0); });

                clientModal.style.display = 'block';
                clientModal.setAttribute('aria-modal', 'true');
                setTimeout(function () {
                    document.getElementById('do-client-item-picker-choice-code')?.focus();
                }, 0);
            }

            // F2: open the item picker modal for the current (or last) row; F2 again closes it.
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'F2') return;

                var form = e.target.closest('form[wire\\:submit\\.prevent="addDO"]');
                if (!form) return;

                e.preventDefault();

                var lwRoot = form.closest('[wire\\:id]');
                if (!lwRoot || typeof Livewire === 'undefined') return;
                var comp = Livewire.find(lwRoot.getAttribute('wire:id'));
                if (!comp) return;

                var clientModal = document.getElementById('do-client-item-picker-modal');
                if (clientModal && clientModal.style.display !== 'none') {
                    clientModal.style.display = 'none';
                    clientModal.removeAttribute('data-row-index');
                    Livewire.dispatch('do-item-picker-close');
                    return;
                }

                if (document.querySelector('.do-item-picker-modal:not(.d-none)')) {
                    Livewire.dispatch('do-item-picker-close');
                    return;
                }

                var currentRow = e.target.closest('tr.item-row');
                var targetRow = currentRow;
                if (!targetRow) {
                    var rows = form.querySelectorAll('tr.item-row');
                    if (rows.length === 0) return;
                    targetRow = rows[rows.length - 1];
                }
                if (!targetRow || !targetRow.dataset.rowIndex) return;
                var rowIdx = parseInt(targetRow.dataset.rowIndex, 10);
                if (isNaN(rowIdx)) return;

                openClientPickerForRow(rowIdx);
            });

            document.addEventListener('click', function (e) {
                var openBtn = e.target.closest('[data-do-open-item-picker]');
                if (!openBtn) return;
                var rowIdx = parseInt(openBtn.getAttribute('data-do-open-item-picker') || '', 10);
                if (isNaN(rowIdx)) return;
                e.preventDefault();
                openClientPickerForRow(rowIdx);
            });
        })();
        (function () {
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;
                var clientModal = document.getElementById('do-client-item-picker-modal');
                if (clientModal && clientModal.style.display !== 'none') {
                    clientModal.style.display = 'none';
                    clientModal.removeAttribute('data-row-index');
                    var formForClient = document.querySelector('form[wire\\:submit\\.prevent="addDO"]');
                    if (formForClient) {
                        var rootForClient = formForClient.closest('[wire\\:id]');
                        if (rootForClient && typeof Livewire !== 'undefined') {
                            var compForClient = Livewire.find(rootForClient.getAttribute('wire:id'));
                            Livewire.dispatch('do-item-picker-close');
                        }
                    }
                    return;
                }
                var modal = document.querySelector('.do-item-picker-modal:not(.d-none)');
                if (!modal) return;
                var form = document.querySelector('form[wire\\:submit\\.prevent="addDO"]');
                if (!form) return;
                var lwRoot = form.closest('[wire\\:id]');
                if (!lwRoot || typeof Livewire === 'undefined') return;
                var comp = Livewire.find(lwRoot.getAttribute('wire:id'));
                if (comp) Livewire.dispatch('do-item-picker-close');
            });
        })();
        (function () {
            function getModeButtons(container) {
                if (!container) return [];
                return Array.from(container.querySelectorAll('[data-do-picker-choice]'));
            }

            function setActiveChoice(container, index) {
                var buttons = getModeButtons(container);
                if (buttons.length === 0) return;

                var clamped = Math.max(0, Math.min(index, buttons.length - 1));
                container.setAttribute('data-do-picker-active-index', String(clamped));

                buttons.forEach(function (btn, i) {
                    if (i === clamped) {
                        btn.classList.add('active');
                        btn.focus();
                    } else {
                        btn.classList.remove('active');
                    }
                });
            }

            document.addEventListener('keydown', function (e) {
                // Do not steal digits while typing in fields (e.g. item code/name search).
                var t = e.target;
                var tag = t && t.tagName ? t.tagName.toLowerCase() : '';
                if (tag === 'input' || tag === 'textarea' || tag === 'select' || (t && t.isContentEditable)) {
                    return;
                }

                // When client mode chooser is visible, it must own keyboard selection.
                // Otherwise 1/2/Enter may target hidden preloaded server mode buttons.
                var clientModal = document.getElementById('do-client-item-picker-modal');
                var clientContainer = null;
                if (clientModal && window.getComputedStyle(clientModal).display !== 'none') {
                    clientContainer = clientModal.querySelector('[data-do-client-picker-mode-select="1"]');
                }
                var serverContainer = document.querySelector('.do-item-picker-modal:not(.d-none) [data-do-picker-mode-select="1"]');
                var container = clientContainer || serverContainer;
                if (!container) return;

                var buttons = getModeButtons(container);
                if (buttons.length === 0) return;

                var activeIndex = parseInt(container.getAttribute('data-do-picker-active-index') || '0', 10);
                if (isNaN(activeIndex)) activeIndex = 0;

                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    setActiveChoice(container, activeIndex - 1);
                    return;
                }
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    setActiveChoice(container, activeIndex + 1);
                    return;
                }
                if (e.key === '1') {
                    e.preventDefault();
                    buttons[0].click();
                    return;
                }
                if (e.key === '2') {
                    e.preventDefault();
                    if (buttons[1]) buttons[1].click();
                    return;
                }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    var chosen = buttons[activeIndex] || buttons[0];
                    if (chosen) chosen.click();
                }
            });

            document.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-do-picker-choice]');
                if (!btn) return;
                var container = btn.closest('[data-do-picker-mode-select="1"]')
                    || btn.closest('[data-do-client-picker-mode-select="1"]');
                if (!container) return;
                var buttons = getModeButtons(container);
                var idx = buttons.indexOf(btn);
                if (idx >= 0) container.setAttribute('data-do-picker-active-index', String(idx));
            });
        })();
        (function () {
            document.addEventListener('click', function (e) {
                if (e.target.closest('[data-do-client-picker-close]')) {
                    var cm = document.getElementById('do-client-item-picker-modal');
                    if (cm && cm.style.display !== 'none') {
                        cm.style.display = 'none';
                        cm.removeAttribute('data-row-index');
                    }
                    var formClose = document.querySelector('form[wire\\:submit\\.prevent="addDO"]');
                    if (formClose) {
                        var rootClose = formClose.closest('[wire\\:id]');
                        if (rootClose && typeof Livewire !== 'undefined') {
                            var compClose = Livewire.find(rootClose.getAttribute('wire:id'));
                            Livewire.dispatch('do-item-picker-close');
                        }
                    }
                    return;
                }
                var choose = e.target.closest('[data-do-client-picker-choose]');
                if (!choose) return;
                var cm = document.getElementById('do-client-item-picker-modal');
                if (!cm || cm.style.display === 'none') return;
                var rowIdx = parseInt(cm.getAttribute('data-row-index') || '', 10);
                if (isNaN(rowIdx)) return;
                var mode = choose.getAttribute('data-do-client-picker-choose');
                if (mode !== 'code' && mode !== 'name') return;

                var form = document.querySelector('form[wire\\:submit\\.prevent="addDO"]');
                if (!form) return;
                var lwRoot = form.closest('[wire\\:id]');
                if (!lwRoot || typeof Livewire === 'undefined') return;
                var comp = Livewire.find(lwRoot.getAttribute('wire:id'));
                if (!comp) return;

                cm.style.display = 'none';
                cm.removeAttribute('data-row-index');
                Livewire.dispatch('do-item-picker-open', { rowIndex: rowIdx, mode: mode });
            });
        })();
        (function () {
            var pickerListActiveIndex = -1;

            function getPickerLivewire() {
                var modal = document.querySelector('.do-item-picker-modal:not(.d-none)');
                if (!modal || typeof Livewire === 'undefined') return null;
                var root = modal.closest('[wire\\:id]');
                if (!root) return null;
                return Livewire.find(root.getAttribute('wire:id'));
            }

            function getPickerDataRows() {
                var wrap = document.querySelector('.do-item-picker-modal:not(.d-none) .do-item-picker-table-wrap');
                if (!wrap) return [];
                return Array.from(wrap.querySelectorAll('tbody tr.do-item-picker-row[data-item-id]'));
            }

            function clearPickerHighlight() {
                document.querySelectorAll('.do-item-picker-modal:not(.d-none) tr.do-item-picker-row').forEach(function (row) {
                    row.classList.remove('table-active');
                });
            }

            function applyPickerHighlight(index) {
                var rows = getPickerDataRows();
                clearPickerHighlight();
                if (index < 0 || index >= rows.length) return;
                rows[index].classList.add('table-active');
                rows[index].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }

            function syncPickerIndexAfterDomChange() {
                var rows = getPickerDataRows();
                if (pickerListActiveIndex >= rows.length) {
                    pickerListActiveIndex = -1;
                    clearPickerHighlight();
                }
            }

            document.addEventListener('input', function (e) {
                if (e.target && e.target.id === 'do-item-picker-search') {
                    pickerListActiveIndex = -1;
                    clearPickerHighlight();
                }
            });

            document.addEventListener('click', function (e) {
                var row = e.target.closest('.do-item-picker-modal:not(.d-none) tr.do-item-picker-row[data-item-id]');
                if (!row) return;
                var rows = getPickerDataRows();
                var idx = rows.indexOf(row);
                if (idx >= 0) pickerListActiveIndex = idx;
            });

            document.addEventListener('keydown', function (e) {
                if (!document.querySelector('.do-item-picker-modal:not(.d-none)')) return;
                var search = document.getElementById('do-item-picker-search');
                if (!search) return;

                var rows = getPickerDataRows();
                if (rows.length === 0) return;

                var t = e.target;
                var inSearch = t === search;
                var inPickerRow = t.closest && t.closest('.do-item-picker-modal:not(.d-none) tr.do-item-picker-row');
                if (!inSearch && !inPickerRow) return;

                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    syncPickerIndexAfterDomChange();
                    rows = getPickerDataRows();
                    if (rows.length === 0) return;

                    if (e.key === 'ArrowDown') {
                        if (pickerListActiveIndex < rows.length - 1) {
                            pickerListActiveIndex++;
                        } else {
                            pickerListActiveIndex = rows.length - 1;
                        }
                        if (pickerListActiveIndex < 0) pickerListActiveIndex = 0;
                        applyPickerHighlight(pickerListActiveIndex);
                        return;
                    }
                    if (pickerListActiveIndex > 0) {
                        pickerListActiveIndex--;
                        applyPickerHighlight(pickerListActiveIndex);
                    } else {
                        pickerListActiveIndex = -1;
                        clearPickerHighlight();
                    }
                    return;
                }

                if (e.key !== 'Enter') return;

                syncPickerIndexAfterDomChange();
                rows = getPickerDataRows();
                if (rows.length === 0) return;

                var idx = pickerListActiveIndex >= 0 ? pickerListActiveIndex : 0;
                var row = rows[idx];
                if (!row) return;

                var id = row.getAttribute('data-item-id');
                if (!id) return;

                e.preventDefault();
                e.stopPropagation();
                var pickerComp = getPickerLivewire();
                if (pickerComp) pickerComp.call('selectItem', parseInt(id, 10));
            }, true);
        })();
    </script>
    <script>
        (function () {
            // Ctrl+X handling:
            // - Triggers the Duplicate DO feature (same as clicking the button)
            // - Works even if focus is not currently inside the form
            document.addEventListener('keydown', function (e) {
                // Ctrl+X (or Cmd+X on Mac just in case)
                const isCtrlX = (e.key === 'x' || e.key === 'X') && (e.ctrlKey || e.metaKey);
                if (!isCtrlX) return;

                // Find the DO form on the page
                var form = document.querySelector('form[wire\\:submit\\.prevent="addDO"]');
                if (!form) return;

                var duplicateBtn = form.querySelector('[data-do-duplicate-button]');
                if (duplicateBtn && !duplicateBtn.disabled) {
                    // Prevent the browser's default cut behavior to avoid delay
                    e.preventDefault();
                    duplicateBtn.click();
                }
            });
        })();
    </script>
    <script>
        (function() {
            var registered = false;
            function registerScrollToError() {
                if (typeof Livewire === 'undefined' || registered) return;
                registered = true;
                Livewire.on('scroll-to-first-error', (event) => {
                    var payload = event && event[0];
                    var firstKey = (payload && payload.firstKey) || (typeof payload === 'string' ? payload : null);
                    if (!firstKey) return;

                    var el = null;
                    if (firstKey === 'cust_id') el = document.getElementById('field-cust_id');
                    else if (firstKey === 'date') el = document.getElementById('field-date');
                    else if (firstKey === 'do_num') el = document.getElementById('field-do_num');
                    else if (typeof firstKey === 'string' && firstKey.indexOf('stackedItems.') === 0) el = document.getElementById('field-items');

                    if (el) {
                        setTimeout(function() {
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 150);
                    }
                });
            }
            document.addEventListener('livewire:init', registerScrollToError);
            if (document.readyState !== 'loading' && typeof Livewire !== 'undefined') registerScrollToError();
        })();
    </script>
    <script>
        (function() {
            var registered = false;
            function registerFocusQtyAfterAdd() {
                if (typeof Livewire === 'undefined' || registered) return;
                registered = true;
                Livewire.on('focus-qty-row', (event) => {
                    var payload = event && event[0];
                    var rowIndex = payload && payload.rowIndex;
                    if (rowIndex === null || rowIndex === undefined) return;

                    // Wait for Livewire DOM patch to complete before focusing.
                    setTimeout(function() {
                        var row = document.querySelector('tr.item-row[data-row-index="' + rowIndex + '"]');
                        if (!row) return;

                        var qtyInput = row.querySelector('[data-do-role="qty"]:not([disabled])');
                        if (!qtyInput) return;

                        qtyInput.focus();
                        if (typeof qtyInput.select === 'function') qtyInput.select();
                    }, 0);
                });
            }
            document.addEventListener('livewire:init', registerFocusQtyAfterAdd);
            if (document.readyState !== 'loading' && typeof Livewire !== 'undefined') registerFocusQtyAfterAdd();
        })();
    </script>
    <script>
        (function() {
            // Enter handling inside DO form:
            // - Never submits the form
            // - From QTY field: jump to same-row UOM field
            // - Otherwise from item-row field: jump into the NEXT row's description text field (if any)
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter') return;

                // Let field-specific handlers (e.g. Alpine x-on:keydown.enter.prevent) win
                if (e.defaultPrevented) return;

                // Only handle inside the DO form
                var form = e.target.closest('form[wire\\:submit\\.prevent="addDO"]');
                if (!form) return;

                e.preventDefault();

                // Only handle movement inside the items grid rows
                var currentRow = e.target.closest('tr.item-row');
                if (!currentRow) {
                    // Outside grid in the form: Enter is just blocked (no submit, no move)
                    return;
                }

                // If Enter was pressed in QTY, move to UOM in the same row first.
                var isQtyTarget = e.target.matches('[data-do-role="qty"]');
                if (isQtyTarget) {
                    var unitInput = currentRow.querySelector('[data-do-role="uom"]:not([disabled])');
                    if (unitInput) {
                        unitInput.focus();
                        if (typeof unitInput.select === 'function') unitInput.select();
                        return;
                    }
                }

                // If Enter was pressed in UOM, move to description in the same row first.
                var isUomTarget = e.target.matches('[data-do-role="uom"]');
                if (isUomTarget) {
                    var sameRowDesc = currentRow.querySelector('[data-do-role="desc"]:not([disabled])');
                    if (sameRowDesc) {
                        sameRowDesc.focus();
                        if (typeof sameRowDesc.select === 'function') sameRowDesc.select();
                        return;
                    }
                }

                // If Enter was pressed in description, move to price in the same row first.
                var isDescTarget = e.target.matches('[data-do-role="desc"]');
                if (isDescTarget) {
                    var sameRowPrice = currentRow.querySelector('[data-do-role="price"]:not([disabled])');
                    if (sameRowPrice) {
                        sameRowPrice.focus();
                        if (typeof sameRowPrice.select === 'function') sameRowPrice.select();
                        return;
                    }
                }

                // Find the next item-row that has a free-form description input
                var nextRow = currentRow.nextElementSibling;
                while (nextRow) {
                    if (nextRow.classList.contains('item-row')) {
                        var descInput = nextRow.querySelector('[data-do-role="desc"]');
                        if (descInput && !descInput.disabled) {
                            descInput.focus();
                            if (typeof descInput.select === 'function') descInput.select();
                            return;
                        }
                    }
                    nextRow = nextRow.nextElementSibling;
                }
                // If no suitable next-row description is found, do nothing else (focus stays put).
            });
        })();
    </script>
    <script>
        (function() {
            // Arrow key handling inside DO item grid:
            // - Left / Right: move between fields in the same row
            // - Up / Down: move to the same field role in previous/next row
            var roles = ['qty', 'uom', 'desc', 'price'];

            function focusField(row, role) {
                if (!row) return false;
                var selector = '[data-do-role="' + role + '"]:not([disabled])';
                var target = row.querySelector(selector);
                if (!target) return false;
                target.focus();
                if (typeof target.select === 'function') target.select();
                return true;
            }

            document.addEventListener('keydown', function (e) {
                if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) return;
                if (e.defaultPrevented) return;

                var source = e.target;
                if (!source || !source.matches('[data-do-role]')) return;

                // Only handle movement inside the DO form.
                var form = source.closest('form[wire\\:submit\\.prevent="addDO"]');
                if (!form) return;

                var currentRow = source.closest('tr.item-row');
                if (!currentRow) return;

                var currentRole = source.getAttribute('data-do-role');
                var roleIdx = roles.indexOf(currentRole);
                if (roleIdx === -1) return;

                var rows = Array.from(form.querySelectorAll('tr.item-row'));
                var rowIdx = rows.indexOf(currentRow);
                if (rowIdx === -1) return;

                var moved = false;

                if (e.key === 'ArrowLeft') {
                    for (var leftIdx = roleIdx - 1; leftIdx >= 0; leftIdx--) {
                        if (focusField(currentRow, roles[leftIdx])) {
                            moved = true;
                            break;
                        }
                    }
                } else if (e.key === 'ArrowRight') {
                    for (var rightIdx = roleIdx + 1; rightIdx < roles.length; rightIdx++) {
                        if (focusField(currentRow, roles[rightIdx])) {
                            moved = true;
                            break;
                        }
                    }
                } else if (e.key === 'ArrowUp') {
                    for (var upRowIdx = rowIdx - 1; upRowIdx >= 0; upRowIdx--) {
                        if (focusField(rows[upRowIdx], currentRole)) {
                            moved = true;
                            break;
                        }
                    }
                } else if (e.key === 'ArrowDown') {
                    for (var downRowIdx = rowIdx + 1; downRowIdx < rows.length; downRowIdx++) {
                        if (focusField(rows[downRowIdx], currentRole)) {
                            moved = true;
                            break;
                        }
                    }
                }

                if (moved) e.preventDefault();
            });
        })();
    </script>
    <script>
        (function () {
            if (window.__doDuplicatePreviewBackBound) return;
            window.__doDuplicatePreviewBackBound = true;
            window.addEventListener('message', function (e) {
                if (!e.data || e.data.type !== 'do-duplicate-preview-back') return;
                if (typeof Livewire === 'undefined') return;
                Livewire.dispatch('do-duplicate-preview-back');
            });
        })();
    </script>
    
</div>
