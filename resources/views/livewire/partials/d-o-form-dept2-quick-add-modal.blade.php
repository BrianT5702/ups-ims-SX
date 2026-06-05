{{-- Dept 2 quick-add item modal (rendered on parent DOForm, outside the DO <form>) --}}
@if($showDept2QuickAddModal)
<div class="modal fade show dept2-quick-add-modal" tabindex="-1" style="display: block; position: fixed; inset: 0; z-index: 1060;" aria-modal="true" role="dialog">
    <div class="modal-backdrop fade show dept2-quick-add-backdrop" wire:click="closeDept2QuickAddModal"></div>
    <div class="modal-dialog modal-dialog-centered dept2-quick-add-dialog">
        <div class="modal-content shadow-lg border-0 dept2-quick-add-content" wire:click.stop wire:keydown.escape.window="closeDept2QuickAddModal">
            @if($dept2QuickAddModalStep === 'confirm')
                <div class="modal-header border-0 pb-0 pt-3 px-4">
                    <div class="d-flex align-items-start gap-3 w-100">
                        <div class="dept2-quick-add-icon rounded-circle flex-shrink-0">
                            <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                        </div>
                        <div class="flex-grow-1 pe-2">
                            <h5 class="modal-title mb-1 fw-semibold">Item not found</h5>
                            <p class="text-muted small mb-0">This code is not in your inventory yet.</p>
                        </div>
                        <button type="button" class="btn-close mt-1" wire:click="closeDept2QuickAddModal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body px-4 pt-3 pb-2">
                    <div class="dept2-quick-add-code-panel rounded-3 p-3 mb-3">
                        <div class="small text-muted text-uppercase fw-semibold mb-1" style="letter-spacing: 0.04em;">Entered code</div>
                        <div class="dept2-quick-add-code-value font-monospace fw-semibold">{{ $dept2QuickAddItemCode }}</div>
                    </div>
                    <p class="small text-muted mb-0">
                        Would you like to create a new item with this code and add it to row <strong>{{ ($dept2PendingQuickAddRowIndex ?? 0) + 1 }}</strong>?
                    </p>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                    <button type="button" class="btn btn-light btn-sm px-3" wire:click="closeDept2QuickAddModal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm px-3" wire:click="proceedDept2QuickAddForm">
                        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add new item
                    </button>
                </div>
            @else
                <div class="modal-header border-0 pb-0 pt-3 px-4">
                    <div class="d-flex align-items-start gap-3 w-100">
                        <div class="dept2-quick-add-icon dept2-quick-add-icon--form rounded-circle flex-shrink-0">
                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                        </div>
                        <div class="flex-grow-1 pe-2">
                            <h5 class="modal-title mb-1 fw-semibold">Add new item</h5>
                            <p class="text-muted small mb-0">Create inventory for code <span class="font-monospace fw-semibold text-dark">{{ $dept2QuickAddItemCode }}</span></p>
                        </div>
                        <button type="button" class="btn-close mt-1" wire:click="closeDept2QuickAddModal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body px-4 pt-3 pb-2 dept2-quick-add-form-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label for="dept2-quick-item-code" class="form-label small fw-semibold mb-1">Item code</label>
                            <input type="text"
                                id="dept2-quick-item-code"
                                class="form-control form-control-sm bg-light font-monospace"
                                wire:model="dept2QuickAddItemCode"
                                readonly>
                            @error('dept2QuickAddItemCode') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-8">
                            <label for="dept2-quick-item-name" class="form-label small fw-semibold mb-1">Item name <span class="text-danger">*</span></label>
                            <input type="text"
                                id="dept2-quick-item-name"
                                class="form-control form-control-sm"
                                wire:model="dept2QuickAddItemName"
                                placeholder="Enter item description"
                                autocomplete="off">
                            @error('dept2QuickAddItemName') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="dept2-quick-item-um" class="form-label small fw-semibold mb-1">Unit <span class="text-danger">*</span></label>
                            <input type="text"
                                id="dept2-quick-item-um"
                                class="form-control form-control-sm"
                                wire:model="dept2QuickAddUm"
                                placeholder="e.g. UNIT, PCS"
                                autocomplete="off">
                            @error('dept2QuickAddUm') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="small text-muted text-uppercase fw-semibold mt-3 mb-2" style="letter-spacing: 0.04em;">Pricing</div>
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <label for="dept2-quick-item-cost" class="form-label small fw-semibold mb-1">Cost</label>
                            <input type="number" step="0.01" min="0" id="dept2-quick-item-cost" class="form-control form-control-sm" wire:model="dept2QuickAddCost">
                            @error('dept2QuickAddCost') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="dept2-quick-item-cash" class="form-label small fw-semibold mb-1">Cash</label>
                            <input type="number" step="0.01" min="0" id="dept2-quick-item-cash" class="form-control form-control-sm" wire:model="dept2QuickAddCashPrice">
                            @error('dept2QuickAddCashPrice') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="dept2-quick-item-term" class="form-label small fw-semibold mb-1">Term</label>
                            <input type="number" step="0.01" min="0" id="dept2-quick-item-term" class="form-control form-control-sm" wire:model="dept2QuickAddTermPrice">
                            @error('dept2QuickAddTermPrice') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="dept2-quick-item-cust" class="form-label small fw-semibold mb-1">Cust.</label>
                            <input type="number" step="0.01" min="0" id="dept2-quick-item-cust" class="form-control form-control-sm" wire:model="dept2QuickAddCustPrice">
                            @error('dept2QuickAddCustPrice') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row g-2 mt-1">
                        <div class="col-md-6">
                            <label for="dept2-quick-item-memo" class="form-label small fw-semibold mb-1">Memo</label>
                            <textarea id="dept2-quick-item-memo" class="form-control form-control-sm" rows="2" wire:model="dept2QuickAddMemo" placeholder="Memo / special handling"></textarea>
                            @error('dept2QuickAddMemo') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="dept2-quick-item-details" class="form-label small fw-semibold mb-1">Details</label>
                            <textarea id="dept2-quick-item-details" class="form-control form-control-sm" rows="2" wire:model="dept2QuickAddDetails" placeholder="Shown on DO"></textarea>
                            @error('dept2QuickAddDetails') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                    <button type="button" class="btn btn-light btn-sm px-3" wire:click="closeDept2QuickAddModal">Cancel</button>
                    <button type="button"
                        class="btn btn-primary btn-sm px-3"
                        wire:click="saveDept2QuickAddItem"
                        wire:loading.attr="disabled"
                        wire:target="saveDept2QuickAddItem">
                        <span wire:loading wire:target="saveDept2QuickAddItem" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Save &amp; add line
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
<style>
    .dept2-quick-add-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1060;
        background-color: rgba(15, 23, 42, 0.45);
    }
    .dept2-quick-add-dialog {
        position: relative;
        z-index: 1065;
        max-width: 560px;
    }
    .dept2-quick-add-form-body {
        max-height: min(70vh, 520px);
        overflow-y: auto;
    }
    .dept2-quick-add-content {
        border-radius: 0.85rem;
        overflow: hidden;
    }
    .dept2-quick-add-icon {
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff4e6;
        color: #c2410c;
        font-size: 1rem;
    }
    .dept2-quick-add-icon--form {
        background: #eff6ff;
        color: #1d4ed8;
    }
    .dept2-quick-add-code-panel {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    .dept2-quick-add-code-value {
        font-size: 1.05rem;
        color: #0f172a;
        word-break: break-all;
    }
</style>
@endif
