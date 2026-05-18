<div class="list-page-unified-density">
    <div class="container my-2" style="padding-left: 0.25rem; padding-right: 0.25rem;">
    <div class="row">
            <div class="col-md-11 m-auto">
            <div class="card shadow-sm">
                <div class="card-header transaction-log-page-header d-flex align-items-start justify-content-between gap-3 flex-wrap">
                    <div class="min-w-0 flex-grow-1">
                        @if($filteredItem)
                            <div class="transaction-log-header-eyebrow text-muted fw-semibold">Transaction log</div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-1 mb-1">
                                <span class="badge rounded-pill bg-light text-dark border font-monospace px-2 py-1">{{ $filteredItem->item_code }}</span>
                            </div>
                            <p class="transaction-log-header-item-name mb-0 text-body" title="{{ $filteredItem->item_name }}">{{ $filteredItem->item_name }}</p>
                            <p class="small text-muted mb-0 mt-2">
                                Current on hand:
                                <span class="fw-semibold text-body">{{ number_format((float) $filteredItem->qty, 2) }}</span>
                                <span class="mx-2">·</span>
                                Table is <strong>newest doc date first</strong>, then <strong>doc number</strong> on the same day. Balance is stock <strong>right after</strong> that line only — later moves (including hidden DO adjustments) can change on-hand without an extra visible row.
                            </p>
                        @else
                            <h5 class="fw-bold mb-0 list-page-unified-title">Transaction Log</h5>
                        @endif
                    </div>
                    @if($filteredItem)
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm flex-shrink-0 align-self-start">Back</a>
                    @endif
                </div>
                
                <div class="card-body px-2 pb-3 transaction-log-card-body">
                    <div class="row mb-1 g-2 align-items-end list-page-unified-filters">
                        <div class="col-md-2">
                            <label class="form-label">Group</label>
                            <select 
                                wire:model.live="selectedGroupId" 
                                class="form-select form-select-sm rounded"
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
                                            class="form-control form-control-sm rounded" 
                                            value="{{ $selectedCompanyName }}"
                                            readonly
                                        >
                                        <button 
                                            type="button"
                                            wire:click="clearCompany"
                                            class="btn btn-outline-secondary btn-sm"
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
                                        class="form-control form-control-sm rounded" 
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
                                class="form-control form-control-sm rounded" 
                                placeholder="Search item code, name, or doc number"
                            >
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Source Type</label>
                            <select 
                                wire:model.live="sourceTypeFilter" 
                                class="form-select form-select-sm rounded"
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
                                class="form-control form-control-sm rounded"
                            >
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <input 
                                type="date" 
                                wire:model.live="endDate" 
                                class="form-control form-control-sm rounded"
                            >
                        </div>
                    </div>
                    
                    <div class="row transaction-log-reset-toolbar mb-1">
                        <div class="col-12 d-flex justify-content-end py-0">
                            <button 
                                wire:click="clearFilters" 
                                type="button"
                                class="btn btn-outline-secondary btn-sm transaction-log-reset-btn"
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
                        @php
                            $txLogListInitialColWidths = [78, 108, 92, 200, 68, 68, 68];
                            $txLogColResizeVariant = ($isGroupReportMode ?? false) ? 'group' : 'std';
                        @endphp
                        <style>
                            /* Wrapper to separate scrollable table from fixed pagination */
                            .transaction-log-wrapper {
                                display: flex;
                                flex-direction: column;
                                width: 100%;
                                max-width: 100%;
                                overflow: hidden;
                            }
                            
                            /* Constrain Bootstrap table-responsive within wrapper */
                            .transaction-log-wrapper .table-responsive {
                                max-width: 100%;
                            }

                            .transaction-log-scrollable {
                                width: 100%;
                                max-width: 100%;
                                margin-bottom: 0;
                            }
                            
                            /* Table (layout / clip / resize: partial .list-col-resize-table) */
                            .table.transaction-log-table.list-col-resize-table { 
                                width: 100%;
                                min-width: 100%;
                                max-width: 100%;
                                border-collapse: collapse;
                                border-spacing: 0;
                                margin-bottom: 0;
                                border: 1px solid #212529;
                                --tx-log-cell-px: 0.38rem;
                                --tx-log-cell-py: 0.22rem;
                            }
                            
                            .table.transaction-log-table.list-col-resize-table th,
                            .table.transaction-log-table.list-col-resize-table td {
                                padding: var(--tx-log-cell-py) var(--tx-log-cell-px);
                                vertical-align: middle;
                                border: 1px solid #dee2e6;
                            }

                            .table.transaction-log-table tbody td {
                                font-size: 0.78rem;
                                line-height: 1.28;
                            }
                            
                            .table.transaction-log-table thead th {
                                border-bottom: 2px solid #212529;
                                border-top: 1px solid #212529;
                                border-left: 1px solid #dee2e6;
                                border-right: 1px solid #dee2e6;
                                background-color: #f8f9fa;
                                font-weight: 600;
                                font-size: 0.82rem;
                                line-height: 1.3;
                                letter-spacing: 0.01em;
                            }

                            .table.transaction-log-table .list-col-resize-handle::after {
                                content: '';
                                position: absolute;
                                top: 0;
                                bottom: 0;
                                right: 3px;
                                width: 1px;
                                background: transparent;
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

                            .table.transaction-log-table.list-col-resize-table th:nth-child(n+5),
                            .table.transaction-log-table.list-col-resize-table td:nth-child(n+5) {
                                text-align: right;
                                font-variant-numeric: tabular-nums;
                            }
                            
                        </style>
                        
                        <!-- Scrollable table area -->
                        <div class="table-responsive transaction-log-scrollable list-sticky-table-scroll">
                            <table class="table table-hover transaction-log-table list-col-resize-table" data-list-col-storage-key="transactionLog" data-list-col-variant="{{ $txLogColResizeVariant }}">
                            <colgroup>
                                @foreach($txLogListInitialColWidths as $idx => $wPx)
                                    <col data-list-col-index="{{ $idx }}" style="width: {{ $wPx }}px;">
                                @endforeach
                            </colgroup>
                            <thead>
                                <tr>
                                    <th><span class="list-th-label">Doc Date</span><span class="list-col-resize-handle" data-list-col-index="0" title="Drag to resize"></span></th>
                                    <th><span class="list-th-label">Source Doc No</span><span class="list-col-resize-handle" data-list-col-index="1" title="Drag to resize"></span></th>
                                    <th><span class="list-th-label">Item Code</span><span class="list-col-resize-handle" data-list-col-index="2" title="Drag to resize"></span></th>
                                    <th><span class="list-th-label">Company Name</span><span class="list-col-resize-handle" data-list-col-index="3" title="Drag to resize"></span></th>
                                    <th><span class="list-th-label">In</span><span class="list-col-resize-handle" data-list-col-index="4" title="Drag to resize"></span></th>
                                    <th><span class="list-th-label">Out</span><span class="list-col-resize-handle" data-list-col-index="5" title="Drag to resize"></span></th>
                                    <th><span class="list-th-label">Balance</span><span class="list-col-resize-handle" data-list-col-index="6" title="Drag to resize"></span></th>
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
                                                $lastTransaction = \App\Models\Transaction::query()
                                                    ->select('transactions.*')
                                                    ->withLogDocDateJoins()
                                                    ->where('transactions.item_id', $item->id)
                                                    ->whereLogDisplayDateOnOrBefore($endDateCarbon)
                                                    ->orderByLogDisplayDate('desc')
                                                    ->first();
                                                // If no transactions exist up to end date, use item's current qty
                                                $balance = $lastTransaction ? $lastTransaction->qty_after : $item->qty;
                                            @endphp
                                            <tr align="center" style="background-color: #f8f9fa;">
                                                <td>-</td>
                                                <td>-</td>
                                                <td><a href="{{ route('items.view', ['item' => $item->id]) }}">{{ $item->item_code }}</a></td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>{{ $balance }}</td>
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
                                                
                                                // Determine In/Out based on transaction_type
                                                $inQty = '';
                                                $outQty = '';
                                                
                                                $isDoReversal = in_array($transaction->source_type, ['DO Reversal', 'DO Status Reversal', 'DO Delta Reversal', 'DO Draft Delta'], true);

                                                if ($transaction->transaction_type === 'Stock In') {
                                                    $inQty = $transaction->transaction_qty;
                                                } elseif ($transaction->transaction_type === 'Stock Out' && !$isDoReversal) {
                                                    $outQty = abs($transaction->transaction_qty);
                                                }
                                            @endphp
                                            <tr align="left" style="cursor: pointer;">
                                                <td>{{ $transaction->logDisplayDate()->format('d/m/Y') }}</td>
                                                <td wire:click="redirectToPage('{{ $transaction->source_type }}', {{ $transaction->id }})">{{ $transaction->source_doc_num }}</td>
                                                <td><a href="{{ route('items.view', ['item' => $transaction->item->id]) }}">{{ $transaction->item->item_code }}</a></td>
                                                <td>{{ $customerName }}</td>
                                                <td>{{ $inQty }}</td>
                                                <td>{{ $outQty }}</td>
                                                <td>{{ ($displayBalances ?? [])[(int) $transaction->id] ?? $transaction->qty_after }}</td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No items found.</td>
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
                                            
                                            // Determine In/Out based on transaction_type
                                            $inQty = '';
                                            $outQty = '';
                                            
                                            $isDoReversal = in_array($transaction->source_type, ['DO Reversal', 'DO Status Reversal', 'DO Delta Reversal', 'DO Draft Delta'], true);

                                            if ($transaction->transaction_type === 'Stock In') {
                                                $inQty = $transaction->transaction_qty;
                                            } elseif ($transaction->transaction_type === 'Stock Out' && !$isDoReversal) {
                                                $outQty = abs($transaction->transaction_qty);
                                            }
                                        @endphp
                                        <tr align="left" style="cursor: pointer;">
                                            <td>{{ $transaction->logDisplayDate()->format('d/m/Y') }}</td>
                                            <td wire:click="redirectToPage('{{ $transaction->source_type }}', {{ $transaction->id }})">{{ $transaction->source_doc_num }}</td>
                                            <td><a href="{{ route('items.view', ['item' => $transaction->item->id]) }}">{{ $transaction->item->item_code }}</a></td>
                                            <td>{{ $customerName }}</td>
                                            <td>{{ $inQty }}</td>
                                            <td>{{ $outQty }}</td>
                                            <td>{{ ($displayBalances ?? [])[(int) $transaction->id] ?? $transaction->qty_after }}</td>
                                        </tr>
                                @empty
                                    <tr>
                                            <td colspan="7" class="text-center">No transactions found.</td>
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
    @include('partials.unified-list-page-styles')
    @include('partials.list-table-column-resize')
    <style>
        .transaction-log-header-eyebrow {
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .transaction-log-header-item-name {
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.25;
            word-break: break-word;
            overflow-wrap: anywhere;
            max-width: min(100%, 52rem);
        }
        .transaction-log-page-header .badge {
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</div>