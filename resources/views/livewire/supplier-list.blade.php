<div class="list-page-unified-density">
    <div class="container my-2" style="padding-left: 0.25rem; padding-right: 0.25rem;">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header transaction-log-page-header d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="min-w-0 flex-grow-1">
                            <h5 class="fw-bold mb-0 list-page-unified-title">Manage Supplier</h5>
                        </div>
                        <div class="d-flex align-items-start gap-2 flex-shrink-0">
                            <a wire:navigate href="{{ route('suppliers.add') }}" class="btn btn-primary btn-sm">Add Supplier</a>
                        </div>
                    </div>

                    <div class="card-body px-2 pb-3 transaction-log-card-body">
                        <div class="row mb-1 g-2 align-items-end list-page-unified-filters">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" wire:model.live.debounce.100ms="supplierSearchTerm" class="form-control form-control-sm rounded" placeholder="Search supplier name...">
                            </div>

                        </div>
                        <div class="row transaction-log-reset-toolbar mb-1">
                            <div class="col-12 d-flex justify-content-end py-0">
                                <button wire:click="clearFilters" type="button" class="btn btn-outline-secondary btn-sm transaction-log-reset-btn">Reset</button>
                            </div>
                        </div>

                        <div class="supplier-list-wrapper" style="position: relative;">
                            @php
                                $supplierListInitialColWidths = [52, 260, 120, 180, 130];
                            @endphp
                            <style>
                                /* Wrapper to separate scrollable table from fixed pagination */
                                .supplier-list-wrapper {
                                    display: flex;
                                    flex-direction: column;
                                    width: 100%;
                                    max-width: 100%;
                                    overflow: hidden;
                                }
                                
                                /* Constrain Bootstrap table-responsive within wrapper */
                                .supplier-list-wrapper .table-responsive {
                                    max-width: 100%;
                                }

                                .supplier-list-scrollable {
                                    width: 100%;
                                    max-width: 100%;
                                    margin-bottom: 0;
                                }
                                
                                /* Table borders (layout / clip / resize: partial .list-col-resize-table) */
                                .table.supplier-list.list-col-resize-table { 
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
                                
                                .table.supplier-list.list-col-resize-table th,
                                .table.supplier-list.list-col-resize-table td {
                                    padding: var(--tx-log-cell-py) var(--tx-log-cell-px);
                                    vertical-align: middle;
                                    border: 1px solid #dee2e6;
                                }

                                .table.supplier-list tbody td {
                                    font-size: 0.78rem;
                                    line-height: 1.28;
                                }
                                
                                .table.supplier-list thead th {
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

                                .table.supplier-list .list-col-resize-handle::after {
                                    content: '';
                                    position: absolute;
                                    top: 0;
                                    bottom: 0;
                                    right: 3px;
                                    width: 1px;
                                    background: transparent;
                                }
                                
                                .table.supplier-list thead th:first-child {
                                    border-left: 1px solid #212529;
                                }
                                
                                .table.supplier-list thead th:last-child {
                                    border-right: 1px solid #212529;
                                }
                                
                                .table.supplier-list tbody tr {
                                    border-bottom: 1px solid #dee2e6;
                                }
                                
                                .table.supplier-list tbody tr:hover {
                                    background-color: #f8f9fa;
                                }
                                
                                .table.supplier-list tbody td:first-child {
                                    border-left: 1px solid #212529;
                                }
                                
                                .table.supplier-list tbody td:last-child {
                                    border-right: 1px solid #212529;
                                }
                                
                                .table.supplier-list tbody tr:last-child td {
                                    border-bottom: 1px solid #212529;
                                }
                                
                                .table.supplier-list .supplier-list-sort-btn {
                                    font-size: 0.82rem;
                                    line-height: 1.3;
                                }
                                .table.supplier-list.list-col-resize-table tbody td:last-child {
                                    overflow: visible;
                                    text-overflow: clip;
                                }
                                .table.supplier-list .supplier-list-po-link {
                                    font-size: 0.78rem;
                                    padding: 0;
                                    white-space: nowrap;
                                }
                            </style>
                            
                            <!-- Scrollable table area -->
                            <div class="table-responsive supplier-list-scrollable list-sticky-table-scroll">
                                <table class="table table-hover supplier-list list-col-resize-table" data-list-col-storage-key="supplierList" data-list-col-variant="default">
                                    <colgroup>
                                        @foreach($supplierListInitialColWidths as $idx => $wPx)
                                            <col data-list-col-index="{{ $idx }}" style="width: {{ $wPx }}px;">
                                        @endforeach
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th class="text-center"><span class="list-th-label">No</span><span class="list-col-resize-handle" data-list-col-index="0" title="Drag to resize"></span></th>
                                            <th>
                                                <span class="list-th-label">
                                                    <button type="button" wire:click="sortBy('sup_name')" class="btn btn-sm p-0 border-0 bg-transparent text-dark text-decoration-none fw-semibold supplier-list-sort-btn">
                                                        Name{{ $sortColumn === 'sup_name' ? ($sortOrder === 'asc' ? ' ↑' : ' ↓') : '' }}
                                                    </button>
                                                </span>
                                                <span class="list-col-resize-handle" data-list-col-index="1" title="Drag to resize"></span>
                                            </th>
                                            <th><span class="list-th-label">Phone</span><span class="list-col-resize-handle" data-list-col-index="2" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Email</span><span class="list-col-resize-handle" data-list-col-index="3" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Action</span><span class="list-col-resize-handle" data-list-col-index="4" title="Drag to resize"></span></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($suppliers as $supplier)
                                            <tr>
                                                <td class="text-center text-muted">{{ ($suppliers->firstItem() ?? 0) + $loop->index }}</td>
                                                <td><a wire:navigate href="{{ route('suppliers.view', $supplier->id) }}">{{ $supplier->sup_name }}</a></td>
                                                <td><a wire:navigate href="{{ route('suppliers.view', $supplier->id) }}">{{ $supplier->phone_num }}</a></td>
                                                <td><a wire:navigate href="{{ route('suppliers.view', $supplier->id) }}">{{ $supplier->email }}</a></td>
                                                <td>
                                                    @can('Manage PO')
                                                        <button type="button" wire:click.prevent="showSupplierPO({{ $supplier->id }})" class="btn btn-link supplier-list-po-link text-decoration-none">View POs</button>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No suppliers found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Fixed pagination area - separate from scrollable table -->
                            <div class="supplier-list-pagination d-flex justify-content-between align-items-center flex-wrap">
                                <div class="small text-muted">
                                    @php
                                        $from = $suppliers->firstItem() ?? 0;
                                        $to = $suppliers->lastItem() ?? 0;
                                        $total = $suppliers->total();
                                    @endphp
                                    Showing {{ $from }} to {{ $to }} of {{ $total }} results
                                </div>
                                <div>
                                    @if ($suppliers->hasPages())
                                        {{ $suppliers->links() }}
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
