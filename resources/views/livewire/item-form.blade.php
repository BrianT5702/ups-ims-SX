<div class="container my-3 item-form-page" @if($browseNavEnabled) data-item-browse-nav="1" @endif>
    <div class="row">
        <div class="col-12 col-xxl-11 m-auto item-form-shell">
            <div class="card shadow-sm" style="overflow: visible;">
                <div class="card-header">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h5 class="fw-bold fs-5 mb-0">{{ $isView ? 'View' : ($item ? 'Edit' : 'Add') }} Item</h5>
                            @if($browseNavEnabled)
                                <span id="item-browse-position" class="badge text-bg-light border text-muted fw-normal item-form-browse-position">
                                    {{ $browsePosition }}
                                </span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 item-form-header-actions">
                            @if($browseNavEnabled)
                                <div class="btn-group btn-group-sm item-form-browse-nav" role="group" aria-label="Browse inventory list">
                                    <button type="button"
                                            class="btn btn-outline-secondary"
                                            data-item-browse="prev"
                                            @disabled(! $browseHasPrev)
                                            title="Previous item (↑)">
                                        <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
                                        <span class="visually-hidden">Previous item</span>
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-secondary"
                                            data-item-browse="next"
                                            @disabled(! $browseHasNext)
                                            title="Next item (↓)">
                                        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                                        <span class="visually-hidden">Next item</span>
                                    </button>
                                </div>
                            @endif
                            @if(!$isView)
                                <button type="submit"
                                    form="item-form-main"
                                    class="btn btn-primary btn-sm"
                                    wire:loading.attr="disabled"
                                    wire:target="image"
                                    {{ $isImageUploading ? 'disabled' : '' }}>
                                    <span wire:loading wire:target="image" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ $item ? 'Update' : 'Add' }}
                                </button>
                                @if($item)
                                    <button wire:confirm="Are you sure you want to delete this item? This will also delete all related batch tracking, purchase order, delivery order, and transaction records!" wire:click="deleteItem({{ $item->id }})" type="button" class="btn btn-danger btn-sm">Delete</button>
                                @endif
                            @endif
                            @can('View Transaction Log')
                                @if($item)
                                    <a id="item-browse-txlog" href="{{ route('transaction-log.show', $item->id) }}" wire:navigate class="btn btn-outline-primary btn-sm" title="Transaction log for this item">
                                        <i class="fa-solid fa-clock-rotate-left me-1"></i>Transaction log
                                    </a>
                                @endif
                            @endcan
                            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">Back</a>
                        </div>
                    </div>
                </div>
                <div class="card-body item-form-body">
                    <form id="item-form-main" wire:submit.prevent="addItem" class="item-form-grid">
                        <div class="row g-1 g-lg-2">
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group mb-1">
                                    <label for="item_code" class="form-label">Item Code <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live="item_code" id="item_code" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                    @error('item_code') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-lg-9 col-md-6">
                                <div class="form-group mb-1">
                                    <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live="item_name" id="item_name" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                    @error('item_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        @if($this->isDepartment2)
                        <div class="row g-1 g-lg-2">
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group mb-1">
                                    <label for="um" class="form-label">Unit <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live="um" id="um" class="form-control form-control-sm rounded" placeholder="e.g. PCS, UNIT" {{ $isView ? 'disabled' : '' }}>
                                    @error('um') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="item-form-section-title">Pricing</div>
                        <div class="row g-1 g-lg-2">
                            <div class="col-lg-3 col-6">
                                <div class="form-group mb-1">
                                    <label for="cost" class="form-label">Cost <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model.live="cost" min="0" id="cost" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                    @error('cost') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="form-group mb-1">
                                    <label for="cash_price" class="form-label">Cash <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model.live="cash_price" min="0" id="cash_price" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                    @error('cash_price') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="form-group mb-1">
                                    <label for="term_price" class="form-label">Term <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model.live="term_price" min="0" id="term_price" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                    @error('term_price') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="form-group mb-1">
                                    <label for="cust_price" class="form-label">Cust. <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model.live="cust_price" min="0" id="cust_price" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                    @error('cust_price') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="item-form-section-title">Memo &amp; Details</div>
                        <div class="row g-1 g-lg-2">
                            <div class="col-lg-6">
                                <div class="form-group mb-1">
                                    <label for="memo" class="form-label">Memo</label>
                                    <textarea id="memo" wire:model.defer="memo" class="form-control form-control-sm rounded item-form-textarea" rows="4" placeholder="Memo / special handling" style="font-family: inherit; font-size: inherit;" {{ $isView ? 'disabled' : '' }}></textarea>
                                    @error('memo') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group mb-1">
                                    <label for="details" class="form-label">Details</label>
                                    <textarea id="details" wire:model.defer="details" class="form-control form-control-sm rounded item-form-textarea" rows="4" placeholder="Shown on DO" style="font-family: inherit; font-size: inherit;" {{ $isView ? 'disabled' : '' }}></textarea>
                                    @error('details') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="row g-2 item-form-groups-row">
                            <div class="col-lg-3 col-md-6">
                                <div class="item-form-group-panel h-100">
                                    <div class="item-form-section-title item-form-section-title--panel">Inventory</div>
                                    <div class="item-form-group-fields">
                                        <div class="form-group mb-1">
                                            <label for="qty" class="form-label">Stock</label>
                                            <input type="number"
                                                step="any"
                                                wire:model.live="qty"
                                                id="qty"
                                                class="form-control form-control-sm rounded"
                                                @disabled($isView || ! ($canEditQtyForUpsDervet ?? false))>
                                            @error('qty') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="form-group mb-1">
                                            <label for="um" class="form-label">Unit <span class="text-danger">*</span></label>
                                            <input type="text" wire:model.live="um" id="um" class="form-control form-control-sm rounded" placeholder="e.g. PCS, BOX" {{ $isView ? 'disabled' : '' }}>
                                            @error('um') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="form-group mb-1">
                                            <label for="stock_alert_level" class="form-label">Min Stock</label>
                                            <input type="number" wire:model.live="stock_alert_level" min="0" id="stock_alert_level" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                            @error('stock_alert_level') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="item-form-group-panel h-100">
                                    <div class="item-form-section-title item-form-section-title--panel">Pricing</div>
                                    <div class="item-form-group-fields">
                                        <div class="form-group mb-1">
                                            <label for="cash_price" class="form-label">Cash <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" wire:model.live="cash_price" min="0" id="cash_price" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                            @error('cash_price') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="form-group mb-1">
                                            <label for="term_price" class="form-label">Term <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" wire:model.live="term_price" min="0" id="term_price" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                            @error('term_price') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="form-group mb-1">
                                            <label for="cust_price" class="form-label">Customer <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" wire:model.live="cust_price" min="0" id="cust_price" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                            @error('cust_price') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="form-group mb-1">
                                            <label for="cost" class="form-label">Cost <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" wire:model.live="cost" min="0" id="cost" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                            @error('cost') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="item-form-group-panel h-100">
                                    <div class="item-form-section-title item-form-section-title--panel">Classification</div>
                                    <div class="item-form-group-fields">
                                        <div class="form-group mb-1">
                                            <label for="family" class="form-label">Family</label>
                                            <select wire:model.live="family" id="family" class="form-select form-select-sm" {{ $isView ? 'disabled' : '' }}>
                                                <option value="">— None —</option>
                                                @foreach($families as $family)
                                                    <option value="{{ $family->id }}">{{ $family->family_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('family') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="form-group mb-1">
                                            <label for="group" class="form-label">Group</label>
                                            <select wire:model.live="group" id="group" class="form-select form-select-sm" {{ $isView ? 'disabled' : '' }}>
                                                <option value="">— None —</option>
                                                @foreach($groups as $group)
                                                    <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('group') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="form-group mb-1">
                                            <label for="category" class="form-label">Category</label>
                                            <select wire:model.live="category" id="category" class="form-select form-select-sm" {{ $isView ? 'disabled' : '' }}>
                                                <option value="">— None —</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->cat_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('category') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="item-form-group-panel h-100">
                                    <div class="item-form-section-title item-form-section-title--panel">Vendor &amp; Location</div>
                                    <div class="item-form-group-fields">
                                        <div class="form-group mb-1">
                                            <label for="supplier" class="form-label">Supplier</label>
                                            <select wire:model.live="supplier" id="supplier" class="form-select form-select-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                                <option value="">— None —</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}">{{ $supplier->sup_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('supplier') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="form-group mb-1">
                                            <label for="warehouse" class="form-label">Warehouse</label>
                                            <select wire:model.live="warehouse" id="warehouse" class="form-select form-select-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                                <option value="">— None —</option>
                                                @foreach($warehouses as $wh)
                                                    <option value="{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('warehouse') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="form-group mb-1">
                                            <label for="location" class="form-label">Location</label>
                                            <select wire:model.live="location" id="location" class="form-select form-select-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                                <option value="">— None —</option>
                                                @foreach($locations as $location)
                                                    <option value="{{ $location->id }}">{{ $location->location_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('location') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-1 g-lg-2 mt-1">
                            <div class="col-lg-6">
                                <div class="form-group mb-1">
                                    <label for="memo" class="form-label">Memo</label>
                                    <textarea id="memo" wire:model.defer="memo" class="form-control form-control-sm rounded item-form-textarea" rows="2" placeholder="Memo / special handling" style="font-family: inherit; font-size: inherit;" {{ $isView ? 'disabled' : '' }}></textarea>
                                    @error('memo') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group mb-1">
                                    <label for="details" class="form-label">Details</label>
                                    <textarea id="details" wire:model.defer="details" class="form-control form-control-sm rounded item-form-textarea" rows="2" placeholder="Shown on DO" style="font-family: inherit; font-size: inherit;" {{ $isView ? 'disabled' : '' }}></textarea>
                                    @error('details') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="item-form-section-title">Image</div>
                        <div class="row g-1 g-lg-2 align-items-start">
                            <div class="col-lg-8">
                                <div class="form-group mb-1">
                                    <label for="image" class="form-label mb-1">Item image</label>
                                    @if(!$isView)
                                        <input type="file" wire:model="image" id="image"
                                            accept="image/*"
                                            class="form-control form-control-sm">
                                        <div wire:loading wire:target="image" class="mt-1 small text-muted">Uploading…</div>
                                        @error('image')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    @elseif(!$imagePreview && !($item && $item->image))
                                        <p class="small text-muted mb-0">No image</p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-4 text-lg-end">
                                @if($imagePreview || ($item && $item->image))
                                    <div class="d-inline-block position-relative item-form-image-thumb">
                                        <button type="button"
                                            class="btn p-0 border-0 item-form-image-thumb-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#item-image-preview-modal"
                                            title="Click to enlarge">
                                            <img src="{{ $imagePreview }}" alt="Preview" class="rounded border">
                                        </button>
                                        @if(!$isView)
                                            <button type="button" wire:click="deleteImage" class="btn btn-danger btn-sm py-0 px-1 position-absolute top-0 end-0 m-1">×</button>
                                        @endif
                                    </div>
                                    <div class="modal fade" id="item-image-preview-modal" tabindex="-1" aria-labelledby="item-image-preview-modal-label" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-xl">
                                            <div class="modal-content item-form-image-modal-content border-0 bg-transparent">
                                                <div class="modal-body p-2 p-md-3 text-center position-relative">
                                                    <button type="button"
                                                        class="btn-close item-form-image-modal-close"
                                                        data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                    <img src="{{ $imagePreview }}"
                                                        id="item-image-preview-modal-img"
                                                        class="item-form-image-modal-img rounded shadow"
                                                        alt="Item image enlarged">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                            {{-- @if($item)
                            <div class="mt-2 pt-2 border-top item-form-batch-block">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0 small text-uppercase text-secondary">Batch tracking</h6>
                                </div>

                                @if(count($batchTrackings) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Batch Number</th>
                                                    <th>Quantity</th>
                                                    <th>PO Number</th>
                                                    <th>Received Date</th>
                                                    <th>Received By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($batchTrackings as $batch)
                                                    <tr>
                                                        <td>{{ $batch['batch_num'] }}</td>
                                                        <td>
                                                            @if($isView)
                                                                {{ $batch['quantity'] }}
                                                            @else
                                                                <input type="number" 
                                                                    wire:model="batchTrackings.{{ $loop->index }}.quantity"
                                                                    class="form-control form-control-sm rounded"
                                                                    step="1"
                                                                    min="0"
                                                                    value="{{ $batch['quantity'] }}">
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @php
                                                                $purchaseOrder = \App\Models\PurchaseOrder::where('po_num', $batch['po_num'])->first();
                                                            @endphp
                                                            @if ($purchaseOrder)
                                                                <a href="{{ route('purchase-orders.view', ['purchaseOrder' => $purchaseOrder->id]) }}">
                                                                    {{ $batch['po_num'] }}
                                                                </a>
                                                            @else
                                                                {{ $batch['po_num'] }}
                                                            @endif
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($batch['received_date'])->format('Y-m-d') }}</td>
                                                        <td>{{ $batch['received_by'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="1"><strong>Total Quantity:</strong></td>
                                                    <td colspan="5">{{ array_sum(array_column($batchTrackings, 'quantity')) }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        No batch tracking records found for this item.
                                    </div>
                                @endif

                                @if(!$isView)
                                <div class="mt-3 mb-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" wire:click="toggleAddBatchSection">
                                        {{ $showAddBatchSection ? 'Hide' : 'Add New Batch' }}
                                    </button>
                                    
                                    @if($showAddBatchSection)
                                    <div class="card mt-2">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-2">Add New Batch</h6>
                                            <div class="row g-2">
                                                <div class="col-md-3">
                                                    <label class="form-label">Quantity</label>
                                                    <input type="number" min="1" step="1" class="form-control form-control-sm rounded" wire:model.lazy="newBatchQty">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Received Date</label>
                                                    <input type="date" class="form-control form-control-sm rounded" wire:model.lazy="newBatchDate">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Source</label>
                                                    <input type="text" class="form-control form-control-sm rounded" value="Manual Addition" disabled readonly>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Doc No</label>
                                                    <input type="text" class="form-control form-control-sm rounded" value="-" disabled readonly>
                                                </div>
                                            </div>
                                            <div class="text-end mt-2">
                                                <button type="button" class="btn btn-success btn-sm" wire:click="submitAddBatch">Add Batch</button>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                            @endif --}}
                        @endif

                    </form>
                </div>
            </div>
        </div>
    </div>
    <style>
        .item-form-page .item-form-shell {
            max-width: 1280px;
        }

        .item-form-page .card {
            border: 1px solid #d8deea;
        }

        .item-form-page .card-header {
            background: #f7f9fc;
            border-bottom: 1px solid #d8deea;
            padding-top: 0.65rem;
            padding-bottom: 0.65rem;
        }

        .item-form-page .item-form-body {
            padding-top: 0.55rem;
            padding-bottom: 0.55rem;
        }

        .item-form-page .item-form-grid .form-group {
            margin-bottom: 0.25rem !important;
        }

        .item-form-page .item-form-section-title {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #5f6f86;
            border-top: 1px solid #e7ecf4;
            padding-top: 0.35rem;
            margin-top: 0.3rem;
            margin-bottom: 0.15rem;
        }

        .item-form-page .item-form-groups-row {
            margin-top: 0.15rem;
        }

        .item-form-page .item-form-group-panel {
            display: flex;
            flex-direction: column;
            border: 1px solid #d8deea;
            border-radius: 0.4rem;
            background: #fafbfd;
            padding: 0.45rem 0.55rem 0.55rem;
        }

        .item-form-page .item-form-section-title--panel {
            border-top: none;
            margin-top: 0;
            padding-top: 0;
            margin-bottom: 0.35rem;
            color: #3d4d63;
        }

        .item-form-page .item-form-group-fields {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .item-form-page .item-form-group-fields .form-group {
            margin-bottom: 0 !important;
        }

        .item-form-page .item-form-grid .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 0.12rem;
            color: #2f3b4b;
        }

        .item-form-page .item-form-grid .form-control,
        .item-form-page .item-form-grid .form-select {
            font-size: 0.8rem;
            min-height: calc(1.35em + 0.35rem + 2px);
            padding-top: 0.18rem;
            padding-bottom: 0.18rem;
        }

        .item-form-page .item-form-grid textarea.item-form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .item-form-page .item-form-image-thumb img {
            max-width: 100px;
            max-height: 100px;
            width: auto;
            height: auto;
            object-fit: cover;
        }

        .item-form-page .item-form-image-thumb-btn {
            cursor: zoom-in;
            line-height: 0;
        }

        .item-form-page .item-form-image-thumb-btn:hover img {
            opacity: 0.92;
        }

        .item-form-page .item-form-image-modal-img {
            max-width: 100%;
            max-height: min(85vh, 900px);
            width: auto;
            height: auto;
            object-fit: contain;
            background: #fff;
        }

        .item-form-page .item-form-image-modal-close {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            z-index: 2;
            background-color: #fff;
            border-radius: 50%;
            padding: 0.55rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .item-form-page .item-form-batch-block .table {
            font-size: 0.8rem;
        }

        .item-form-page .item-form-batch-block .table th,
        .item-form-page .item-form-batch-block .table td {
            padding: 0.35rem 0.45rem;
            vertical-align: middle;
        }

        .item-form-page .text-danger {
            font-size: 0.77rem;
        }

        .item-form-page .item-form-browse-position {
            font-size: 0.75rem;
        }
    </style>
    @if($browseNavEnabled)
    <script type="application/json" id="item-browse-client-data">@json($browseClientPayload)</script>
    <script>
        (function () {
            var registered = false;
            var instantBrowse = null;
            var fallbackWire = null;

            function isTypingTarget(el) {
                if (!el || !el.closest) return false;
                return !!el.closest('input, textarea, select, [contenteditable="true"]');
            }

            function getWireComponent() {
                var root = document.querySelector('.item-form-page');
                var wireId = root && root.closest('[wire\\:id]') && root.closest('[wire\\:id]').getAttribute('wire:id');
                if (!wireId || typeof Livewire === 'undefined') return null;
                return Livewire.find(wireId);
            }

            function readBrowsePayload() {
                var el = document.getElementById('item-browse-client-data');
                if (!el || !el.textContent) return null;
                try {
                    return JSON.parse(el.textContent);
                } catch (e) {
                    return null;
                }
            }

            function setField(id, value) {
                var el = document.getElementById(id);
                if (!el) return;
                // Display only — do not dispatch input events (wire:model.live would
                // run uniqueness validation against the previous item id).
                el.value = value === null || value === undefined ? '' : String(value);
            }

            function createInstantBrowse(payload, wire) {
                var state = {
                    payload: payload,
                    index: payload.index,
                    syncSeq: 0,
                    syncDebounce: null,
                    syncInFlight: false,
                };

                function currentId() {
                    return state.payload.ids[state.index];
                }

                function currentItem() {
                    return state.payload.items[String(currentId())];
                }

                function reapplyChrome() {
                    var item = currentItem();
                    if (!item) return;
                    applyItem(item);
                }

                function updateChrome(item) {
                    var pos = document.getElementById('item-browse-position');
                    if (pos) {
                        pos.textContent = (state.index + 1) + ' of ' + state.payload.ids.length;
                    }

                    var prevBtn = document.querySelector('[data-item-browse="prev"]');
                    var nextBtn = document.querySelector('[data-item-browse="next"]');
                    if (prevBtn) prevBtn.disabled = state.index <= 0;
                    if (nextBtn) nextBtn.disabled = state.index >= state.payload.ids.length - 1;

                    if (state.payload.urlTemplate) {
                        var url = state.payload.urlTemplate.replace('__ID__', String(item.id));
                        history.replaceState(null, '', url);
                        document.title = item.item_code + ' | Manage Item';
                    }

                    var tx = document.getElementById('item-browse-txlog');
                    if (tx && tx.href) {
                        tx.href = tx.href.replace(/transaction-log\/\d+/, 'transaction-log/' + item.id);
                    }
                }

                function setLocationOptions(warehouseId, selectedLocationId) {
                    var sel = document.getElementById('location');
                    if (!sel) return;
                    var list = state.payload.locationsByWarehouse[String(warehouseId)] || [];
                    var html = '<option value="">— None —</option>';
                    list.forEach(function (loc) {
                        html += '<option value="' + loc.id + '">' + loc.name + '</option>';
                    });
                    sel.innerHTML = html;
                    sel.value = selectedLocationId ? String(selectedLocationId) : '';
                }

                function applyItem(item) {
                    setField('item_code', item.item_code);
                    setField('item_name', item.item_name);
                    setField('category', item.category);
                    setField('family', item.family);
                    setField('group', item.group);
                    setField('um', item.um);
                    setField('cost', item.cost);
                    setField('cash_price', item.cash_price);
                    setField('term_price', item.term_price);
                    setField('cust_price', item.cust_price);
                    setField('qty', item.qty);
                    setField('stock_alert_level', item.stock_alert_level);
                    setField('supplier', item.supplier);
                    setField('warehouse', item.warehouse_id);
                    setLocationOptions(item.warehouse_id, item.location_id);
                    setField('memo', item.memo);
                    setField('details', item.details);

                    var thumb = document.querySelector('.item-form-image-thumb img');
                    if (thumb) {
                        if (item.image_url) {
                            thumb.src = item.image_url;
                            thumb.closest('.item-form-image-thumb').style.display = '';
                        } else {
                            thumb.removeAttribute('src');
                        }
                    }

                    var modalImg = document.getElementById('item-image-preview-modal-img');
                    if (modalImg && item.image_url) {
                        modalImg.src = item.image_url;
                    }

                    updateChrome(item);
                }

                function runSync() {
                    if (!wire) return;

                    var itemId = currentId();
                    var indexAtRequest = state.index;
                    var seq = ++state.syncSeq;

                    if (state.syncInFlight) {
                        return;
                    }

                    state.syncInFlight = true;

                    wire.call('syncBrowseItem', itemId)
                        .catch(function () {})
                        .finally(function () {
                            state.syncInFlight = false;

                            // Client index is source of truth — undo stale Livewire morphs.
                            reapplyChrome();

                            if (state.index !== indexAtRequest || seq !== state.syncSeq) {
                                runSync();
                            }
                        });
                }

                function queueSync() {
                    if (!wire) return;
                    clearTimeout(state.syncDebounce);
                    state.syncDebounce = setTimeout(runSync, 50);
                }

                function go(direction) {
                    var delta = direction === 'next' ? 1 : -1;
                    var newIndex = state.index + delta;
                    if (newIndex < 0 || newIndex >= state.payload.ids.length) return;
                    state.index = newIndex;
                    applyItem(currentItem());
                    queueSync();
                }

                return { go: go, reapply: reapplyChrome };
            }

            function handleBrowse(direction) {
                if (instantBrowse) {
                    instantBrowse.go(direction);
                    return;
                }
                if (fallbackWire) {
                    fallbackWire.call('navigateBrowse', direction);
                }
            }

            function refreshBrowseController() {
                var payload = readBrowsePayload();
                fallbackWire = getWireComponent();
                instantBrowse = payload && payload.items
                    ? createInstantBrowse(payload, fallbackWire)
                    : null;
            }

            function registerItemBrowseKeys() {
                if (typeof Livewire === 'undefined' || registered) return;
                registered = true;

                refreshBrowseController();

                document.addEventListener('click', function (e) {
                    var btn = e.target.closest('[data-item-browse]');
                    if (!btn || !document.querySelector('[data-item-browse-nav="1"]')) return;
                    e.preventDefault();
                    handleBrowse(btn.getAttribute('data-item-browse'));
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key !== 'ArrowUp' && e.key !== 'ArrowDown') return;
                    if (e.defaultPrevented || e.altKey || e.ctrlKey || e.metaKey) return;
                    if (!document.querySelector('[data-item-browse-nav="1"]')) return;
                    if (isTypingTarget(e.target)) return;

                    e.preventDefault();
                    handleBrowse(e.key === 'ArrowUp' ? 'prev' : 'next');
                });
            }

            document.addEventListener('livewire:init', function () {
                registerItemBrowseKeys();

                Livewire.hook('commit', function (_ref) {
                    var succeed = _ref.succeed;
                    var component = _ref.component;
                    if (!instantBrowse || !document.querySelector('[data-item-browse-nav="1"]')) {
                        return;
                    }
                    var root = document.querySelector('.item-form-page');
                    var wireId = root && root.closest('[wire\\:id]') && root.closest('[wire\\:id]').getAttribute('wire:id');
                    if (!wireId || !component || component.id !== wireId) {
                        return;
                    }
                    succeed(function () {
                        instantBrowse.reapply();
                    });
                });
            });
            document.addEventListener('livewire:navigated', refreshBrowseController);
            if (document.readyState !== 'loading' && typeof Livewire !== 'undefined') {
                registerItemBrowseKeys();
            }
        })();
    </script>
    @endif
    <script>
        (function () {
            var imageModalListenerBound = false;

            function syncItemImageModalSrc() {
                var thumb = document.querySelector('.item-form-page .item-form-image-thumb img');
                var modalImg = document.getElementById('item-image-preview-modal-img');
                if (!thumb || !modalImg) return;
                var src = thumb.getAttribute('src');
                if (src) {
                    modalImg.src = src;
                }
            }

            function bindItemImagePreviewModal() {
                if (imageModalListenerBound) return;
                var page = document.querySelector('.item-form-page');
                if (!page) return;
                imageModalListenerBound = true;
                page.addEventListener('show.bs.modal', function (e) {
                    if (e.target && e.target.id === 'item-image-preview-modal') {
                        syncItemImageModalSrc();
                    }
                });
            }

            document.addEventListener('livewire:init', bindItemImagePreviewModal);
            document.addEventListener('livewire:navigated', bindItemImagePreviewModal);
            if (document.readyState !== 'loading') {
                bindItemImagePreviewModal();
            }
        })();
    </script>
</div>
