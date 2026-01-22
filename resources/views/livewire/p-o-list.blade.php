<div>
    <div class="container my-3">
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

                    <div class="card-body">
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

                        <!-- Table for Purchase Orders -->
                        <div class="table-responsive mt-3">
                            <style>
                                /* Fixed column widths with Supplier Name being the largest */
                                .table.po-list { 
                                    table-layout: fixed;
                                    width: 100%;
                                }
                                .table.po-list td {
                                    white-space: nowrap;
                                    overflow: hidden;
                                    text-overflow: ellipsis;
                                }
                                .table.po-list th:nth-child(1), .table.po-list td:nth-child(1) { width: 100px; } /* Date */
                                .table.po-list th:nth-child(2), .table.po-list td:nth-child(2) { width: 120px; } /* PO Number */
                                .table.po-list th:nth-child(3), .table.po-list td:nth-child(3) { width: 35%; } /* Supplier Name - largest */
                                .table.po-list th:nth-child(4), .table.po-list td:nth-child(4) { width: 100px; } /* Status */
                                .table.po-list th:nth-child(5), .table.po-list td:nth-child(5) { width: 120px; } /* Created by */
                                .table.po-list th:nth-child(6), .table.po-list td:nth-child(6) { width: 80px; text-align: center; } /* Printed */
                                /* Removed Action column */

                                /* Action buttons layout */
                                .action-buttons {
                                    display: flex;
                                    flex-direction: column;
                                    gap: 0.25rem;
                                    align-items: center;
                                }
                                
                                /* Print status colors */
                                .print-status {
                                    font-weight: 500;
                                }
                                .print-status.printed-yes {
                                    color: #0d6efd; /* Bootstrap primary blue */
                                }
                                .print-status.printed-no {
                                    color: #dc3545; /* Bootstrap danger red */
                                }
                            </style>
                            <table class="table table-hover po-list">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>PO Number</th>
                                        <th>Supplier Name</th>
                                        <th>Status</th>
                                        <th>Created by</th>
                                        <th>Printed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($purchase_orders as $purchase_order)
                                        <tr>
                                            <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}">{{ $purchase_order->created_at->format('d/m/Y') }}</a></td>
                                            <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}">{{ $purchase_order->po_num }}</a></td>
                                            <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}">{{ $purchase_order->supplierSnapshot->sup_name ?? $purchase_order->supplier->sup_name }}</a></td>
                                            <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}">{{ $purchase_order->status }}</a></td>
                                            <td><a wire:navigate href="{{ route('purchase-orders.view', $purchase_order->id)}}">{{ $purchase_order->user->name ?? '-' }}</a></td>
                                            <td class="text-center">
                                                <span class="print-status {{ $purchase_order->printed === 'Y' ? 'printed-yes' : 'printed-no' }}">
                                                    {{ $purchase_order->printed }}
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
                            {{ $purchase_orders->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
