<div class="list-page-unified-density">
    <div class="container my-3">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <div class="row d-flex align-items-center justify-content-between">
                            <div class="col-8">
                                <h5 class="fw-bold mb-0 list-page-unified-title">Batch List</h5>
                            </div>
                            <div class="col-4 text-end">
                                <a href="javascript:history.back()" class="btn btn-primary btn-sm">Back</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3 g-2 align-items-end">
                            <div class="col-md-3">
                                <label for="startDate" class="form-label">Start Date</label>
                                <input type="date" wire:model.live="startDate" id="startDate" class="form-control form-control-sm rounded" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="endDate" class="form-label">End Date</label>
                                <input type="date" wire:model.live="endDate" id="endDate" class="form-control form-control-sm rounded" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="searchTerm" class="form-label">Search Batch</label>
                                <input type="text" wire:model.live="searchTerm" id="searchTerm" class="form-control form-control-sm rounded" placeholder="Search batch number...">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" wire:click="clearFilters" class="btn btn-outline-secondary btn-sm">Clear Filters</button>
                            </div>
                        </div>

                        <!-- Batch List Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered batch-list-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Batch Number</th>
                                        <th>Received Date</th>
                                        <th>Received By</th>
                                        <th>PO Number</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($batches as $batch)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $batch->batch_num }}</td>
                                            <td>{{ \Carbon\Carbon::parse($batch->received_date)->format('Y-m-d') }}</td>
                                            <td>{{ $batch->receivedBy ? $batch->receivedBy->name : 'N/A' }}</td>
                                            <td>
                                                @if($batch->purchaseOrder)
                                                    <a href="{{ route('purchase-orders.view', ['purchaseOrder' => $batch->purchaseOrder->id]) }}">
                                                        {{ $batch->purchaseOrder->po_num }}
                                                    </a>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('batch-details', ['batchNum' => $batch->batch_num]) }}"
                                                   class="btn btn-primary btn-sm">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No batches found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3 batch-list-pagination">
                            {{ $batches->links() }}
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
        .table.batch-list-table {
            --bs-table-border-color: #d0d7e2;
            table-layout: auto;
            width: 100%;
        }
        .table.batch-list-table > :not(caption) > * > * {
            border-color: var(--bs-table-border-color);
        }
        .table.batch-list-table thead th {
            background-color: #f4f6fa;
            font-size: 0.78rem;
            line-height: 1.4;
            vertical-align: middle;
            padding: 0.5rem;
        }
        .table.batch-list-table tbody td {
            font-size: 0.8rem;
            line-height: 1.25;
            padding: 0.5rem;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .table.batch-list-table tbody td a:not(.btn) {
            font-size: inherit;
        }
        .batch-list-pagination {
            font-size: 0.8rem;
        }
    </style>
</div>
