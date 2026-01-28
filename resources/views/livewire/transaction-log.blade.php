<div>
    <div class="container my-3">
    <div class="row">
            <div class="col-md-11 m-auto">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold fs-5 mb-0">
                        @if($filteredItem)
                            Transaction Log for {{ $filteredItem->item_name }} ({{ $filteredItem->item_code }})
                        @else
                            Manage Transactions
                        @endif
                    </h5>
                    @if($filteredItem)
                    <div class="col-4 text-end">

                    <a href="javascript:history.back()" class="btn btn-primary btn-sm">Back</a>
                    </div>
                    @endif
                </div>
                
                <div class="card-body" style="overflow-x: hidden;">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="searchTerm" 
                                class="form-control rounded" 
                                placeholder="Search item code, name, or doc number"
                            >
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Source Type</label>
                            <select 
                                wire:model.live="sourceTypeFilter" 
                                class="form-control rounded"
                            >
                                <option value="">All Types</option>
                                @foreach($sourceTypeOptions as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input 
                                type="date" 
                                wire:model.live="startDate" 
                                class="form-control rounded"
                            >
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input 
                                type="date" 
                                wire:model.live="endDate" 
                                class="form-control rounded"
                            >
                        </div>
                        
                        <div class="col-md-1 d-flex align-items-end">
                            <button 
                                wire:click="clearFilters" 
                                class="btn btn-outline-secondary"
                            >
                                Reset
                            </button>
                        </div>
                    </div>

                    <div class="transaction-log-wrapper" style="position: relative;">
                        <style>
                            /* Wrapper to separate scrollable table from fixed pagination */
                            .transaction-log-wrapper {
                                display: flex;
                                flex-direction: column;
                                width: 100%;
                                max-width: 100%;
                                overflow-x: hidden;
                            }
                            
                            /* Scrollable table container - separate from pagination */
                            .transaction-log-scrollable {
                                overflow-x: auto;
                                overflow-y: visible;
                                width: 100%;
                                margin-bottom: 0;
                                -webkit-overflow-scrolling: touch;
                                scrollbar-width: thin;
                                scrollbar-color: #cbd5e0 #f7fafc;
                            }
                            .transaction-log-scrollable::-webkit-scrollbar {
                                height: 10px;
                            }
                            .transaction-log-scrollable::-webkit-scrollbar-track {
                                background: #f7fafc;
                                border-radius: 5px;
                            }
                            .transaction-log-scrollable::-webkit-scrollbar-thumb {
                                background: #cbd5e0;
                                border-radius: 5px;
                            }
                            .transaction-log-scrollable::-webkit-scrollbar-thumb:hover {
                                background: #a0aec0;
                            }
                            
                            /* Table styling - auto layout, single row, no wrapping */
                            .table.transaction-log-table { 
                                table-layout: auto; 
                                width: max-content;
                                min-width: 100%;
                                border-collapse: collapse;
                                border-spacing: 0;
                                margin-bottom: 0;
                                border: 1px solid #212529;
                            }
                            
                            /* All cells - prevent wrapping and overlapping */
                            .table.transaction-log-table th,
                            .table.transaction-log-table td {
                                white-space: nowrap;
                                overflow: visible;
                                text-overflow: clip;
                                padding: 4px 8px;
                                vertical-align: middle;
                                border: 1px solid #dee2e6;
                            }
                            
                            /* Table borders - clearer lines */
                            .table.transaction-log-table thead th {
                                border-bottom: 2px solid #212529;
                                border-top: 1px solid #212529;
                                border-left: 1px solid #dee2e6;
                                border-right: 1px solid #dee2e6;
                                background-color: #f8f9fa;
                                font-weight: 600;
                            }
                            
                            .table.transaction-log-table thead th:first-child {
                                border-left: 1px solid #212529;
                            }
                            
                            .table.transaction-log-table thead th:last-child {
                                border-right: 1px solid #212529;
                            }
                            
                            .table.transaction-log-table tbody tr {
                                border-bottom: 1px solid #dee2e6;
                            }
                            
                            .table.transaction-log-table tbody tr:hover {
                                background-color: #f8f9fa;
                            }
                            
                            .table.transaction-log-table tbody td:first-child {
                                border-left: 1px solid #212529;
                            }
                            
                            .table.transaction-log-table tbody td:last-child {
                                border-right: 1px solid #212529;
                            }
                            
                            .table.transaction-log-table tbody tr:last-child td {
                                border-bottom: 1px solid #212529;
                            }
                            
                            /* Column widths - fixed minimum widths */
                            .table.transaction-log-table th:nth-child(1), 
                            .table.transaction-log-table td:nth-child(1) { 
                                min-width: 50px;
                                width: 50px;
                            } /* # */
                            
                            .table.transaction-log-table th:nth-child(2), 
                            .table.transaction-log-table td:nth-child(2) { 
                                min-width: 100px;
                                width: 100px;
                            } /* Date */
                            
                            .table.transaction-log-table th:nth-child(3), 
                            .table.transaction-log-table td:nth-child(3) { 
                                min-width: 120px;
                                width: 120px;
                            } /* Source Type */
                            
                            .table.transaction-log-table th:nth-child(4), 
                            .table.transaction-log-table td:nth-child(4) { 
                                min-width: 130px;
                                width: 130px;
                            } /* Source Doc No */
                            
                            .table.transaction-log-table th:nth-child(5), 
                            .table.transaction-log-table td:nth-child(5) { 
                                min-width: 120px;
                                width: 120px;
                            } /* Item Code */
                            
                            .table.transaction-log-table th:nth-child(6), 
                            .table.transaction-log-table td:nth-child(6) { 
                                min-width: 200px;
                                width: auto;
                            } /* Item Name */
                            
                            .table.transaction-log-table th:nth-child(7), 
                            .table.transaction-log-table td:nth-child(7) { 
                                min-width: 200px;
                                width: auto;
                            } /* Customer Name */
                            
                            .table.transaction-log-table th:nth-child(8), 
                            .table.transaction-log-table td:nth-child(8) { 
                                min-width: 80px;
                                width: 80px;
                            } /* In */
                            
                            .table.transaction-log-table th:nth-child(9), 
                            .table.transaction-log-table td:nth-child(9) { 
                                min-width: 80px;
                                width: 80px;
                            } /* Out */
                            
                            .table.transaction-log-table th:nth-child(10), 
                            .table.transaction-log-table td:nth-child(10) { 
                                min-width: 100px;
                                width: 100px;
                            } /* Balance */
                            
                            .table.transaction-log-table th:nth-child(11), 
                            .table.transaction-log-table td:nth-child(11) { 
                                min-width: 130px;
                                width: 130px;
                            } /* Batch Number */
                            
                            .table.transaction-log-table th:nth-child(12), 
                            .table.transaction-log-table td:nth-child(12) { 
                                min-width: 120px;
                                width: 120px;
                            } /* User */
                            
                            /* Ensure links don't cause wrapping */
                            .table.transaction-log-table td a {
                                white-space: nowrap;
                                display: inline-block;
                                max-width: 100%;
                                overflow: visible;
                                text-overflow: clip;
                            }
                            
                            /* Fixed pagination container - separate from scrollable table */
                            .transaction-log-pagination {
                                position: relative;
                                width: 100%;
                                margin-top: 0;
                                padding-top: 15px;
                                border-top: 1px solid #dee2e6;
                                background-color: #fff;
                                z-index: 10;
                            }
                        </style>
                        
                        <!-- Scrollable table area -->
                        <div class="table-responsive mt-3 transaction-log-scrollable">
                            <table class="table table-hover transaction-log-table">
                            <thead>
                                <tr align="center">
                                    <th>#</th>
                                        <th>Date</th>
                                    <th>Source Type</th>
                                    <th>Source Doc No</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                        <th>Customer Name</th>
                                        <th>In</th>
                                        <th>Out</th>
                                        <th>Balance</th>
                                    <th>Batch Number</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                        @php
                                            // Get customer/supplier name based on source type
                                            $customerName = '-';
                                            
                                            // For DO (Delivery Order) - but not DO Reversal types, show customer name
                                            if (($transaction->source_type === 'DO' || $transaction->source_type === 'Delivery Order') && $transaction->source_doc_num) {
                                                // Always query manually to ensure we get the data
                                                $deliveryOrder = \App\Models\DeliveryOrder::where('do_num', $transaction->source_doc_num)
                                                    ->with('customerSnapshot', 'customer')
                                                    ->first();
                                                
                                                if ($deliveryOrder) {
                                                    $customerName = $deliveryOrder->customerSnapshot->cust_name ?? $deliveryOrder->customer->cust_name ?? '-';
                                                }
                                            }
                                            // For PO (Purchase Order), show supplier name
                                            elseif (($transaction->source_type === 'PO' || $transaction->source_type === 'Purchase Order') && $transaction->source_doc_num) {
                                                // Always query manually to ensure we get the data
                                                $purchaseOrder = \App\Models\PurchaseOrder::where('po_num', $transaction->source_doc_num)
                                                    ->with('supplierSnapshot', 'supplier')
                                                    ->first();
                                                
                                                if ($purchaseOrder) {
                                                    $customerName = $purchaseOrder->supplierSnapshot->sup_name ?? $purchaseOrder->supplier->sup_name ?? '-';
                                                }
                                            }
                                            // For DO Reversal types, show "-"
                                            elseif (in_array($transaction->source_type, ['DO Reversal', 'DO Status Reversal', 'DO Delta Reversal', 'DO Draft Delta'])) {
                                                $customerName = '-';
                                            }
                                            
                                            // Determine In/Out based on transaction_type
                                            $inQty = '';
                                            $outQty = '';
                                            
                                            if ($transaction->transaction_type === 'Stock In') {
                                                $inQty = $transaction->transaction_qty;
                                            } elseif ($transaction->transaction_type === 'Stock Out') {
                                                $outQty = abs($transaction->transaction_qty);
                                            }

                                            // Batch number display:
                                            // - For PO / stock-in batches, show the actual batch number
                                            // - For the special initial import batch 'BATCH-00000000-000', hide it (show '-')
                                            //   because it's just a placeholder used during Excel import
                                            $batchNumber = $transaction->batch->batch_num ?? '-';
                                            if ($batchNumber === 'BATCH-00000000-000') {
                                                $batchNumber = '-';
                                            }
                                        @endphp
                                        <tr align="center" style="cursor: pointer;">
                                        <td>{{ $loop->iteration }}</td>
                                            <td>{{ $transaction->created_at->format('d/m/Y') }}</td>
                                        <td wire:click="redirectToPage('{{ $transaction->source_type }}', {{ $transaction->id }})">{{ $transaction->source_type }}</td>
                                        <td wire:click="redirectToPage('{{ $transaction->source_type }}', {{ $transaction->id }})">{{ $transaction->source_doc_num }}</td>
                                        <td><a href="{{ route('items.view', ['item' => $transaction->item->id]) }}">{{ $transaction->item->item_code }}</a></td>
                                        <td><a href="{{ route('items.view', ['item' => $transaction->item->id]) }}">{{ $transaction->item->item_name }}</a></td>
                                            <td>{{ $customerName }}</td>
                                            <td>{{ $inQty }}</td>
                                            <td>{{ $outQty }}</td>
                                        <td>{{ $transaction->qty_after }}</td>
                                            <td>{{ $batchNumber }}</td>
                                        <td>{{ $transaction->user->name }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                            <td colspan="12" class="text-center">No transactions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>
                        
                        <!-- Fixed pagination area - separate from scrollable table -->
                        <div class="transaction-log-pagination">
                        {{ $transactions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>