<div class="container my-3">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="row d-flex align-items-center justify-content-between">
                        <div class="col-8">
                            <h5 class="fw-bold fs-5">Batch Details</h5>
                        </div>
                        <div class="col-4 text-end">
                            <a href="{{ route('batch-list') }}" class="btn btn-primary btn-sm">Back to Batch List</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Batch Information -->
                    <div class="card mb-4">
                        <div class="card-body bg-light">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Batch Number:</strong><br>
                                    {{ $batchNum }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Received Date:</strong><br>
                                    {{ $batchItems->first() ? \Carbon\Carbon::parse($batchItems->first()->received_date)->format('Y-m-d') : 'N/A' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Received By:</strong><br>
                                    {{ $batchItems->first() ? $batchItems->first()->receivedBy->name : 'N/A' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>PO Number:</strong><br>
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

                    <!-- Batch Items Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
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
                                        $initialTransaction = \App\Models\Transaction::where('batch_id', $item->id)
                                            ->where('transaction_type', 'Stock In')
                                            ->orderBy('created_at', 'asc')
                                            ->first();
                                        $initialQty = $initialTransaction ? $initialTransaction->transaction_qty : 0;
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
                    <div class="mt-3">
                        {{ $batchItems->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 