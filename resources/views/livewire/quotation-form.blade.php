<div>
<div class="container-fluid my-3 px-2 px-md-3">
    <div class="do-form-page">
        <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold fs-5">{{ $isView ? 'View' : ($quotation ? 'Edit' : 'Add') }} Quotation</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="addQuotation">
                        <div class="do-header-fields">
                        <div class="row mb-3 align-items-start g-3 do-header-three-col">
                            <div class="col-xl-4 col-lg-12 d-flex flex-column" id="field-cust_id" x-data="{ hi: 0 }">
                                @if(!$quotation || !$isView)
                                    <label for="customer">Customer <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.debounce.300ms.live="customerSearchTerm" id="searchCustomer" class="form-control rounded" placeholder="Search customer" {{ $isView ? 'disabled' : '' }} autocomplete="off" x-on:input="hi = 0"
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
                                    <div class="do-customer-detail mt-2">
                                        @if($isView)
                                            <p class="fw-bold mb-1 do-customer-detail-title">{{ $quotation->customerSnapshot->cust_name ?? $quotation->customer->cust_name }}</p>
                                        @endif
                                        <p class="mb-0"><span class="text-muted">Currency:</span> {{ $quotation->customerSnapshot->currency ?? $quotation->customer->currency ?? 'RM' }}</p>
                                        <p class="mb-0">{{ $quotation->customerSnapshot->address_line1 ?? $quotation->customer->address_line1 }}</p>
                                        <p class="mb-0">{{ $quotation->customerSnapshot->address_line2 ?? $quotation->customer->address_line2 }}</p>
                                        @if($quotation->customerSnapshot->address_line3 ?? $quotation->customer->address_line3)
                                            <p class="mb-0">{{ $quotation->customerSnapshot->address_line3 ?? $quotation->customer->address_line3 }}</p>
                                        @endif
                                        @if($quotation->customerSnapshot->address_line4 ?? $quotation->customer->address_line4)
                                            <p class="mb-0">{{ $quotation->customerSnapshot->address_line4 ?? $quotation->customer->address_line4 }}</p>
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
                                    <input type="date" wire:model="date" id="date" class="form-control rounded" placeholder="dd/mm/yyyy" {{ $isView ? 'disabled' : '' }}>
                                    @error('date') <p class="text-danger">{{ $message }}</p> @enderror
                                </div>
                                <div class="mt-2" id="field-salesman_id">
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
                            </div>

                            <div class="col-xl-4 col-lg-6 do-header-stack">
                                <div id="field-quotation_num">
                                    <label for="quotation_num">Quotation Number <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="quotation_num" id="quotation_num" class="form-control rounded" {{ $isView ? 'disabled' : '' }} @if(!$isView && (!$quotation || !$quotation->id)) readonly @endif placeholder="Quotation number (assigned on save)">
                                    @error('quotation_num') <p class="text-danger">{{ $message }}</p> @enderror
                                </div>
                                <div class="mt-2">
                                    <label for="ref_num">Reference Number</label>
                                    <input type="text" wire:model="ref_num" id="ref_num" class="form-control rounded" {{ $isView ? 'disabled' : '' }} placeholder="Enter Reference Number">
                                    @error('ref_num') <p class="text-danger">{{ $message }}</p> @enderror
                                </div>
                                <div class="mt-2">
                                    <label for="remark">Remark</label>
                                    <textarea wire:model="remark" id="remark" class="form-control rounded" rows="3" {{ $isView ? 'disabled' : '' }} placeholder="Enter Remark"></textarea>
                                    @error('remark') <p class="text-danger">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                        </div>

                        @include('livewire.partials.quotation-do-style-items-table')

                        <div class="text-end mb-3">
                            <div class="mb-3">
                                @php
                                    $freeFormPreviewAmountTotal = 0;
                                    if (!empty($freeFormTextRows) && is_array($freeFormTextRows)) {
                                        foreach ($freeFormTextRows as $rowData) {
                                            if (!is_array($rowData)) continue;
                                            $qty = (float) ($rowData['qty'] ?? 0);
                                            $price = (float) ($rowData['price'] ?? 0);
                                            $freeFormPreviewAmountTotal += ($qty * $price);
                                        }
                                    }
                                    $displayTotalAmount = (float) ($total_amount ?? 0) + $freeFormPreviewAmountTotal;
                                @endphp
                                <h6>Total Amount:
                                    @if($quotation && $quotation->id)
                                        {{ $quotation->customerSnapshot->currency ?? $quotation->customer->currency ?? 'RM' }}
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
                        @php
                            $hasItems = !empty($stackedItems) && count($stackedItems) > 0;
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
                        @endphp
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('quotations') }}" class="btn btn-secondary">Back</a>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-success me-2" @if(!$hasContent) disabled @endif
                                    x-on:click="syncQuotationGridInputsBeforeSave($wire).then(() => $wire.markSent())">Sent</button>
                                <button type="button" class="btn btn-secondary me-2" @if(!$hasContent) disabled @endif
                                    x-on:click="syncQuotationGridInputsBeforeSave($wire).then(() => $wire.saveDraft())">
                                    Save to Draft
                                </button>
                                <button type="button" class="btn btn-info" @if(!$hasContent) disabled @endif
                                    x-on:click="syncQuotationGridInputsBeforeSave($wire).then(() => $wire.preview())">
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

@if(!$isView)
    @include('livewire.partials.form-item-picker', ['wireFormSubmit' => 'addQuotation'])
    @include('livewire.partials.do-form-items-grid-keyboard', ['wireFormSubmit' => 'addQuotation'])
    <script>
        window.syncQuotationGridInputsBeforeSave = function (wire) {
            var form = document.querySelector('form[wire\\:submit\\.prevent="addQuotation"]');
            if (!form || !wire) {
                return Promise.resolve();
            }

            var active = document.activeElement;
            if (active && form.contains(active) && typeof active.blur === 'function') {
                active.blur();
            }

            var sets = [];

            form.querySelectorAll('tr.item-row[data-row-index]').forEach(function (row) {
                var rowIndex = row.getAttribute('data-row-index');
                if (rowIndex === null) {
                    return;
                }

                row.querySelectorAll('[data-quotation-free-form-field]').forEach(function (input) {
                    var field = input.getAttribute('data-quotation-free-form-field');
                    var anchorKey = input.getAttribute('data-quotation-free-form-anchor') || rowIndex;
                    if (!field || input.disabled) {
                        return;
                    }
                    sets.push(wire.set('freeFormTextRows.' + anchorKey + '.' + field, input.value));
                });
            });

            form.querySelectorAll('[data-quotation-item-description]').forEach(function (textarea) {
                var idx = textarea.getAttribute('data-quotation-stacked-index');
                if (idx === null || textarea.disabled) {
                    return;
                }
                sets.push(wire.set('stackedItems.' + idx + '.more_description', textarea.value));
            });

            form.querySelectorAll('[data-quotation-text-only-name]').forEach(function (input) {
                var idx = input.getAttribute('data-quotation-stacked-index');
                if (idx === null || input.disabled) {
                    return;
                }
                sets.push(wire.set('stackedItems.' + idx + '.custom_item_name', input.value));
            });

            return sets.length ? Promise.all(sets) : Promise.resolve();
        };
    </script>
@endif

@include('livewire.partials.do-form-page-shared-styles')
@include('livewire.partials.do-form-items-table-styles')
</div>
