<div class="container my-3">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="row d-flex align-items-center justify-content-between">
                        <div class="col-8">
                            <h5 class="fw-bold fs-5">Batch List</h5>
                        </div>
                        <div class="col-4 text-end">
                            <a href="javascript:history.back()" class="btn btn-primary btn-sm">Back</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" wire:model.live="startDate" class="form-control form-control-sm rounded" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" wire:model.live="endDate" class="form-control form-control-sm rounded" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-3">
                            <label for="searchTerm" class="form-label">Search Batch</label>
                            <input type="text" wire:model.live="searchTerm" class="form-control form-control-sm rounded" placeholder="Search batch number...">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button wire:click="clearFilters" class="btn btn-secondary btn-sm">Clear Filters</button>
                        </div>
                    </div>

                    <!-- Batch List Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                    <div class="mt-3">
                        {{ $batches->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 