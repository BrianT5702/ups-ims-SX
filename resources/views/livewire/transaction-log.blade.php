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
                        <div class="col-md-2">
                            <label class="form-label">Group</label>
                            <select 
                                wire:model.live="selectedGroupId" 
                                class="form-control rounded"
                            >
                                <option value="">All Groups</option>
                                @foreach($groups ?? [] as $group)
                                    <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2" x-data="{ open: false }" x-on:click.away="open = false">
                            <label class="form-label">Company Name</label>
                            <div class="position-relative">
                                @if(isset($selectedCompanyName) && $selectedCompanyName)
                                    <div class="input-group">
                                        <input 
                                            type="text" 
                                            class="form-control rounded" 
                                            value="{{ $selectedCompanyName }}"
                                            readonly
                                        >
                                        <button 
                                            type="button"
                                            wire:click="clearCompany"
                                            class="btn btn-outline-secondary"
                                            style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @else
                                    <input 
                                        type="text" 
                                        wire:model.debounce.300ms="companySearchTerm"
                                        wire:input.debounce.300ms="searchCompanies"
                                        x-on:focus="open = true"
                                        class="form-control rounded" 
                                        placeholder="Search company..."
                                        autocomplete="off"
                                    >
                                    @if((count($companySearchCustomers) > 0 || count($companySearchSuppliers) > 0) && $companySearchTerm)
                                        <div 
                                            class="position-absolute w-100 bg-white border rounded shadow-lg mt-1"
                                            style="max-height: 300px; overflow-y: auto; z-index: 1000;"
                                            x-show="open"
                                        >
                                            @if(count($companySearchCustomers) > 0)
                                                <div class="px-3 py-2 bg-light border-bottom">
                                                    <small class="text-muted fw-bold">CUSTOMERS</small>
                                                </div>
                                                <ul class="list-group list-group-flush mb-0">
                                                    @foreach($companySearchCustomers as $customer)
                                                        <li 
                                                            class="list-group-item list-group-item-action"
                                                            wire:click="selectCompany('{{ $customer['id'] }}')"
                                                            style="cursor: pointer;"
                                                        >
                                                            <span>{{ $customer['name'] }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            @if(count($companySearchSuppliers) > 0)
                                                <div class="px-3 py-2 bg-light border-bottom {{ count($companySearchCustomers) > 0 ? 'border-top' : '' }}">
                                                    <small class="text-muted fw-bold">SUPPLIERS</small>
                                                </div>
                                                <ul class="list-group list-group-flush mb-0">
                                                    @foreach($companySearchSuppliers as $supplier)
                                                        <li 
                                                            class="list-group-item list-group-item-action"
                                                            wire:click="selectCompany('{{ $supplier['id'] }}')"
                                                            style="cursor: pointer;"
                                                        >
                                                            <span>{{ $supplier['name'] }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-2">
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
                        
                        <div class="col-md-2">
                            <label class="form-label">From Date</label>
                            <input 
                                type="date" 
                                wire:model.live="startDate" 
                                class="form-control rounded"
                            >
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <input 
                                type="date" 
                                wire:model.live="endDate" 
                                class="form-control rounded"
                            >
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12 d-flex justify-content-end">
                            <button 
                                wire:click="clearFilters" 
                                class="btn btn-outline-secondary"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                    
                    @if($isGroupReportMode ?? false && isset($selectedGroup))
                        <div class="alert alert-info mb-3">
                            <strong>Group Report Mode:</strong> Showing all items in <strong>{{ $selectedGroup->group_name }}</strong> 
                            from {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}. 
                            Items with no transactions in this period are also shown.
                        </div>
                    @endif

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
                                min-width: 100px;
                                width: 100px;
                            } /* Date */
                            
                            .table.transaction-log-table th:nth-child(2), 
                            .table.transaction-log-table td:nth-child(2) { 
                                min-width: 130px;
                                width: 130px;
                            } /* Source Doc No */
                            
                            .table.transaction-log-table th:nth-child(3), 
                            .table.transaction-log-table td:nth-child(3) { 
                                min-width: 120px;
                                width: 120px;
                            } /* Item Code */
                            
                            .table.transaction-log-table th:nth-child(4), 
                            .table.transaction-log-table td:nth-child(4) { 
                                min-width: 200px;
                                width: auto;
                            } /* Item Name */
                            
                            .table.transaction-log-table th:nth-child(5), 
                            .table.transaction-log-table td:nth-child(5) { 
                                min-width: 200px;
                                width: auto;
                            } /* Customer Name */
                            
                            .table.transaction-log-table th:nth-child(6), 
                            .table.transaction-log-table td:nth-child(6) { 
                                min-width: 100px;
                                width: 100px;
                                text-align: right;
                            } /* Price */
                            
                            .table.transaction-log-table th:nth-child(7), 
                            .table.transaction-log-table td:nth-child(7) { 
                                min-width: 80px;
                                width: 80px;
                            } /* In */
                            
                            .table.transaction-log-table th:nth-child(8), 
                            .table.transaction-log-table td:nth-child(8) { 
                                min-width: 80px;
                                width: 80px;
                            } /* Out */
                            
                            .table.transaction-log-table th:nth-child(9), 
                            .table.transaction-log-table td:nth-child(9) { 
                                min-width: 100px;
                                width: 100px;
                            } /* Balance */
                            
                            .table.transaction-log-table th:nth-child(10), 
                            .table.transaction-log-table td:nth-child(10) { 
                                min-width: 130px;
                                width: 130px;
                            } /* Batch Number */
                            
                            .table.transaction-log-table th:nth-child(11), 
                            .table.transaction-log-table td:nth-child(11) { 
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
                                <tr align="left">
                                    <th>Date</th>
                                    <th>Source Doc No</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Company Name</th>
                                    <th style="text-align: center;">Price</th>
                                    <th>In</th>
                                    <th>Out</th>
                                    <th>Balance</th>
                                    <th>Batch Number</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($isGroupReportMode ?? false)
                                    {{-- Group Report Mode: Show items with their transactions --}}
                                    @forelse($transactions as $entry)
                                        @if($entry['type'] === 'item_no_transactions')
                                            {{-- Item with no transactions in date range --}}
                                            @php
                                                $item = $entry['item'];
                                                // Get balance at end of date range
                                                // This is the balance after the last transaction up to and including the end date
                                                $endDateCarbon = \Carbon\Carbon::parse($endDate ?? now())->endOfDay();
                                                $lastTransaction = \App\Models\Transaction::where('item_id', $item->id)
                                                    ->where('created_at', '<=', $endDateCarbon)
                                                    ->orderBy('created_at', 'desc')
                                                    ->first();
                                                // If no transactions exist up to end date, use item's current qty
                                                $balance = $lastTransaction ? $lastTransaction->qty_after : $item->qty;
                                                $balance = $lastTransaction ? $lastTransaction->qty_after : $item->qty;
                                            @endphp
                                            <tr align="center" style="background-color: #f8f9fa;">
                                                <td>-</td>
                                                <td>-</td>
                                                <td><a href="{{ route('items.view', ['item' => $item->id]) }}">{{ $item->item_code }}</a></td>
                                                <td><a href="{{ route('items.view', ['item' => $item->id]) }}">{{ $item->item_name }}</a></td>
                                                <td>-</td>
                                                <td class="text-right">-</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>{{ $balance }}</td>
                                                <td>-</td>
                                                <td>-</td>
                                            </tr>
                                        @else
                                            {{-- Regular transaction --}}
                                            @php
                                                $transaction = $entry['transaction'];
                                                // Get customer/supplier name based on source type
                                                $customerName = '-';
                                                
                                                // For DO (Delivery Order) - but not DO Reversal types, show customer name
                                                if (($transaction->source_type === 'DO' || $transaction->source_type === 'Delivery Order') && $transaction->source_doc_num) {
                                                    $deliveryOrder = \App\Models\DeliveryOrder::where('do_num', $transaction->source_doc_num)
                                                        ->with('customerSnapshot', 'customer')
                                                        ->first();
                                                    
                                                    if ($deliveryOrder) {
                                                        $customerName = $deliveryOrder->customerSnapshot->cust_name ?? $deliveryOrder->customer->cust_name ?? '-';
                                                    }
                                                }
                                                // For PO (Purchase Order), show supplier name
                                                elseif (($transaction->source_type === 'PO' || $transaction->source_type === 'Purchase Order') && $transaction->source_doc_num) {
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
                                                
                                                // Get price from DO or PO item
                                                $unitPrice = '-';
                                                if (($transaction->source_type === 'DO' || $transaction->source_type === 'Delivery Order') && $transaction->source_doc_num && $transaction->item_id) {
                                                    $deliveryOrder = \App\Models\DeliveryOrder::where('do_num', $transaction->source_doc_num)->first();
                                                    if ($deliveryOrder) {
                                                        $doItem = \App\Models\DeliveryOrderItem::where('do_id', $deliveryOrder->id)
                                                            ->where('item_id', $transaction->item_id)
                                                            ->first();
                                                        if ($doItem && $doItem->unit_price) {
                                                            $unitPrice = number_format($doItem->unit_price, 2);
                                                        }
                                                    }
                                                } elseif (($transaction->source_type === 'PO' || $transaction->source_type === 'Purchase Order') && $transaction->source_doc_num && $transaction->item_id) {
                                                    $purchaseOrder = \App\Models\PurchaseOrder::where('po_num', $transaction->source_doc_num)->first();
                                                    if ($purchaseOrder) {
                                                        $poItem = \App\Models\PurchaseOrderItem::where('po_id', $purchaseOrder->id)
                                                            ->where('item_id', $transaction->item_id)
                                                            ->first();
                                                        if ($poItem && $poItem->unit_price) {
                                                            $unitPrice = number_format($poItem->unit_price, 2);
                                                        }
                                                    }
                                                }
                                                
                                                // Determine In/Out based on transaction_type
                                                $inQty = '';
                                                $outQty = '';
                                                
                                                if ($transaction->transaction_type === 'Stock In') {
                                                    $inQty = $transaction->transaction_qty;
                                                } elseif ($transaction->transaction_type === 'Stock Out') {
                                                    $outQty = abs($transaction->transaction_qty);
                                                }

                                                // Batch number display
                                                $batchNumber = $transaction->batch->batch_num ?? '-';
                                                if ($batchNumber === 'BATCH-00000000-000') {
                                                    $batchNumber = '-';
                                                }
                                            @endphp
                                            <tr align="left" style="cursor: pointer;">
                                                <td>{{ $transaction->created_at->format('d/m/Y') }}</td>
                                                <td wire:click="redirectToPage('{{ $transaction->source_type }}', {{ $transaction->id }})">{{ $transaction->source_doc_num }}</td>
                                                <td><a href="{{ route('items.view', ['item' => $transaction->item->id]) }}">{{ $transaction->item->item_code }}</a></td>
                                                <td><a href="{{ route('items.view', ['item' => $transaction->item->id]) }}">{{ $transaction->item->item_name }}</a></td>
                                                <td>{{ $customerName }}</td>
                                                <td class="text-right">{{ $unitPrice }}</td>
                                                <td>{{ $inQty }}</td>
                                                <td>{{ $outQty }}</td>
                                                <td>{{ $transaction->qty_after }}</td>
                                                <td>{{ $batchNumber }}</td>
                                                <td>{{ $transaction->user->name }}</td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center">No items found.</td>
                                        </tr>
                                    @endforelse
                                @else
                                    {{-- Regular Transaction Mode --}}
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
                                            
                                            // Get price from DO or PO item
                                            $unitPrice = '-';
                                            if (($transaction->source_type === 'DO' || $transaction->source_type === 'Delivery Order') && $transaction->source_doc_num && $transaction->item_id) {
                                                $deliveryOrder = \App\Models\DeliveryOrder::where('do_num', $transaction->source_doc_num)->first();
                                                if ($deliveryOrder) {
                                                    $doItem = \App\Models\DeliveryOrderItem::where('do_id', $deliveryOrder->id)
                                                        ->where('item_id', $transaction->item_id)
                                                        ->first();
                                                    if ($doItem && $doItem->unit_price) {
                                                        $unitPrice = number_format($doItem->unit_price, 2);
                                                    }
                                                }
                                            } elseif (($transaction->source_type === 'PO' || $transaction->source_type === 'Purchase Order') && $transaction->source_doc_num && $transaction->item_id) {
                                                $purchaseOrder = \App\Models\PurchaseOrder::where('po_num', $transaction->source_doc_num)->first();
                                                if ($purchaseOrder) {
                                                    $poItem = \App\Models\PurchaseOrderItem::where('po_id', $purchaseOrder->id)
                                                        ->where('item_id', $transaction->item_id)
                                                        ->first();
                                                    if ($poItem && $poItem->unit_price) {
                                                        $unitPrice = number_format($poItem->unit_price, 2);
                                                    }
                                                }
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
                                        <tr align="left" style="cursor: pointer;">
                                            <td>{{ $transaction->created_at->format('d/m/Y') }}</td>
                                            <td wire:click="redirectToPage('{{ $transaction->source_type }}', {{ $transaction->id }})">{{ $transaction->source_doc_num }}</td>
                                            <td><a href="{{ route('items.view', ['item' => $transaction->item->id]) }}">{{ $transaction->item->item_code }}</a></td>
                                            <td><a href="{{ route('items.view', ['item' => $transaction->item->id]) }}">{{ $transaction->item->item_name }}</a></td>
                                            <td>{{ $customerName }}</td>
                                            <td class="text-right">{{ $unitPrice }}</td>
                                            <td>{{ $inQty }}</td>
                                            <td>{{ $outQty }}</td>
                                            <td>{{ $transaction->qty_after }}</td>
                                            <td>{{ $batchNumber }}</td>
                                            <td>{{ $transaction->user->name }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                            <td colspan="11" class="text-center">No transactions found.</td>
                                    </tr>
                                @endforelse
                                @endif
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