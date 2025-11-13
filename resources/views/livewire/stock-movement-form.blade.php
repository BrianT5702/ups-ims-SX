<div>
    <div class="container my-3">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold fs-5">{{ $isView ? 'View' : ($stockMovement ? 'Edit': 'Add' )}} Stock Movement</h5>
                        <div>
                            <a href="javascript:history.back()" class="btn btn-primary btn-sm">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveStockMovement">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="movement_type">Movement Type <span class="text-danger">*</span></label>
                                    <select wire:model="movement_type" id="movement_type" class="form-control rounded" 
                                            {{ $isView ? 'disabled' : '' }}>
                                        <option value="">Select Movement Type</option>
                                        <option value="In">In</option>
                                        <option value="Out">Out</option>
                                    </select>
                                    @error('movement_type')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="movement_date">Movement Date & Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" wire:model="movement_date" id="movement_date" 
                                           class="form-control rounded" {{ $isView ? 'disabled' : '' }}>
                                    @error('movement_date')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="reference_no">Reference Number</label>
                                    <input type="text" wire:model="reference_no" id="reference_no" 
                                           class="form-control rounded"
                                           {{ $isView ? 'disabled' : '' }}>
                                    @error('reference_no')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="remarks">Remarks</label>
                                    <input type="text" wire:model="remarks" id="remarks" 
                                           class="form-control rounded" placeholder="Notes about this movement" 
                                           {{ $isView ? 'disabled' : '' }}>
                                    @error('remarks')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                @if(!$isView)
                                    <div class="col-md-6">
                                        <label for="search">Search Items</label>
                                        <input type="text" wire:model.debounce.100ms="itemSearchTerm" 
                                               wire:keyup="searchItems" id="searchItem" 
                                               class="form-control rounded" 
                                               placeholder="Search by Item Code or Name" 
                                               {{ $isView ? 'disabled' : '' }} autocomplete="off">

                                        @if(count($itemSearchResults) > 0)
                                            <div class="search-results mt-2">
                                                <ul class="list-group">
                                                    @foreach($itemSearchResults as $result)
                                                        <li class="list-group-item d-flex justify-content-between align-items-center"
                                                            wire:click="addItem({{ $result->id }})" style="cursor: pointer;">
                                                            <span>{{ $result->item_code }} - {{ $result->item_name }} (<b>Qty on hand: {{ $result->qty }}</b>)</span>
                                                            <span class="text-muted">Click to add</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="selected-items mb-3">
                                <h6>Selected Items for Movement:</h6>
                                @error('stackedItems')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item Code</th>
                                            <th>Item Name</th>
                                            <th>Movement Quantity</th>
                                            <th>Remarks</th>
                                            @if(!$isView)
                                                <th>Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stackedItems as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item['item']['item_code'] }}</td>
                                            <td>{{ $item['item']['item_name'] }}</td>
                                            <td>
                                                <input type="number" 
                                                    wire:model.lazy="stackedItems.{{ $index }}.quantity" 
                                                    class="form-control rounded @error('stackedItems.'.$index.'.quantity') is-invalid @enderror" 
                                                    min="1" 
                                                    {{ $isView ? 'disabled' : '' }}
                                                    style="width: 100%;">
                                                @error('stackedItems.'.$index.'.quantity')
                                                    <div class="text-danger small ml-2">!</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="text" wire:model="stackedItems.{{ $index }}.remarks" 
                                                    class="form-control rounded form-control-sm" 
                                                    {{ $isView ? 'disabled' : '' }} placeholder="Optional notes">
                                            </td>
                                            @if(!$isView)
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                     wire:click="removeItem({{ $index }})" 
                                                           {{ $isView ? 'disabled' : ''}}>
                                                            Remove
                                                </button>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if(!$isView)
                            <div class="text-end">
                                <button type="submit" class="btn btn-success" @if(empty($stackedItems)) disabled @endif>
                                    {{ $stockMovement ? 'Update' : 'Save' }} Movement
                                </button>
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
    </style>
</div>
