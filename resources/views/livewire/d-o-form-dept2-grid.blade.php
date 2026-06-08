{{-- Department 2 line grid: Index, Code, Description, Qty, Unit, Price, Amount --}}
<div class="do-table-shell">
    <p class="small text-muted mb-2">Type an item code and press <strong>Enter</strong> to load the line. You can also type a description without a code for manual lines.</p>
    <table class="table table-bordered do-fixed-table do-dept2-grid">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">#</th>
                <th style="width: 100px;" class="text-start do-dept2-col-code">Code</th>
                <th>Description</th>
                <th style="width: 72px;" class="text-end">Qty</th>
                <th style="width: 80px;">Unit</th>
                <th style="width: 90px;" class="text-end">Price</th>
                <th style="width: 100px;" class="text-end">Amount</th>
                <th style="width: 28px;"></th>
            </tr>
        </thead>
        @php
            $rowToItemMap = [];
            $regularItemIndex = 0;
            foreach ($stackedItems as $idx => $item) {
                if (isset($item['original_row_index']) && $item['original_row_index'] !== null) {
                    $originalRow = $item['original_row_index'];
                    if ($originalRow < 24) {
                        $rowToItemMap[$originalRow] = $idx;
                    } else {
                        while (isset($rowToItemMap[$regularItemIndex]) && $regularItemIndex < 24) {
                            $regularItemIndex++;
                        }
                        if ($regularItemIndex < 24) {
                            $rowToItemMap[$regularItemIndex] = $idx;
                            $regularItemIndex++;
                        }
                    }
                } else {
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
        <tbody wire:key="do-form-tbody-dept2-{{ count($stackedItems) }}-{{ $rowsToShow }}">
            @for($rowIndex = 0; $rowIndex < $rowsToShow; $rowIndex++)
                @php
                    $itemIndex = $rowToItemMap[$rowIndex] ?? null;
                    $item = $itemIndex !== null ? $stackedItems[$itemIndex] : null;
                    $isEmptyRow = ($itemIndex === null);
                    $freeFormRowData = $freeFormTextRows[$rowIndex] ?? null;
                    $freeFormQty = is_array($freeFormRowData) ? (float) ($freeFormRowData['qty'] ?? 0) : 0;
                    $freeFormPrice = is_array($freeFormRowData) ? (float) ($freeFormRowData['price'] ?? 0) : 0;
                    $freeFormAmount = $freeFormQty * $freeFormPrice;
                    $isInventoryLine = $item
                        && empty($item['is_text_only'])
                        && empty($item['is_choice'])
                        && (($item['item']['id'] ?? null) !== null);
                    $isTextOnlyLine = $item && !empty($item['is_text_only']);
                @endphp
                <tr class="item-row" data-row-index="{{ $rowIndex }}" wire:key="do-form-dept2-row-{{ $rowIndex }}-{{ $itemIndex === null ? 'empty' : $itemIndex }}">
                    <td class="text-center text-muted do-row-number-cell" style="vertical-align: top; font-size: 0.65em;">
                        {{ $rowIndex + 1 }}
                    </td>
                    <td style="vertical-align: top;">
                        @if($isInventoryLine)
                            <span class="small fw-semibold text-nowrap">{{ $item['item']['item_code'] ?? '' }}</span>
                        @elseif($isTextOnlyLine)
                            <span class="small text-muted">—</span>
                        @elseif(!$isView && $isEmptyRow)
                            <input type="text"
                                wire:keydown.enter.prevent="addItemByCodeAtRow({{ $rowIndex }}, $event.target.value)"
                                wire:loading.attr="disabled"
                                wire:target="addItemByCodeAtRow"
                                class="form-control form-control-sm"
                                placeholder="Code"
                                autocomplete="off"
                                {{ ($isView || $isPosted) ? 'disabled' : '' }}
                                data-do-role="code">
                        @endif
                    </td>
                    <td style="vertical-align: top;">
                        @if($item)
                            <div class="d-flex gap-1 align-items-start">
                                @if($isTextOnlyLine)
                                    <input type="text"
                                        wire:model.defer="stackedItems.{{ $itemIndex }}.custom_item_name"
                                        class="form-control form-control-sm flex-grow-1"
                                        placeholder="Description"
                                        {{ ($isView || $isPosted) ? 'disabled' : '' }}
                                        data-do-role="desc">
                                @else
                                    <input type="text"
                                        wire:model.defer="stackedItems.{{ $itemIndex }}.custom_item_name"
                                        class="form-control form-control-sm flex-grow-1"
                                        placeholder="{{ $item['item']['item_name'] ?? 'Description' }}"
                                        {{ ($isView || $isPosted) ? 'disabled' : '' }}
                                        data-do-role="desc">
                                @endif
                            </div>
                        @elseif(!$isView && $isEmptyRow)
                            <input type="text"
                                wire:model.live.debounce.400ms="freeFormTextRows.{{ $rowIndex }}.text"
                                class="form-control form-control-sm"
                                placeholder="Description (optional)"
                                {{ ($isView || $isPosted) ? 'disabled' : '' }}
                                data-do-role="desc">
                        @endif
                    </td>
                    <td class="do-qty-cell text-end" style="vertical-align: top;">
                        @if($item)
                            @if($isTextOnlyLine)
                                <input type="text"
                                    wire:model.lazy="stackedItems.{{ $itemIndex }}.item_qty"
                                    wire:change="updatePriceLine({{ $itemIndex }})"
                                    class="form-control form-control-sm text-end"
                                    inputmode="decimal"
                                    {{ ($isView || $isPosted) ? 'disabled' : '' }}
                                    data-do-role="qty">
                            @else
                                <input type="number"
                                    wire:model.lazy="stackedItems.{{ $itemIndex }}.item_qty"
                                    wire:change="updatePriceLine({{ $itemIndex }})"
                                    class="form-control form-control-sm text-end"
                                    min="0.1" step="0.01" inputmode="decimal"
                                    {{ ($isView || $isPosted) ? 'disabled' : '' }}
                                    data-do-role="qty">
                            @endif
                        @elseif(!$isView && $isEmptyRow)
                            <input type="text"
                                wire:model.lazy="freeFormTextRows.{{ $rowIndex }}.qty"
                                class="form-control form-control-sm text-end"
                                inputmode="decimal"
                                {{ ($isView || $isPosted) ? 'disabled' : '' }}
                                data-do-role="qty">
                        @endif
                    </td>
                    <td style="vertical-align: top;">
                        @if($item)
                            <input type="text"
                                wire:model.defer="stackedItems.{{ $itemIndex }}.custom_um"
                                class="form-control form-control-sm"
                                {{ ($isView || $isPosted) ? 'disabled' : '' }}
                                data-do-role="uom">
                        @elseif(!$isView && $isEmptyRow)
                            <input type="text"
                                wire:model.lazy="freeFormTextRows.{{ $rowIndex }}.um"
                                class="form-control form-control-sm"
                                {{ ($isView || $isPosted) ? 'disabled' : '' }}
                                data-do-role="uom">
                        @endif
                    </td>
                    <td class="text-end" style="vertical-align: top;">
                        @if($item)
                            <input type="text"
                                inputmode="decimal"
                                wire:model.lazy="stackedItems.{{ $itemIndex }}.item_unit_price"
                                wire:change="updateUnitPrice({{ $itemIndex }})"
                                class="form-control form-control-sm text-end"
                                {{ ($isView || $isPosted) ? 'disabled' : '' }}
                                data-do-role="price">
                        @elseif(!$isView && $isEmptyRow)
                            <input type="text"
                                inputmode="decimal"
                                wire:model.lazy="freeFormTextRows.{{ $rowIndex }}.price"
                                class="form-control form-control-sm text-end"
                                {{ ($isView || $isPosted) ? 'disabled' : '' }}
                                data-do-role="price">
                        @endif
                    </td>
                    <td class="text-end" style="vertical-align: top;">
                        @if($item)
                            <span class="fw-bold do-amount-cell small text-nowrap">
                                {{ number_format($stackedItems[$itemIndex]['amount'] ?? 0, 2) }}
                            </span>
                        @elseif(!$isView && $isEmptyRow && ($freeFormQty > 0 || $freeFormPrice > 0))
                            <span class="fw-bold do-amount-cell small text-nowrap">
                                {{ number_format($freeFormAmount, 2) }}
                            </span>
                        @endif
                    </td>
                    <td class="text-center" style="vertical-align: top;">
                        @if($item && !$isView && !$isPosted)
                            <button type="button"
                                class="btn btn-sm p-0 px-1 btn-danger"
                                wire:click="removeItem({{ $itemIndex }})"
                                title="Remove line"
                                style="font-size: 0.7rem;">×</button>
                        @endif
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>
