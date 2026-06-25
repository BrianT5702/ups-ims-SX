@php
    $rowToItemMap = $this->getQuotationRowToItemMap();
    $rowSequenceMap = $this->getQuotationRowToItemSequenceMap();
    $sequenceTotal = count($rowSequenceMap);
    $printLayout = $this->quotationPrintLayout();
    $layoutBaseRows = $printLayout->baseRowMap();
    $rowsToShow = $this->getFormRowsToShow();
    $viewportRows = $this->getQuotationViewportRows();
    $currentRowCount = $this->getCurrentRowCount();
    $remainingRows = $this->getRemainingRowCount();
    $maxPrintRows = $this->getQuotationMaxPrintRows();
    $printStatus = $this->getQuotationPrintPageStatus();
    $quotationPage2StartRow = $this->getQuotationPage2StartRowIndex();
@endphp

<div class="do-items-table mb-3" id="field-items">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Quotation Items</h6>
        <small class="text-muted">
            Used: <strong data-do-row-used>{{ $currentRowCount }}</strong> / {{ $maxPrintRows }} rows |
            Remaining: <strong data-do-row-remaining>{{ $remainingRows }}</strong> rows
            · <strong data-quotation-print-pages>{{ $printStatus['pages'] }}</strong> Pages
        </small>
    </div>
    @error('stackedItems')
        <p class="text-danger">{{ $message }}</p>
    @enderror
    <div class="do-table-shell quotation-items-table-shell">
        <table class="table table-bordered do-fixed-table">
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">#</th>
                    <th style="width: 90px;" class="text-end">QTY</th>
                    <th style="width: 90px;">UNIT</th>
                    <th>Description</th>
                    <th style="width: 165px;" class="text-center">Price</th>
                    <th style="width: 135px;" class="text-center">Amount</th>
                </tr>
            </thead>
            <tbody wire:key="quotation-form-tbody-{{ $rowsToShow }}-{{ $currentRowCount }}">
                @for($rowIndex = 0; $rowIndex < $rowsToShow; $rowIndex++)
                    @php
                        $continuation = $printLayout->continuationAt($rowIndex);
                        $itemIndex = $layoutBaseRows[$rowIndex] ?? null;
                        $item = $itemIndex !== null ? $stackedItems[$itemIndex] : null;
                        $isContinuationRow = $continuation !== null;
                        $isEmptyRow = ($itemIndex === null && ! $isContinuationRow);
                        $freeFormPreferredKey = $this->quotationFreeFormPreferredKeyAtDisplayRow($rowIndex);
                        $emptyRowAnchor = $this->quotationAnchorRowForDisplayRow($rowIndex);
                        $freeFormRowData = $freeFormPreferredKey !== null ? ($freeFormTextRows[$freeFormPreferredKey] ?? null) : null;
                        $freeFormQty = is_array($freeFormRowData) ? (float) ($freeFormRowData['qty'] ?? 0) : 0;
                        $freeFormPrice = is_array($freeFormRowData) ? (float) ($freeFormRowData['price'] ?? 0) : 0;
                        $freeFormAmount = $freeFormQty * $freeFormPrice;
                        $canMoveUp = $item && $rowIndex > 0 && ! $printLayout->isOccupiedRow($rowIndex - 1) && ! $this->quotationRowHasPendingFreeForm($rowIndex - 1);
                        $canMoveDown = $item && $rowIndex < ($rowsToShow - 1) && ! $printLayout->isOccupiedRow($rowIndex + 1) && ! $this->quotationRowHasPendingFreeForm($rowIndex + 1);
                    @endphp
                    <tr class="item-row quotation-grid-page-{{ $rowIndex >= $quotationPage2StartRow ? 2 : 1 }}{{ $rowIndex === $quotationPage2StartRow ? ' quotation-grid-page-break' : '' }}{{ $isContinuationRow ? ' quotation-continuation-row' : '' }}"
                        data-row-index="{{ $rowIndex }}"
                        data-print-page="{{ $rowIndex >= $quotationPage2StartRow ? 2 : 1 }}"
                        wire:key="quotation-form-row-{{ $rowIndex }}-{{ $itemIndex === null ? 'empty' : $itemIndex }}">
                        <td class="text-center text-muted do-row-number-cell" style="width: 30px; vertical-align: top; font-size: 0.65em;">
                            @if($rowIndex === $quotationPage2StartRow && !isset($rowSequenceMap[$rowIndex]))
                                <span class="quotation-page-zone-label">Pg 2</span>
                            @endif
                            @if($this->quotationRowShowsSequenceInput($rowIndex))
                                @if($isView)
                                    @if(isset($rowSequenceMap[$rowIndex]))
                                        {{ $rowSequenceMap[$rowIndex] }}
                                    @endif
                                @else
                                    <input type="number"
                                        min="1"
                                        max="{{ max(1, $sequenceTotal) }}"
                                        value="{{ $rowSequenceMap[$rowIndex] ?? '' }}"
                                        wire:change="setQuotationItemSequenceFromRow({{ $rowIndex }}, $event.target.value)"
                                        class="form-control form-control-sm text-center p-0 quotation-seq-input"
                                        inputmode="numeric"
                                        title="Change order, or clear to hide the line number"
                                        aria-label="Item sequence number">
                                @endif
                            @endif
                        </td>
                        <td class="do-qty-cell" style="width: 62px; vertical-align: top;">
                            @if($item)
                                @if((isset($item['is_text_only']) && $item['is_text_only']) || ($item['item']['id'] ?? null) === null)
                                    <input type="text"
                                        wire:model.lazy="stackedItems.{{ $itemIndex }}.item_qty"
                                        class="form-control form-control-sm"
                                        inputmode="decimal"
                                        autocomplete="off"
                                        {{ $isView ? 'disabled' : '' }}
                                        data-do-role="qty">
                                @else
                                    <input type="number"
                                        wire:model.lazy="stackedItems.{{ $itemIndex }}.item_qty"
                                        class="form-control form-control-sm @error('stackedItems.'.$itemIndex.'.item_qty') is-invalid @enderror"
                                        min="0.1" step="0.01" inputmode="decimal"
                                        wire:change="updatePriceLine({{ $itemIndex }})"
                                        {{ $isView ? 'disabled' : '' }}
                                        data-do-role="qty">
                                    @error('stackedItems.'.$itemIndex.'.item_qty')
                                        <div class="text-danger small text-end">!</div>
                                    @enderror
                                @endif
                            @elseif($isContinuationRow)
                                &nbsp;
                            @elseif(!$isView && $isEmptyRow)
                                <input type="text"
                                    wire:model.lazy="freeFormTextRows.{{ $emptyRowAnchor }}.qty"
                                    class="form-control form-control-sm"
                                    inputmode="decimal"
                                    autocomplete="off"
                                    data-do-role="qty"
                                    data-quotation-free-form-field="qty"
                                    data-quotation-free-form-anchor="{{ $emptyRowAnchor }}">
                            @elseif(!$isView)
                                <input type="text" class="form-control form-control-sm" placeholder="Qty" disabled
                                    style="width: 100%; background-color: #f8f9fa;">
                            @endif
                        </td>
                        <td style="width: 80px; vertical-align: top;">
                            @if($item)
                                <input type="text" wire:model="stackedItems.{{ $itemIndex }}.custom_um"
                                    class="form-control form-control-sm"
                                    data-do-role="uom"
                                    placeholder="{{ ($item['item']['um'] ?? 'UNIT') === 'UNIT' ? 'UNITS' : ($item['item']['um'] ?? 'UOM') }}"
                                    {{ $isView ? 'disabled' : '' }}
                                    style="max-width: 86px; padding: 0.15rem 0.25rem;">
                            @elseif($isContinuationRow)
                                &nbsp;
                            @elseif(!$isView && $isEmptyRow)
                                <input type="text" wire:model.lazy="freeFormTextRows.{{ $emptyRowAnchor }}.um"
                                    {{ $isView ? 'disabled' : '' }}
                                    class="form-control form-control-sm"
                                    data-do-role="uom"
                                    data-quotation-free-form-field="um"
                                    data-quotation-free-form-anchor="{{ $emptyRowAnchor }}"
                                    style="max-width: 86px; padding: 0.15rem 0.25rem;">
                            @endif
                        </td>
                        <td style="vertical-align: top; position: relative;">
                            @if($item)
                                @if(isset($item['is_text_only']) && $item['is_text_only'])
                                    <div class="d-flex gap-2 align-items-center" style="position: relative;">
                                        <div style="flex: 1;">
                                            @if(!$isView)
                                                <input type="text"
                                                    wire:model.lazy="stackedItems.{{ $itemIndex }}.custom_item_name"
                                                    class="form-control form-control-sm"
                                                    placeholder="Detail/text"
                                                    data-do-role="desc"
                                                    data-quotation-text-only-name="1"
                                                    data-quotation-stacked-index="{{ $itemIndex }}"
                                                    style="font-size: 0.85em; padding: 0.15rem 0.25rem;">
                                            @else
                                                <span class="do-item-name-text">{{ $item['custom_item_name'] ?? '' }}</span>
                                            @endif
                                        </div>
                                        @if(!$isView)
                                            <button type="button" class="btn btn-sm p-0 px-1 btn-outline-secondary flex-shrink-0" wire:click="moveItemUp({{ $itemIndex }})" {{ $canMoveUp ? '' : 'disabled' }} title="Move up" style="font-size: 0.7rem;">▲</button>
                                            <button type="button" class="btn btn-sm p-0 px-1 btn-outline-secondary flex-shrink-0" wire:click="moveItemDown({{ $itemIndex }})" {{ $canMoveDown ? '' : 'disabled' }} title="Move down" style="font-size: 0.7rem;">▼</button>
                                            <button type="button" class="btn btn-sm p-0 px-1 btn-danger flex-shrink-0" wire:click="removeItem({{ $itemIndex }})" title="Delete" style="font-size: 0.7rem;">×</button>
                                        @endif
                                    </div>
                                @else
                                    <div x-data="{
                                        showDescription: {{ !empty($stackedItems[$itemIndex]['more_description']) ? 'true' : 'false' }},
                                        editingName: false,
                                        displayName: @js($stackedItems[$itemIndex]['custom_item_name'] ?? $item['item']['item_name'])
                                    }"
                                    x-init="
                                        $watch('showDescription', value => {
                                            if (value) {
                                                $wire.call('validateDescriptionRowsOnShow', {{ $itemIndex }});
                                            }
                                        });
                                        $watch('editingName', value => {
                                            if (!value) {
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
                                                            <div x-show="showMemo" x-transition class="memo-tooltip" @click.stop>
                                                                <div class="memo-tooltip-body">{{ $item['item']['memo'] }}</div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </template>
                                                <template x-if="editingName">
                                                    <div class="d-flex gap-1 align-items-center">
                                                        <input type="text" x-ref="nameInput" class="form-control form-control-sm"
                                                            wire:model.defer="stackedItems.{{ $itemIndex }}.custom_item_name" {{ $isView ? 'disabled' : '' }}
                                                            placeholder="{{ $item['item']['item_name'] }}"
                                                            data-do-role="desc"
                                                            @keydown.enter.prevent="const newValue = $refs.nameInput.value || '{{ $item['item']['item_name'] }}'; $wire.set('stackedItems.{{ $itemIndex }}.custom_item_name', newValue); displayName = newValue; editingName = false"
                                                            @keydown.escape="editingName = false"
                                                            style="font-size: 0.85em;">
                                                        <button type="button" class="btn btn-sm btn-success p-1 px-2"
                                                            @click="const newValue = $refs.nameInput.value || '{{ $item['item']['item_name'] }}'; $wire.set('stackedItems.{{ $itemIndex }}.custom_item_name', newValue); displayName = newValue; editingName = false"
                                                            style="font-size: 0.7rem; line-height: 1;">✓</button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary p-1 px-2"
                                                            @click="$wire.set('stackedItems.{{ $itemIndex }}.custom_item_name', null); editingName = false"
                                                            style="font-size: 0.7rem; line-height: 1;"
                                                            title="Reset to original">↺</button>
                                                    </div>
                                                </template>
                                            </div>
                                            @if(!$isView)
                                                <button type="button" x-show="!editingName" class="btn btn-sm p-0 px-1 flex-shrink-0"
                                                    :class="showDescription ? 'btn-primary' : 'btn-outline-primary'"
                                                    @click="showDescription = !showDescription" style="font-size: 0.7rem;">
                                                    <span x-text="showDescription ? '- desc' : '+ desc'"></span>
                                                </button>
                                                <button type="button" x-show="!editingName" class="btn btn-sm p-0 px-1 btn-outline-secondary flex-shrink-0"
                                                    @click="editingName = true; $nextTick(() => $refs.nameInput?.focus())" style="font-size: 0.7rem;">Edit</button>
                                                <button type="button" class="btn btn-sm p-0 px-1 btn-outline-secondary flex-shrink-0" wire:click="moveItemUp({{ $itemIndex }})" {{ $canMoveUp ? '' : 'disabled' }} style="font-size: 0.7rem;">▲</button>
                                                <button type="button" class="btn btn-sm p-0 px-1 btn-outline-secondary flex-shrink-0" wire:click="moveItemDown({{ $itemIndex }})" {{ $canMoveDown ? '' : 'disabled' }} style="font-size: 0.7rem;">▼</button>
                                                <button type="button" class="btn btn-sm p-0 px-1 btn-danger flex-shrink-0" wire:click="removeItem({{ $itemIndex }})" style="font-size: 0.7rem;">×</button>
                                            @endif
                                        </div>
                                        @if(!$isView)
                                            <div x-show="showDescription" class="mt-1 mb-1 p-1" style="background-color: #f8f9fa; border-radius: 4px; border: 1px solid #dee2e6;">
                                                <textarea wire:model.defer="stackedItems.{{ $itemIndex }}.more_description"
                                                    class="form-control form-control-sm" rows="1"
                                                    placeholder="Enter additional description"
                                                    data-quotation-item-description="1"
                                                    data-quotation-stacked-index="{{ $itemIndex }}"
                                                    style="font-size: 0.78em; resize: vertical; min-height: 28px; padding: 0.15rem 0.3rem; line-height: 1.15;"></textarea>
                                                <div class="d-flex justify-content-between align-items-center mt-1">
                                                    <small class="text-muted" style="font-size: 0.7em;">
                                                        Formula 1+N rows. Max {{ $maxPrintRows }} rows total.
                                                    </small>
                                                    <button type="button"
                                                        wire:click="saveDescriptionAndValidate({{ $itemIndex }})"
                                                        class="btn btn-sm btn-primary"
                                                        style="font-size: 0.7em; padding: 2px 9px;">
                                                        Save
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @elseif($isContinuationRow)
                                @if($continuation['kind'] === 'desc_line')
                                    <div class="ms-0 text-muted quotation-continuation-text" style="font-size: 0.85em; padding-left: 15px;">
                                        • {{ $continuation['text'] }}
                                    </div>
                                @else
                                    &nbsp;
                                @endif
                            @elseif($isContinuationRow)
                                &nbsp;
                            @elseif(!$isView && $isEmptyRow)
                                <div class="d-flex gap-2 align-items-center" style="position: relative; width: 100%;">
                                    <input type="text"
                                        wire:model.lazy="freeFormTextRows.{{ $emptyRowAnchor }}.text"
                                        class="form-control form-control-sm flex-grow-1"
                                        placeholder="Type anything here"
                                        style="font-size: 0.85em;"
                                        data-do-role="desc"
                                        data-quotation-free-form-field="text"
                                        data-quotation-free-form-anchor="{{ $emptyRowAnchor }}">
                                    <button type="button"
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
                            @if($item && !(isset($item['is_text_only']) && $item['is_text_only']) && (($item['item']['id'] ?? null) !== null))
                                @php
                                    $price = $stackedItems[$itemIndex]['item_unit_price'] ?? 0;
                                    $tier = $stackedItems[$itemIndex]['pricing_tier'] ?? '';
                                    $cashPrice = (float) ($item['item']['cash_price'] ?? 0);
                                    $termPrice = (float) ($item['item']['term_price'] ?? 0);
                                    $customerPrice = (float) ($item['item']['cust_price'] ?? 0);
                                    $costPrice = (float) ($item['item']['cost'] ?? 0);
                                    $previousPrice = (float) ($item['item']['latest_quote_price'] ?? 0);
                                @endphp
                                <div class="d-flex align-items-center gap-1 do-price-row">
                                    <select wire:model.live="stackedItems.{{ $itemIndex }}.pricing_tier"
                                            wire:change="selectPricingTier({{ $itemIndex }}, $event.target.value)"
                                            class="form-select form-select-sm do-price-tier-select" {{ $isView ? 'disabled' : '' }}
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
                                        <input type="text" inputmode="decimal"
                                            wire:model.lazy="stackedItems.{{ $itemIndex }}.item_unit_price"
                                            wire:change="updateUnitPrice({{ $itemIndex }})"
                                            class="form-control form-control-sm" {{ $isView ? 'disabled' : '' }}
                                            placeholder="0.00"
                                            data-do-role="price"
                                            style="width: 78px; font-size: 0.76em; text-align: right; flex-shrink: 0;">
                                    @else
                                        <span class="fw-bold form-control form-control-sm d-inline-block"
                                            style="width: 78px; font-size: 0.76em; text-align: right; background-color: #f8f9fa; border: 1px solid #ced4da; border-radius: 0.25rem; padding: 0.12rem 0.25rem; line-height: 1.15; flex-shrink: 0;">
                                            {{ number_format($price, 2) }}
                                        </span>
                                    @endif
                                </div>
                            @elseif($item && ((isset($item['is_text_only']) && $item['is_text_only']) || (($item['item']['id'] ?? null) === null)))
                                <input type="text" inputmode="decimal"
                                    wire:model.lazy="stackedItems.{{ $itemIndex }}.item_unit_price"
                                    wire:change="updateUnitPrice({{ $itemIndex }})"
                                    class="form-control form-control-sm" {{ $isView ? 'disabled' : '' }}
                                    placeholder="0.00"
                                    data-do-role="price"
                                    style="width: 78px; font-size: 0.76em; text-align: right; margin-left: auto; display: block;">
                            @elseif($isContinuationRow)
                                &nbsp;
                            @elseif(!$isView && $isEmptyRow)
                                <input type="text" inputmode="decimal"
                                    wire:model.lazy="freeFormTextRows.{{ $emptyRowAnchor }}.price"
                                    class="form-control form-control-sm"
                                    placeholder="0.00"
                                    data-do-role="price"
                                    data-quotation-free-form-field="price"
                                    data-quotation-free-form-anchor="{{ $emptyRowAnchor }}"
                                    style="width: 78px; font-size: 0.76em; text-align: right; margin-left: auto; display: block;">
                            @endif
                        </td>
                        <td class="text-end" style="vertical-align: top;">
                            @if($item)
                                <span class="fw-bold do-amount-cell" style="font-size: 0.8em; color: #0d6efd; white-space: nowrap;">
                                    {{ number_format($stackedItems[$itemIndex]['amount'] ?? 0, 2) }}
                                </span>
                            @elseif($isContinuationRow)
                                &nbsp;
                            @elseif(!$isView && $isEmptyRow && ($freeFormQty > 0 || $freeFormPrice > 0))
                                <span class="fw-bold do-amount-cell" style="font-size: 0.8em; color: #0d6efd; white-space: nowrap;">
                                    {{ number_format($freeFormAmount, 2) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>

<style>
    .quotation-items-table-shell.do-table-shell {
        /* ~29px per row matches rendered tbody height (34px estimate showed ~24.5 rows). */
        --quotation-grid-row-h: 29.15px;
        --quotation-grid-thead-h: 2.35rem;
        max-height: calc(var(--quotation-grid-thead-h) + {{ $viewportRows }} * var(--quotation-grid-row-h));
        overflow-y: auto;
        overflow-x: hidden;
    }

    .quotation-seq-input {
        width: 1.75rem;
        min-width: 1.75rem;
        max-width: 1.75rem;
        height: 1.35rem;
        font-size: 0.65em;
        font-weight: 600;
        line-height: 1;
        border-color: #ced4da;
        background: #fff;
        -moz-appearance: textfield;
    }

    .quotation-seq-input::-webkit-outer-spin-button,
    .quotation-seq-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .quotation-items-table-shell tr.quotation-grid-page-2 td {
        background-color: #f3f6fb;
    }

    .quotation-items-table-shell tr.quotation-grid-page-break td {
        border-top: 2px solid #9db3d1 !important;
    }

    .quotation-page-zone-label {
        display: block;
        font-size: 0.58em;
        font-weight: 700;
        color: #5c6f8a;
        line-height: 1.1;
        margin-bottom: 1px;
        white-space: nowrap;
    }

    .quotation-items-table-shell tr.quotation-continuation-row td {
        background-color: #fafbfd;
    }

    .quotation-items-table-shell tr.quotation-continuation-row .quotation-continuation-text {
        margin-top: 0;
        margin-bottom: 0;
    }
</style>
