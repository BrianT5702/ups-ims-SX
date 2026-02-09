<div>
    <div class="container my-3" style="padding-left: 0.25rem; padding-right: 0.25rem;">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold fs-5 mb-0">
                            @if($filteredSupplier)
                                Purchase Order for {{ $filteredSupplier->sup_name }} - Total Order(s): {{$purchase_order_count}}
                            @else
                                Manage Purchase Order
                            @endif
                        </h5>
                        @if($filteredSupplier)
                            <div class="col-4 text-end">
                            <a href="javascript:history.back()" class="btn btn-primary btn-sm">Back</a>
                            </div>
                        @endif
                    </div>

                    <div class="card-body" style="padding-left: 0.5rem; padding-right: 0.5rem;">
                        <div class="row d-flex justify-content-end align-items-end">
                             @if(!$filteredSupplier)
                                <div class="col-md-2 text-end">
                                    <a wire:navigate href="{{route('purchase-orders.add')}}" class="btn btn-primary">Add PO</a>
                                </div>
                            @endif
                        </div>
                        <div class="row align-items-end mb-3">
                            <div class="col-md-3">
                                <input type="text" wire:model.live.debounce.100ms="poSearchTerm" class="form-control form-control-sm rounded" placeholder="Search PO...">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                    <select 
                                        wire:model.live="statusFilter" 
                                        class="form-control rounded"
                                    >
                                        <option value="">All Status</option>
                                        @foreach($statusOptions as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                </select>
                            </div>

                     
                            <div class="col-md-3">
                                <label class="form-label">From Date</label>
                                <input type="date" wire:model.live="startDate" class="form-control rounded" placeholder="dd/mm/yyyy">
                            </div>

          
                            <div class="col-md-3">
                                <label class="form-label">To Date</label>
                                <input type="date" wire:model.live="endDate" class="form-control rounded" placeholder="dd/mm/yyyy">
                            </div>


                            <div class="col-md-1 d-flex align-items-end">
                                <button wire:click="clearFilters" class="btn btn-outline-secondary">
                                    Reset
                                </button>
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
                                    overflow-x: auto;
                                    overflow-y: visible;
                                }
                                
                                /* Scrollable table container - separate from pagination */
                                .po-list-scrollable {
                                    overflow-x: auto;
                                    overflow-y: visible;
                                    width: 100%;
                                    max-width: 100%;
                                    margin-bottom: 0;
                                    -webkit-overflow-scrolling: touch;
                                    scrollbar-width: thin;
                                    scrollbar-color: #cbd5e0 #f7fafc;
                                }
                                .po-list-scrollable::-webkit-scrollbar {
                                    height: 10px;
                                }
                                .po-list-scrollable::-webkit-scrollbar-track {
                                    background: #f7fafc;
                                    border-radius: 5px;
                                }
                                .po-list-scrollable::-webkit-scrollbar-thumb {
                                    background: #cbd5e0;
                                    border-radius: 5px;
                                }
                                .po-list-scrollable::-webkit-scrollbar-thumb:hover {
                                    background: #a0aec0;
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
                                }
                                
                                /* All cells - prevent wrapping and overlapping */
                                .table.po-list th,
                                .table.po-list td {
                                    white-space: nowrap;
                                    overflow: visible;
                                    text-overflow: clip;
                                    padding: 3px 6px; /* Slightly smaller padding for tighter rows */
                                    vertical-align: middle;
                                    border: 1px solid #dee2e6; /* Clearer border lines */
                                    font-size: 0.9rem; /* Smaller font size for PO list */
                                }
                                
                                /* Table borders - clearer lines */
                                .table.po-list thead th {
                                    border-bottom: 2px solid #212529; /* Thicker header border */
                                    border-top: 1px solid #212529;
                                    border-left: 1px solid #dee2e6;
                                    border-right: 1px solid #dee2e6;
                                    background-color: #f8f9fa;
                                    font-weight: 600;
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
                                    min-width: 120px;
                                    width: 120px;
                                } /* Created by */
                                
                                .table.po-list th:nth-child(6), 
                                .table.po-list td:nth-child(6) { 
                                    min-width: 120px;
                                    width: 120px;
                                } /* Last edited by */
                                
                                .table.po-list th:nth-child(7), 
                                .table.po-list td:nth-child(7) { 
                                    min-width: 90px;
                                    width: 90px;
                                    text-align: center;
                                } /* Print */
                                
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
                                
                                /* Fixed pagination container - separate from scrollable table */
                                .po-list-pagination {
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
                            <div class="table-responsive po-list-scrollable" style="margin-top: 0.5rem;">
                                <table class="table table-hover po-list">
                                    <thead>
                                        <tr>
                                            <th>PO Number</th>
                                            <th>Date</th>
                                            <th>Supplier Name</th>
                                            <th>Status</th>
                                            <th>Created by</th>
                                            <th>Last edited by</th>
                                            <th>Print</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($purchase_orders as $purchase_order)
                                            <tr>
                                                <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}"> {{ $purchase_order->po_num }}</a></td>
                                                <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}"> {{ $purchase_order->created_at->format('d/m/Y') }}</a></td>
                                                <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}">{{ $purchase_order->supplierSnapshot->sup_name ?? $purchase_order->supplier->sup_name }}</a></td>
                                                <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}">{{ $purchase_order->status }}</a></td>
                                                <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}">{{ $purchase_order->user->name ?? '-' }}</a></td>
                                                <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}">{{ $purchase_order->updatedBy->name ?? ($purchase_order->user->name ?? '-') }}</a></td>
                                                <td class="text-center">
                                                    <span class="po-print-flag">
                                                        {{ $purchase_order->printed === 'Y' ? 'Y' : 'N' }}
                                                    </span>
                                                </td>
                                                
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No purchase orders found.</td>
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
</div>
