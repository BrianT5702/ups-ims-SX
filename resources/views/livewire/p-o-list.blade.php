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
                            @php
                                $poListInitialColWidths = [130, 90, 280, 120, 90, 90, 120, 120];
                            @endphp
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
                                
                                /* Table borders (layout / clip / resize: partial .list-col-resize-table) */
                                .table.po-list.list-col-resize-table { 
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
                                
                                .table.po-list.list-col-resize-table th,
                                .table.po-list.list-col-resize-table td {
                                    padding: var(--tx-log-cell-py) var(--tx-log-cell-px);
                                    vertical-align: middle;
                                    border: 1px solid #dee2e6;
                                }

                                .table.po-list tbody td {
                                    font-size: 0.78rem;
                                    line-height: 1.28;
                                }
                                
                                .table.po-list thead th {
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

                                .table.po-list .list-col-resize-handle::after {
                                    content: '';
                                    position: absolute;
                                    top: 0;
                                    bottom: 0;
                                    right: 3px;
                                    width: 1px;
                                    background: transparent;
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
                                <table class="table table-hover po-list list-col-resize-table" data-list-col-storage-key="poList" data-list-col-variant="default">
                                    <colgroup>
                                        @foreach($poListInitialColWidths as $idx => $wPx)
                                            <col data-list-col-index="{{ $idx }}" style="width: {{ $wPx }}px;">
                                        @endforeach
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th><span class="list-th-label">PO Number</span><span class="list-col-resize-handle" data-list-col-index="0" title="Drag to resize"></span></th>
                                            <th>
                                                <span class="list-th-label">
                                                    <button type="button" wire:click="toggleDateSort" class="btn btn-sm p-0 border-0 bg-transparent text-dark text-decoration-none fw-semibold">
                                                        Date {{ $dateSortDirection === 'asc' ? '↓' : '↑' }}
                                                    </button>
                                                </span>
                                                <span class="list-col-resize-handle" data-list-col-index="1" title="Drag to resize"></span>
                                            </th>
                                            <th><span class="list-th-label">Supplier Name</span><span class="list-col-resize-handle" data-list-col-index="2" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Status</span><span class="list-col-resize-handle" data-list-col-index="3" title="Drag to resize"></span></th>
                                            <th class="text-center"><span class="list-th-label">Update</span><span class="list-col-resize-handle" data-list-col-index="4" title="Drag to resize"></span></th>
                                            <th class="text-center"><span class="list-th-label">Print</span><span class="list-col-resize-handle" data-list-col-index="5" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Created by</span><span class="list-col-resize-handle" data-list-col-index="6" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Last edited by</span><span class="list-col-resize-handle" data-list-col-index="7" title="Drag to resize"></span></th>
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
    @include('partials.list-table-column-resize')
</div>
