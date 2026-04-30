<div class="list-page-unified-density">
    <div class="container my-3">
    <div class="row">
        <div class="col-12 m-auto">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="fw-bold mb-0 list-page-unified-title">Restock List</h5>
                    <div class="d-flex flex-wrap align-items-center gap-2 small">
                        <span class="text-muted">Selected:</span>
                        <span class="badge bg-info" id="selectedItemCount">{{ $selectedItemCount }}</span>
                        <button 
                            type="button"
                            class="btn btn-sm btn-primary" 
                            wire:click="navigateToAddPO"
                            {{ count($stackedItems) == 0 ? 'disabled' : '' }}>
                            Add PO
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Stacked Items Display -->
                    <div class="mb-3">
                        <div class="restock-section-label text-muted fw-semibold mb-2">Selected items stack</div>
                        <div class="overflow-auto restock-stack-scroll">
                            <ul class="list-group list-group-flush border rounded">
                                @forelse($stackedItems as $restock)
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 restock-stack-item">
                                        <span class="text-break">{{ $restock->item->item_name }}</span>
                                        <button type="button" wire:click="toggleItemSelection({{ $restock->id }})" class="btn btn-sm btn-danger flex-shrink-0 ms-2">Remove</button>
                                    </li>
                                @empty
                                    <li class="list-group-item py-2 px-3 restock-stack-item text-muted">No items selected.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="col-md-4 mb-3 px-0">
                        <label class="form-label">Search</label>
                        <input type="text" wire:model.live.debounce.300ms="itemSearchTerm" class="form-control form-control-sm rounded" placeholder="Search item...">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered restock-list-table">
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
                                        <td colspan="5" class="text-center">No restock items found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="restock-list-pagination mt-2">{{ $restockItems->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <style>
        .list-page-unified-density .list-page-unified-title {
            font-size: 1.25rem;
        }
        .list-page-unified-density .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
            color: #2f3b4b;
        }
        .list-page-unified-density .form-control-sm {
            font-size: 0.8rem;
            min-height: calc(1.35em + 0.35rem + 2px);
            padding-top: 0.18rem;
            padding-bottom: 0.18rem;
        }
        .list-page-unified-density .btn-sm {
            font-size: 0.78rem;
        }
        .restock-section-label {
            font-size: 0.72rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .restock-stack-scroll {
            max-height: 150px;
        }
        .restock-stack-item {
            font-size: 0.8rem;
            line-height: 1.25;
        }
        .table.restock-list-table {
            --bs-table-border-color: #d0d7e2;
            table-layout: fixed;
            width: 100%;
        }
        .table.restock-list-table > :not(caption) > * > * {
            border-color: var(--bs-table-border-color);
        }
        .table.restock-list-table thead th {
            background-color: #f4f6fa;
            font-size: 0.78rem;
            line-height: 1.4;
            vertical-align: middle;
            padding: 0.5rem;
        }
        .table.restock-list-table tbody td {
            font-size: 0.8rem;
            line-height: 1.25;
            padding: 0.5rem;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .restock-list-pagination {
            font-size: 0.8rem;
        }
    </style>
</div>
