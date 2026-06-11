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
                                $showInvoiceNoColumn = \App\Helpers\CompanyAccess::showsDoInvoiceNo($activeDb);
                                $doListBaseColWidths = $showInvoiceNoColumn
                                    ? [100, 70, 175, 85, 90, 70, 58, 42, 65, 65]
                                    : [100, 70, 200, 85, 70, 58, 42, 65, 65];
                                $doListInitialColWidths = array_merge($doListBaseColWidths, [95]);
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
                                
                                /* Table styling — borders (layout / resize / clip from partial .list-col-resize-table) */
                                .table.do-list.list-col-resize-table { 
                                    width: max-content;
                                    min-width: 100%;
                                    max-width: none;
                                    border-collapse: collapse; /* Changed to collapse for clearer borders */
                                    border-spacing: 0;
                                    margin-bottom: 0;
                                    border: 1px solid #212529; /* Outer border - darker for clarity */
                                    --tx-log-cell-px: 0.38rem;
                                    --tx-log-cell-py: 0.22rem;
                                }

                                .table.do-list.list-col-resize-table th,
                                .table.do-list.list-col-resize-table td {
                                    padding: var(--tx-log-cell-py) var(--tx-log-cell-px);
                                    border: 1px solid #dee2e6; /* Clearer border lines */
                                    vertical-align: middle;
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

                                .table.do-list .list-col-resize-handle::after {
                                    content: '';
                                    position: absolute;
                                    top: 0;
                                    bottom: 0;
                                    right: 3px;
                                    width: 1px;
                                    background: transparent;
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

                                .table.do-list th.do-list-col-user,
                                .table.do-list td.do-list-col-user,
                                .table.do-list th.do-list-col-datetime,
                                .table.do-list td.do-list-col-datetime {
                                    font-size: 0.72rem;
                                    line-height: 1.2;
                                }

                                .table.do-list td.do-list-col-datetime {
                                    white-space: nowrap;
                                }
                            </style>
                            
                            <!-- Scrollable table area -->
                            <div class="table-responsive do-list-scrollable list-sticky-table-scroll">
                                <table class="table table-hover do-list list-col-resize-table" data-list-col-storage-key="doList" data-list-col-variant="{{ $showInvoiceNoColumn ? 'inv-v2' : 'noinv-v2' }}">
                                    <colgroup>
                                        @foreach($doListInitialColWidths as $idx => $wPx)
                                            <col
                                                data-list-col-index="{{ $idx }}"
                                                @if($idx === count($doListInitialColWidths) - 1) data-list-col-overflow="1" @endif
                                                style="width: {{ $wPx }}px;"
                                            >
                                        @endforeach
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th><span class="list-th-label">DO Number</span><span class="list-col-resize-handle" data-list-col-index="0" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Date</span><span class="list-col-resize-handle" data-list-col-index="1" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Customer Name</span><span class="list-col-resize-handle" data-list-col-index="2" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Amount</span><span class="list-col-resize-handle" data-list-col-index="3" title="Drag to resize"></span></th>
                                            @if($showInvoiceNoColumn)
                                                <th><span class="list-th-label">Invoice No</span><span class="list-col-resize-handle" data-list-col-index="4" title="Drag to resize"></span></th>
                                                <th><span class="list-th-label">Salesman</span><span class="list-col-resize-handle" data-list-col-index="5" title="Drag to resize"></span></th>
                                                <th><span class="list-th-label">Status</span><span class="list-col-resize-handle" data-list-col-index="6" title="Drag to resize"></span></th>
                                                <th class="text-center"><span class="list-th-label">Print</span><span class="list-col-resize-handle" data-list-col-index="7" title="Drag to resize"></span></th>
                                                <th class="do-list-col-user"><span class="list-th-label">Created by</span><span class="list-col-resize-handle" data-list-col-index="8" title="Drag to resize"></span></th>
                                                <th class="do-list-col-user"><span class="list-th-label">Last edited by</span><span class="list-col-resize-handle" data-list-col-index="9" title="Drag to resize"></span></th>
                                                <th class="do-list-col-datetime"><span class="list-th-label">Last edited at</span><span class="list-col-resize-handle" data-list-col-index="10" title="Drag to resize"></span></th>
                                            @else
                                                <th><span class="list-th-label">Salesman</span><span class="list-col-resize-handle" data-list-col-index="4" title="Drag to resize"></span></th>
                                                <th><span class="list-th-label">Status</span><span class="list-col-resize-handle" data-list-col-index="5" title="Drag to resize"></span></th>
                                                <th class="text-center"><span class="list-th-label">Print</span><span class="list-col-resize-handle" data-list-col-index="6" title="Drag to resize"></span></th>
                                                <th class="do-list-col-user"><span class="list-th-label">Created by</span><span class="list-col-resize-handle" data-list-col-index="7" title="Drag to resize"></span></th>
                                                <th class="do-list-col-user"><span class="list-th-label">Last edited by</span><span class="list-col-resize-handle" data-list-col-index="8" title="Drag to resize"></span></th>
                                                <th class="do-list-col-datetime"><span class="list-th-label">Last edited at</span><span class="list-col-resize-handle" data-list-col-index="9" title="Drag to resize"></span></th>
                                            @endif
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
                                                <td class="do-list-col-user"><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ $delivery_order->user->name ?? '-' }}</a></td>
                                                <td class="do-list-col-user"><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ $delivery_order->updatedBy->name ?? ($delivery_order->user->name ?? '-') }}</a></td>
                                                <td class="do-list-col-datetime">
                                                    <a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">
                                                        {{ $delivery_order->updated_at
                                                            ? \Carbon\Carbon::parse($delivery_order->updated_at)->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i')
                                                            : '—' }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $showInvoiceNoColumn ? 11 : 10 }}" class="text-center">No delivery orders found.</td>
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
    @include('partials.list-table-column-resize')
</div>