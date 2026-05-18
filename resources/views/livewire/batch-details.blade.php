<div class="list-page-unified-density">
    <div class="container my-3">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <div class="row d-flex align-items-center justify-content-between">
                            <div class="col-8">
                                <h5 class="fw-bold mb-0 list-page-unified-title">Batch Details</h5>
                            </div>
                            <div class="col-4 text-end">
                                <a href="{{ route('batch-list') }}" class="btn btn-primary btn-sm">Back to Batch List</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Batch Information -->
                        <div class="card mb-3 border batch-details-summary">
                            <div class="card-body bg-light py-3">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="batch-details-meta-label">Batch Number</div>
                                        <div class="batch-details-meta-value">{{ $batchNum }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="batch-details-meta-label">Received Date</div>
                                        <div class="batch-details-meta-value">{{ $batchItems->first() ? \Carbon\Carbon::parse($batchItems->first()->received_date)->format('Y-m-d') : 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="batch-details-meta-label">Received By</div>
                                        <div class="batch-details-meta-value">{{ $batchItems->first() ? $batchItems->first()->receivedBy->name : 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="batch-details-meta-label">PO Number</div>
                                        <div class="batch-details-meta-value">
                                            @if($batchItems->first() && $batchItems->first()->purchaseOrder)
                                                <a href="{{ route('purchase-orders.view', ['purchaseOrder' => $batchItems->first()->purchaseOrder->id]) }}">
                                                    {{ $batchItems->first()->purchaseOrder->po_num }}
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Batch Items Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered batch-details-table">
                                <thead>
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th>Initial Batch Qty</th>
                                        <th>Current Batch Qty</th>
                                        <th>Current Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($batchItems as $item)
                                        @php
                                            $initialQty = $importQtyByItemId[$item->item_id] ?? 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $item->item->item_code }}</td>
                                            <td>
                                                <a href="{{ route('items.edit', ['item' => $item->item->id]) }}">
                                                    {{ $item->item->item_name }}
                                                </a>
                                            </td>
                                            <td>{{ $initialQty }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ \App\Models\BatchTracking::where('item_id', $item->item->id)->sum('quantity') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No items found in this batch</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3 batch-details-pagination">
                            {{ $batchItems->links() }}
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
        .list-page-unified-density .btn-sm {
            font-size: 0.78rem;
        }
        .batch-details-summary {
            --bs-card-border-color: #d0d7e2;
        }
        .batch-details-meta-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #2f3b4b;
            margin-bottom: 0.2rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .batch-details-meta-value {
            font-size: 0.8rem;
            line-height: 1.25;
            word-break: break-word;
        }
        .batch-details-meta-value a {
            font-size: inherit;
        }
        .table.batch-details-table {
            --bs-table-border-color: #d0d7e2;
            table-layout: auto;
            width: 100%;
        }
        .table.batch-details-table > :not(caption) > * > * {
            border-color: var(--bs-table-border-color);
        }
        .table.batch-details-table thead th {
            background-color: #f4f6fa;
            font-size: 0.78rem;
            line-height: 1.4;
            vertical-align: middle;
            padding: 0.5rem;
        }
        .table.batch-details-table tbody td {
            font-size: 0.8rem;
            line-height: 1.25;
            padding: 0.5rem;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .table.batch-details-table tbody td a:not(.btn) {
            font-size: inherit;
        }
        .batch-details-pagination {
            font-size: 0.8rem;
        }
    </style>
</div>
