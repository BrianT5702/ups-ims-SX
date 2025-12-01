<div class="container my-3">
    <div class="row">
        <div class="col-9 m-auto">
            <div class="card shadow-sm" style="overflow: visible;">
                <div class="card-header">
                    <div class="row d-flex align-items-center justify-content-between">
                        <div class="col-8">
                            <h5 class="fw-bold fs-5">{{ $isView ? 'View' : ($item ? 'Edit' : 'Add') }} Item</h5>
                        </div>
                        <div class="col-4 text-end">
                        <a href="javascript:history.back()" class="btn btn-primary btn-sm">Back</a>

                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="addItem">
                        <div class="form-group mb-2">
                            <label for="item_code" class="form-label">Item Code <span class="text-danger">*</span></label>
                            <input type="text" wire:model.live="item_code" id="item_code" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                            @error('item_code') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model.live="item_name" id="item_name" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                            @error('item_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select wire:model.live="category" id="category" class="form-select form-select-sm" {{ $isView ? 'disabled' : '' }}>
                                        <option value="" disabled>Select a category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->cat_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label for="family" class="form-label">Family <span class="text-danger">*</span></label>
                                    <select wire:model.live="family" id="family" class="form-select form-select-sm" {{ $isView ? 'disabled' : '' }}>
                                        <option value="" disabled>Select a family</option>
                                        @foreach($families as $family)
                                            <option value="{{ $family->id }}">{{ $family->family_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('family') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label for="group" class="form-label">Group <span class="text-danger">*</span></label>
                                    <select wire:model.live="group" id="group" class="form-select form-select-sm" {{ $isView ? 'disabled' : '' }}>
                                        <option value="" disabled>Select a group</option>
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('group') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label for="qty" class="form-label">Quantity <span class="text-danger">*</span></label>
                                    @if($item)
                                        <input type="number" value="{{ array_sum(array_column($batchTrackings, 'quantity')) }}" class="form-control form-control-sm rounded" disabled>
                                    @else
                                        <input type="number" wire:model.live="initialQuantity" min="0" class="form-control form-control-sm rounded">
                                    @endif
                                    @error('initialQuantity') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label for="cost" class="form-label">Cost <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model.live="cost" min="0" id="cost" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                    @error('cost') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label for="cash_price" class="form-label">Cash Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model.live="cash_price" min="0" id="cash_price" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                    @error('cash_price') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label for="term_price" class="form-label">Term Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model.live="term_price" min="0" id="term_price" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                    @error('term_price') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label for="cust_price" class="form-label">Customer Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model.live="cust_price" min="0" id="cust_price" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                    @error('cust_price') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label for="stock_alert_level" class="form-label">Stock Alert Level</label>
                                    <input type="number" wire:model.live="stock_alert_level" min="0" id="stock_alert_level" class="form-control form-control-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                    @error('stock_alert_level') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label for="um" class="form-label">Unit Measurement</label>
                                    <select wire:model.live="um" id="um" class="form-select form-select-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                        <option value="" disabled>Select a unit measurement</option>
                                        <option value="UNIT">UNIT</option>
                                        <option value="BOX">BOX</option>
                                        <option value="KG">KG</option>
                                        <option value="ROLL">ROLL</option>
                                        <option value="custom">Other (Specify)</option>
                                    </select>
                                    
                                    @if ($is_custom_um)
                                        <input type="text" wire:model.live="custom_um" wire:key="custom-um-input" class="form-control form-control-sm rounded mt-2" placeholder="Enter custom unit">
                                    @endif

                                    @error('um') <span class="text-danger">{{ $message }}</span> @enderror
                                    @error('custom_um') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                        </div>


                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label for="supplier" class="form-label">Supplier <span class="text-danger">*</span></label>
                                    <select wire:model.live="supplier" id="supplier" class="form-select form-select-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                        <option value="" disabled>Select a supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->sup_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplier') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                    <label for="warehouse" class="form-label">Warehouse <span class="text-danger">*</span></label>
                                    <select wire:model.live="warehouse" id="warehouse" class="form-select form-select-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                        <option value="" disabled>Select a warehouse</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('warehouse') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                            <select wire:model.live="location" id="location" class="form-select form-select-sm rounded" {{ $isView ? 'disabled' : '' }}>
                                        <option value="" disabled>Select a location</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->location_name }}</option>
                                        @endforeach
                                    </select>
                            @error('location') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="memo" class="form-label">Memo</label>
                                    <textarea id="memo" wire:model.defer="memo" class="form-control form-control-sm rounded" rows="3" placeholder="Enter memo or special handling notes" style="font-family: inherit; font-size: inherit;" {{ $isView ? 'disabled' : '' }}></textarea>
                                    @error('memo') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="details" class="form-label">Details</label>
                                    <textarea id="details" wire:model.defer="details" class="form-control form-control-sm rounded" rows="3" placeholder="Enter item details (shown in DO, PO, Quotation)" style="font-family: inherit; font-size: inherit;" {{ $isView ? 'disabled' : '' }}></textarea>
                                    @error('details') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <!-- Image upload section with preview -->
                            <div class="mb-4">
                                <label for="image" class="block text-gray-700">Item Image</label>
                                
                                <div class="mt-2 justify-items-center">
                                
                                    @if($imagePreview || ($item && $item->image))
                                        <div class="relative w-32 h-32 mt-3">
                                            <img src="{{ $imagePreview }}" alt="Item Preview" class="w-32 h-32 object-cover rounded">
                                            @if(!$isView)
                                                <button type="button" wire:click="deleteImage" 
                                                        class="absolute top-0 right-0 bg-red-500 text-white py-1 px-2 hover:bg-red-600">
                                                    Delete Image
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                
                                </div>

                                @if(!$isView)
                                <div class="mt-2">
                                    <input type="file" wire:model="image" id="image" 
                                        accept="image/*"
                                        class="mt-1 block w-full text-sm text-gray-500
                                                file:mr-4 file:py-2 file:px-4
                                                file:rounded-full file:border-0
                                                file:text-sm file:font-semibold
                                                file:bg-violet-50 file:text-violet-700
                                                hover:file:bg-violet-100">
                                    
                                    <div wire:loading wire:target="image" class="mt-2 text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-violet-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Uploading...
                                        </div>
                                    </div>
                                    
                                    @error('image') 
                                        <span class="text-red-500 text-xs mt-2">{{ $message }}</span>
                                    @enderror
                                </div>
                                @endif
                            </div>

                            @if($item)
                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold">Batch Tracking Information</h6>
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
                            @endif


                        @if(!$isView)
                            <div class="card-footer mb-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="submit" 
                                            class="btn btn-primary btn-sm"
                                            wire:loading.attr="disabled"
                                            wire:target="image"
                                            {{ $isImageUploading ? 'disabled' : '' }}>
                                            <span wire:loading wire:target="image" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            {{ $item ? 'Update' : 'Add' }}
                                        </button>
                                    </div>
                        @endif
                        @if(!$isView && $item)
                                    <div class="col-md-6 text-end">
                                        <button wire:confirm="Are you sure you want to delete this item? This will also delete all related batch tracking, purchase order, delivery order, and transaction records!" wire:click="deleteItem({{ $item->id }})" type="button" class="btn btn-danger btn-sm">Delete</button>
                                    </div>
                                </div>
                            </div>
                        @endif

                </div>
            </div>
        </div>
    </div>
</div>
