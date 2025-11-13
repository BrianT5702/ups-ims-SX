<div class="container-fluid my-3">
    <div class="row">
        <div class="col-md-12 m-auto">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold fs-5 mb-0">Restock List</h5>
                    <div>
                        Selected Items: <span class="badge bg-info" id="selectedItemCount">{{ $selectedItemCount }}</span>
                        <button 
                            class="btn btn-sm btn-primary ms-3" 
                            wire:click="navigateToAddPO"
                            {{ count($stackedItems) == 0 ? 'disabled' : '' }}>
                            Add PO
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Stacked Items Display -->
                    <div class="mb-3">
                        <h6>Selected Items Stack</h6>
                        <div class="overflow-auto" style="max-height: 150px;">
                            <ul class="list-group">
                                @forelse($stackedItems as $restock)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $restock->item->item_name }}
                                        <button wire:click="toggleItemSelection({{ $restock->id }})" class="btn btn-sm btn-danger">Remove</button>
                                    </li>
                                @empty
                                    <li class="list-group-item">No items selected.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="col-md-4 mb-3">
                        <input type="text" wire:model.live.debounce.300ms="itemSearchTerm" class="form-control form-control-sm rounded" placeholder="Search item...">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr align="center">
                                    <th></th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Quantity on Hand</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($restockItems as $restock)
                                    <tr align="center">
                                        <td>
                                            <button wire:click="toggleItemSelection({{ $restock->id }})" class="btn btn-sm btn-success">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                        <td>{{ $restock->item->item_code }}</td>
                                        <td>{{ $restock->item->item_name }}</td>
                                        <td>{{ \App\Models\BatchTracking::where('item_id', $restock->item->id)->sum('quantity') }}
                                        </td>
                                        <td>
                                            <button
                                                wire:click.prevent="deleteRestock({{ $restock->id }})"
                                                onclick="confirm('Are you sure you want to remove this item?') || event.stopImmediatePropagation()"
                                                class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">No restock items found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{ $restockItems->links() }}
                    </div>
                </div>
                <div class="card-footer">
                </div>
                
            </div>
        </div>
    </div>
</div>
