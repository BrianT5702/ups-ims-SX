<div class="list-page-unified-density">
    <div class="container my-2" style="padding-left: 0.25rem; padding-right: 0.25rem;">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header transaction-log-page-header d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="min-w-0 flex-grow-1">
                            @if($filteredCustomer)
                                <div class="text-muted fw-semibold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.08em;">Delivery orders</div>
                                <h5 class="fw-bold mb-0 list-page-unified-title mt-1">{{ $filteredCustomer->cust_name }}</h5>
                                <p class="small text-muted mb-0 mt-1">Total order(s): {{ $delivery_order_count }}</p>
                            @else
                                <h5 class="fw-bold mb-0 list-page-unified-title">Delivery Order List</h5>
                            @endif
                        </div>
                        <div class="d-flex align-items-start gap-2 flex-shrink-0">
                            @if($filteredCustomer)
                                <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">Back</a>
                            @else
                                <a wire:navigate href="{{ route('delivery-orders.add') }}" class="btn btn-primary btn-sm">Add DO</a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body px-2 pb-3 transaction-log-card-body">
                        <div class="row mb-1 g-2 align-items-end list-page-unified-filters">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" wire:model.live.debounce.100ms="doSearchTerm" class="form-control form-control-sm rounded" placeholder="Search DO number or customer...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">From Date</label>
                                <input type="date" wire:model.live="startDate" class="form-control form-control-sm rounded">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">To Date</label>
                                <input type="date" wire:model.live="endDate" class="form-control form-control-sm rounded">
                            </div>
                        </div>
                        <div class="row transaction-log-reset-toolbar mb-1">
                            <div class="col-12 d-flex justify-content-end py-0">
                                <button wire:click="clearFilters" type="button" class="btn btn-outline-secondary btn-sm transaction-log-reset-btn">Reset</button>
                            </div>
                        </div>


                        <div class="do-list-wrapper" style="position: relative;">
                            @php
                                $activeDb = strtolower(session('active_db') ?: config('database.default'));
                                $showInvoiceNoColumn = in_array($activeDb, ['ups', 'ucs'], true);
                            @endphp
                            <style>
                                /* Wrapper to separate scrollable table from fixed pagination */
                                .do-list-wrapper {
                                    display: flex;
                                    flex-direction: column;
                                    width: 100%;
                                    max-width: 100%;
                                    overflow: hidden;
                                }
                                
                                /* Constrain Bootstrap table-responsive within wrapper */
                                .do-list-wrapper .table-responsive {
                                    max-width: 100%;
                                }

                                /* Scroll region: overflow + sticky header come from .list-sticky-table-scroll (partial) */
                                .do-list-scrollable {
                                    width: 100%;
                                    max-width: 100%;
                                    margin-bottom: 0;
                                }
                                
                                /* Table styling - auto layout to prevent overlapping */
                                .table.do-list { 
                                    table-layout: auto; 
                                    width: 100%;
                                    min-width: 100%;
                                    max-width: 100%; /* Constrain to container width */
                                    border-collapse: collapse; /* Changed to collapse for clearer borders */
                                    border-spacing: 0;
                                    margin-bottom: 0;
                                    border: 1px solid #212529; /* Outer border - darker for clarity */
                                    --tx-log-cell-px: 0.38rem;
                                    --tx-log-cell-py: 0.22rem;
                                }
                                
                                /* All cells - prevent wrapping and overlapping */
                                .table.do-list th,
                                .table.do-list td {
                                    white-space: nowrap;
                                    overflow: visible;
                                    text-overflow: clip;
                                    padding: var(--tx-log-cell-py) var(--tx-log-cell-px);
                                    vertical-align: middle;
                                    border: 1px solid #dee2e6; /* Clearer border lines */
                                }

                                .table.do-list tbody td {
                                    font-size: 0.78rem;
                                    line-height: 1.28;
                                }
                                
                                /* Table borders - clearer lines */
                                .table.do-list thead th {
                                    border-bottom: 2px solid #212529; /* Thicker header border */
                                    border-top: 1px solid #212529;
                                    border-left: 1px solid #dee2e6;
                                    border-right: 1px solid #dee2e6;
                                    background-color: #f8f9fa;
                                    font-weight: 600;
                                    font-size: 0.82rem;
                                    line-height: 1.3;
                                    letter-spacing: 0.01em;
                                }
                                
                                .table.do-list thead th:first-child {
                                    border-left: 1px solid #212529;
                                }
                                
                                .table.do-list thead th:last-child {
                                    border-right: 1px solid #212529;
                                }
                                
                                .table.do-list tbody tr {
                                    border-bottom: 1px solid #dee2e6;
                                }
                                
                                .table.do-list tbody tr:hover {
                                    background-color: #f8f9fa;
                                }
                                
                                .table.do-list tbody td:first-child {
                                    border-left: 1px solid #212529;
                                }
                                
                                .table.do-list tbody td:last-child {
                                    border-right: 1px solid #212529;
                                }
                                
                                .table.do-list tbody tr:last-child td {
                                    border-bottom: 1px solid #212529;
                                }
                                
                                /* Column widths - fixed minimum widths to prevent overlap */
                                .table.do-list th:nth-child(1), 
                                .table.do-list td:nth-child(1) { 
                                    min-width: 130px;
                                    width: 130px;
                                } /* DO Number */
                                
                                @if($showInvoiceNoColumn)
                                    .table.do-list th:nth-child(2), 
                                    .table.do-list td:nth-child(2) { 
                                        min-width: 90px;
                                        width: 90px;
                                    } /* Date */
                                    
                                    .table.do-list th:nth-child(3), 
                                    .table.do-list td:nth-child(3) { 
                                        min-width: 250px;
                                        width: auto; /* Allow expansion for long customer names */
                                    } /* Customer Name - full text, no truncation */
                                    
                                    .table.do-list th:nth-child(4), 
                                    .table.do-list td:nth-child(4) { 
                                        min-width: 120px;
                                        width: 120px;
                                    } /* Amount */
                                    
                                    .table.do-list th:nth-child(5), 
                                    .table.do-list td:nth-child(5) { 
                                        min-width: 130px;
                                        width: 130px;
                                    } /* Invoice No */
                                    
                                    .table.do-list th:nth-child(6), 
                                    .table.do-list td:nth-child(6) { 
                                        min-width: 100px;
                                        width: 100px;
                                    } /* Salesman */
                                    
                                    .table.do-list th:nth-child(7), 
                                    .table.do-list td:nth-child(7) { 
                                        min-width: 120px;
                                        width: 120px;
                                    } /* Status */
                                    
                                    .table.do-list th:nth-child(8), 
                                    .table.do-list td:nth-child(8) { 
                                        min-width: 90px;
                                        width: 90px;
                                        text-align: center;
                                    } /* Print */
                                    
                                    .table.do-list th:nth-child(9), 
                                    .table.do-list td:nth-child(9) { 
                                        min-width: 120px;
                                        width: 120px;
                                    } /* Created by */
                                    
                                    .table.do-list th:nth-child(10), 
                                    .table.do-list td:nth-child(10) { 
                                        min-width: 120px;
                                        width: 120px;
                                    } /* Last edited by */
                                @else
                                    .table.do-list th:nth-child(2), 
                                    .table.do-list td:nth-child(2) { 
                                        min-width: 90px;
                                        width: 90px;
                                    } /* Date */
                                    
                                    .table.do-list th:nth-child(3), 
                                    .table.do-list td:nth-child(3) { 
                                        min-width: 250px;
                                        width: auto; /* Allow expansion for long customer names */
                                    } /* Customer Name - full text, no truncation */
                                    
                                    .table.do-list th:nth-child(4), 
                                    .table.do-list td:nth-child(4) { 
                                        min-width: 120px;
                                        width: 120px;
                                    } /* Amount */
                                    
                                    .table.do-list th:nth-child(5), 
                                    .table.do-list td:nth-child(5) { 
                                        min-width: 100px;
                                        width: 100px;
                                    } /* Salesman */
                                    
                                    .table.do-list th:nth-child(6), 
                                    .table.do-list td:nth-child(6) { 
                                        min-width: 120px;
                                        width: 120px;
                                    } /* Status */
                                    
                                    .table.do-list th:nth-child(7), 
                                    .table.do-list td:nth-child(7) { 
                                        min-width: 90px;
                                        width: 90px;
                                        text-align: center;
                                    } /* Print */
                                    
                                    .table.do-list th:nth-child(8), 
                                    .table.do-list td:nth-child(8) { 
                                        min-width: 120px;
                                        width: 120px;
                                    } /* Created by */
                                    
                                    .table.do-list th:nth-child(9), 
                                    .table.do-list td:nth-child(9) { 
                                        min-width: 120px;
                                        width: 120px;
                                    } /* Last edited by */
                                @endif
                                
                                /* Ensure links don't cause wrapping */
                                .table.do-list td a {
                                    white-space: nowrap;
                                    display: inline-block;
                                    max-width: 100%;
                                    overflow: visible;
                                    text-overflow: clip;
                                }
                                
                                /* Action buttons layout */
                                .action-buttons {
                                    display: flex;
                                    flex-direction: column;
                                    gap: 0.25rem;
                                    align-items: center;
                                }

                                /* Status and print styles */
                                .do-status {
                                    font-weight: 600;
                                }
                                .do-status.posted {
                                    color: #198754; /* Bootstrap success green */
                                }
                                .do-status.unposted {
                                    color: #dc3545; /* Bootstrap danger red */
                                }
                                .do-print-flag {
                                    font-weight: 500;
                                }
                                /* Cancelled DO visual marker (zero item lines). */
                                .do-row-cancelled td,
                                .do-row-cancelled td a,
                                .do-row-cancelled .do-print-flag,
                                .do-row-cancelled .do-status {
                                    color: #b02a37 !important;
                                }
                            </style>
                            
                            <!-- Scrollable table area -->
                            <div class="table-responsive do-list-scrollable list-sticky-table-scroll">
                                <table class="table table-hover do-list">
                                    <thead>
                                        <tr>
                                            <th>DO Number</th>
                                            <th>Date</th>
                                            <th>Customer Name</th>
                                            <th>Amount</th>
                                            @if($showInvoiceNoColumn)
                                                <th>Invoice No</th>
                                            @endif
                                            <th>Salesman</th>
                                            <th>Status</th>
                                            <th>Print</th>
                                            <th>Created by</th>
                                            <th>Last edited by</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($delivery_orders as $delivery_order)
                                            @php
                                                $isCancelledStyle = ((int)($delivery_order->items_count ?? 0) === 0);
                                            @endphp
                                            <tr class="{{ $isCancelledStyle ? 'do-row-cancelled' : '' }}">
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}"> {{ $delivery_order->do_num }}</a></td>
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}"> {{ $delivery_order->date?->format('d/m/Y') ?? '—' }}</a></td>
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ $delivery_order->customerSnapshot->cust_name ?? $delivery_order->customer->cust_name }}</a></td>
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ $delivery_order->customerSnapshot->currency ?? $delivery_order->customer->currency ?? 'RM' }} {{ number_format($delivery_order->total_amount ?? 0, 2) }}</a></td>
                                                @if($showInvoiceNoColumn)
                                                    <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ $delivery_order->invoice_no ?? '' }}</a></td>
                                                @endif
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ $delivery_order->salesman ? strtoupper($delivery_order->salesman->username) : '-' }}</a></td>
                                                <td>
                                                    @php
                                                        // Treat Completed as Post, everything else as Unpost
                                                        $isPosted = ($delivery_order->status ?? 'Completed') === 'Completed';
                                                    @endphp
                                                    <span class="do-status {{ $isPosted ? 'posted' : 'unposted' }}">
                                                        {{ $isPosted ? 'Post' : 'Unpost' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="do-print-flag">
                                                        {{ $delivery_order->printed === 'Y' ? 'Y' : 'N' }}
                                                    </span>
                                                </td>
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ $delivery_order->user->name ?? '-' }}</a></td>
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ $delivery_order->updatedBy->name ?? ($delivery_order->user->name ?? '-') }}</a></td>
                                                
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $showInvoiceNoColumn ? 10 : 9 }}" class="text-center">No delivery orders found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Fixed pagination area - separate from scrollable table -->
                            <div class="do-list-pagination d-flex justify-content-between align-items-center flex-wrap">
                                <div class="small text-muted">
                                    @php
                                        $from = $delivery_orders->firstItem() ?? 0;
                                        $to = $delivery_orders->lastItem() ?? 0;
                                        $total = $delivery_orders->total();
                                    @endphp
                                    Showing {{ $from }} to {{ $to }} of {{ $total }} results
                                </div>
                                <div>
                                    @if ($delivery_orders->hasPages())
                                        {{ $delivery_orders->links() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('partials.unified-list-page-styles')
</div>