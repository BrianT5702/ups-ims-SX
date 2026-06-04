<div>
    <div class="modal fade show do-item-picker-modal {{ $showModal ? '' : 'd-none' }}" tabindex="-1" style="display:block;" aria-modal="true" role="dialog">
        <div class="modal-backdrop fade show" style="z-index: 1040;" wire:click="close"></div>
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="z-index: 1045;">
            <div class="modal-content" wire:click.stop>
                <div class="modal-header py-2">
                    <h5 class="modal-title mb-0">Add item - row {{ ($rowIndex ?? 0) + 1 }}</h5>
                    <button type="button" class="btn-close" wire:click="close" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <label class="form-label small text-muted mb-0">
                            Search by {{ $searchMode === 'code' ? 'item code' : 'item name' }}
                        </label>
                        <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="close">
                            Cancel
                        </button>
                    </div>
                    <input type="text"
                        class="form-control form-control-sm mb-2"
                        wire:model.live.debounce.0ms="searchTerm"
                        placeholder="{{ $searchMode === 'code' ? 'Type item code...' : 'Type item name...' }}"
                        autocomplete="off"
                        id="do-item-picker-search">
                    <p class="small text-muted mb-2">
                        Showing up to {{ trim($searchTerm) === '' ? '0' : '120' }} matches.
                    </p>

                    <div class="table-responsive border rounded do-item-picker-table-wrap" style="max-height: min(55vh, 480px); overflow: auto;">
                        <table class="table table-sm table-bordered table-hover table-striped mb-0 do-item-picker-table">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th scope="col" style="width: 11%;">Stock Code</th>
                                    <th scope="col">Stock Description</th>
                                    <th scope="col" class="text-end" style="width: 9%;">On Hand</th>
                                    <th scope="col" class="text-center" style="width: 8%;">U.O.M</th>
                                    <th scope="col" class="text-end" style="width: 10%;">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($results as $result)
                                    @php
                                        $pickerUm = ($result->um ?? 'UNIT') === 'UNIT' ? 'UNITS' : ($result->um ?? 'UNITS');
                                        $pickerPrice = (float) ($result->cash_price ?? 0);
                                        $rawPickerDesc = (string) ($result->item_name ?? '');
                                        $pickerDesc = preg_replace('/^[\s@*#~^$]+/u', '', $rawPickerDesc);
                                        $pickerDesc = ltrim((string) $pickerDesc);
                                        if ($pickerDesc === '') {
                                            $pickerDesc = $rawPickerDesc;
                                        }
                                    @endphp
                                    <tr class="do-item-picker-row"
                                        data-item-id="{{ $result->id }}"
                                        wire:key="do-fast-picker-item-{{ $result->id }}"
                                        wire:click="selectItem({{ $result->id }})"
                                        role="button"
                                        tabindex="0"
                                        style="cursor: pointer;">
                                        <td class="small fw-semibold text-nowrap">{{ $result->item_code }}</td>
                                        <td class="small">{{ $pickerDesc }}</td>
                                        <td class="small text-end font-monospace @if($result->qty < 0) text-danger @elseif((float) $result->qty == 0.0) text-warning @endif">
                                            {{ number_format((float) $result->qty, 2) }}
                                        </td>
                                        <td class="small text-center text-nowrap">{{ $pickerUm }}</td>
                                        <td class="small text-end font-monospace">{{ number_format($pickerPrice, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted small py-3 px-3">
                                            {{ trim($searchTerm) === '' ? 'Type at least 1 character to search items.' : 'No items found. Try a different search.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

