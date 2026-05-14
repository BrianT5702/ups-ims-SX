<div class="list-page-unified-density">
    <div class="container my-2" style="padding-left: 0.25rem; padding-right: 0.25rem;">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header transaction-log-page-header d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="min-w-0 flex-grow-1">
                            @if($filteredSupplier)
                                <div class="text-muted fw-semibold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.08em;">Purchase orders</div>
                                <h5 class="fw-bold mb-0 list-page-unified-title mt-1">{{ $filteredSupplier->sup_name }}</h5>
                                <p class="small text-muted mb-0 mt-1">Total order(s): {{ $purchase_order_count }}</p>
                            @else
                                <h5 class="fw-bold mb-0 list-page-unified-title">Purchase Order List</h5>
                            @endif
                        </div>
                        <div class="d-flex align-items-start gap-2 flex-shrink-0">
                            @if($filteredSupplier)
                                <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">Back</a>
                            @else
                                <a wire:navigate href="{{ route('purchase-orders.add') }}" class="btn btn-primary btn-sm">Add PO</a>
                            @endif
                        </div>
                    </div>

                    <div class="card-body px-2 pb-3 transaction-log-card-body">
                        <div class="row mb-1 g-2 align-items-end list-page-unified-filters">
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" wire:model.live.debounce.100ms="poSearchTerm" class="form-control form-control-sm rounded" placeholder="Search PO number or supplier...">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select wire:model.live="statusFilter" class="form-select form-select-sm rounded">
                                    <option value="">All Status</option>
                                    @foreach($statusOptions as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
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

                        <div class="po-list-wrapper" style="position: relative;">
                            <style>
                                /* Wrapper to separate scrollable table from fixed pagination */
                                .po-list-wrapper {
                                    display: flex;
                                    flex-direction: column;
                                    width: 100%;
                                    max-width: 100%;
                                    overflow: hidden;
                                }
                                
                                /* Constrain Bootstrap table-responsive within wrapper */
                                .po-list-wrapper .table-responsive {
                                    max-width: 100%;
                                }

                                .po-list-scrollable {
                                    width: 100%;
                                    max-width: 100%;
                                    margin-bottom: 0;
                                }
                                
                                /* Table styling - auto layout to prevent overlapping */
                                .table.po-list { 
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
                                .table.po-list th,
                                .table.po-list td {
                                    white-space: nowrap;
                                    overflow: visible;
                                    text-overflow: clip;
                                    padding: var(--tx-log-cell-py) var(--tx-log-cell-px);
                                    vertical-align: middle;
                                    border: 1px solid #dee2e6; /* Clearer border lines */
                                }

                                .table.po-list tbody td {
                                    font-size: 0.78rem;
                                    line-height: 1.28;
                                }
                                
                                /* Table borders - clearer lines */
                                .table.po-list thead th {
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
                                
                                .table.po-list thead th:first-child {
                                    border-left: 1px solid #212529;
                                }
                                
                                .table.po-list thead th:last-child {
                                    border-right: 1px solid #212529;
                                }
                                
                                .table.po-list tbody tr {
                                    border-bottom: 1px solid #dee2e6;
                                }
                                
                                .table.po-list tbody tr:hover {
                                    background-color: #f8f9fa;
                                }
                                
                                .table.po-list tbody td:first-child {
                                    border-left: 1px solid #212529;
                                }
                                
                                .table.po-list tbody td:last-child {
                                    border-right: 1px solid #212529;
                                }
                                
                                .table.po-list tbody tr:last-child td {
                                    border-bottom: 1px solid #212529;
                                }
                                
                                /* Column widths - fixed minimum widths to prevent overlap */
                                .table.po-list th:nth-child(1), 
                                .table.po-list td:nth-child(1) { 
                                    min-width: 130px;
                                    width: 130px;
                                } /* PO Number */
                                
                                .table.po-list th:nth-child(2), 
                                .table.po-list td:nth-child(2) { 
                                    min-width: 90px;
                                    width: 90px;
                                } /* Date */
                                
                                .table.po-list th:nth-child(3), 
                                .table.po-list td:nth-child(3) { 
                                    min-width: 250px;
                                    width: auto; /* Allow expansion for long supplier names */
                                } /* Supplier Name - full text, no truncation */
                                
                                .table.po-list th:nth-child(4), 
                                .table.po-list td:nth-child(4) { 
                                    min-width: 120px;
                                    width: 120px;
                                } /* Status */
                                
                                .table.po-list th:nth-child(5), 
                                .table.po-list td:nth-child(5) { 
                                    min-width: 90px;
                                    width: 90px;
                                    text-align: center;
                                } /* Update */

                                .table.po-list th:nth-child(6), 
                                .table.po-list td:nth-child(6) { 
                                    min-width: 90px;
                                    width: 90px;
                                    text-align: center;
                                } /* Print */

                                .table.po-list th:nth-child(7), 
                                .table.po-list td:nth-child(7) { 
                                    min-width: 120px;
                                    width: 120px;
                                } /* Created by */
                                
                                .table.po-list th:nth-child(8), 
                                .table.po-list td:nth-child(8) { 
                                    min-width: 120px;
                                    width: 120px;
                                } /* Last edited by */
                                
                                /* Ensure links don't cause wrapping */
                                .table.po-list td a {
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

                                /* Print status styles */
                                .po-print-flag {
                                    font-weight: 500;
                                }
                            </style>
                            
                            <!-- Scrollable table area -->
                            <div class="table-responsive po-list-scrollable list-sticky-table-scroll">
                                <table class="table table-hover po-list">
                                    <thead>
                                        <tr>
                                            <th>PO Number</th>
                                            <th>
                                                <button type="button" wire:click="toggleDateSort" class="btn btn-sm p-0 border-0 bg-transparent text-dark text-decoration-none fw-semibold">
                                                    Date {{ $dateSortDirection === 'asc' ? '↓' : '↑' }}
                                                </button>
                                            </th>
                                            <th>Supplier Name</th>
                                            <th>Status</th>
                                            <th>Update</th>
                                            <th>Print</th>
                                            <th>Created by</th>
                                            <th>Last edited by</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($purchase_orders as $purchase_order)
                                            <tr>
                                                <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}"> {{ $purchase_order->po_num }}</a></td>
                                                <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}"> {{ \Carbon\Carbon::parse($purchase_order->date)->format('d/m/Y') }}</a></td>
                                                <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}">{{ $purchase_order->supplierSnapshot->sup_name ?? $purchase_order->supplier->sup_name }}</a></td>
                                                <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}">{{ $purchase_order->status }}</a></td>
                                                <td class="text-center">
                                                    <span class="po-print-flag">
                                                        {{ ($purchase_order->is_updated ?? 'N') === 'Y' ? 'Y' : 'N' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="po-print-flag">
                                                        {{ $purchase_order->printed === 'Y' ? 'Y' : 'N' }}
                                                    </span>
                                                </td>
                                                <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}">{{ $purchase_order->user->name ?? '-' }}</a></td>
                                                <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}">{{ $purchase_order->updatedBy->name ?? ($purchase_order->user->name ?? '-') }}</a></td>
                                                
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No purchase orders found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Fixed pagination area - separate from scrollable table -->
                            <div class="po-list-pagination d-flex justify-content-between align-items-center flex-wrap">
                                <div class="small text-muted">
                                    @php
                                        $from = $purchase_orders->firstItem() ?? 0;
                                        $to = $purchase_orders->lastItem() ?? 0;
                                        $total = $purchase_orders->total();
                                    @endphp
                                    Showing {{ $from }} to {{ $to }} of {{ $total }} results
                                </div>
                                <div>
                                    @if ($purchase_orders->hasPages())
                                        {{ $purchase_orders->links() }}
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
