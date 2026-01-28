<div>
    <div class="container my-3">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold fs-5 mb-0">
                            @if($filteredCustomer)
                                Delivery Order for {{ $filteredCustomer->cust_name }} - Total Order(s): {{$delivery_order_count}}
                            @else
                                Manage Delivery Order
                            @endif
                        </h5>
                        @if($filteredCustomer)
                        <div class="col-4 text-end">
                        <a href="javascript:history.back()" class="btn btn-primary btn-sm">Back</a>
                        </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row d-flex justify-content-end align-items-end">
                            @if(!$filteredCustomer)
                            <div class="d-flex justify-content-end">
                                <a wire:navigate href="{{route('delivery-orders.add')}}" class="btn btn-primary">Add DO</a>
                            </div>
                            @endif
                        </div>
                    <div class="row align-items-end mb-3">
                            <div class="col-md-4">
                                <input type="text" wire:model.live.debounce.100ms="doSearchTerm" class="form-control form-control-sm rounded" placeholder="Search DO...">
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


                        <div class="do-list-wrapper" style="position: relative;">
                            <style>
                                /* Wrapper to separate scrollable table from fixed pagination */
                                .do-list-wrapper {
                                    display: flex;
                                    flex-direction: column;
                                    width: 100%;
                                }
                                
                                /* Scrollable table container - separate from pagination */
                                .do-list-scrollable {
                                    overflow-x: auto;
                                    overflow-y: visible;
                                    width: 100%;
                                    margin-bottom: 0;
                                    -webkit-overflow-scrolling: touch;
                                    scrollbar-width: thin;
                                    scrollbar-color: #cbd5e0 #f7fafc;
                                }
                                .do-list-scrollable::-webkit-scrollbar {
                                    height: 10px;
                                }
                                .do-list-scrollable::-webkit-scrollbar-track {
                                    background: #f7fafc;
                                    border-radius: 5px;
                                }
                                .do-list-scrollable::-webkit-scrollbar-thumb {
                                    background: #cbd5e0;
                                    border-radius: 5px;
                                }
                                .do-list-scrollable::-webkit-scrollbar-thumb:hover {
                                    background: #a0aec0;
                                }
                                
                                /* Table styling - auto layout to prevent overlapping */
                                .table.do-list { 
                                    table-layout: auto; 
                                    width: max-content;
                                    min-width: 100%;
                                    border-collapse: collapse; /* Changed to collapse for clearer borders */
                                    border-spacing: 0;
                                    margin-bottom: 0;
                                    border: 1px solid #212529; /* Outer border - darker for clarity */
                                }
                                
                                /* All cells - prevent wrapping and overlapping */
                                .table.do-list th,
                                .table.do-list td {
                                    white-space: nowrap;
                                    overflow: visible;
                                    text-overflow: clip;
                                    padding: 4px 8px; /* Reduced padding for smaller row spacing */
                                    vertical-align: middle;
                                    border: 1px solid #dee2e6; /* Clearer border lines */
                                }
                                
                                /* Table borders - clearer lines */
                                .table.do-list thead th {
                                    border-bottom: 2px solid #212529; /* Thicker header border */
                                    border-top: 1px solid #212529;
                                    border-left: 1px solid #dee2e6;
                                    border-right: 1px solid #dee2e6;
                                    background-color: #f8f9fa;
                                    font-weight: 600;
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
                                    min-width: 100px;
                                    width: 100px;
                                } /* Date */
                                
                                .table.do-list th:nth-child(2), 
                                .table.do-list td:nth-child(2) { 
                                    min-width: 130px;
                                    width: 130px;
                                } /* DO Number */
                                
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
                                    min-width: 120px;
                                    width: 120px;
                                } /* Created by */
                                
                                .table.do-list th:nth-child(8), 
                                .table.do-list td:nth-child(8) { 
                                    min-width: 120px;
                                    width: 120px;
                                } /* Last edited by */
                                
                                .table.do-list th:nth-child(9), 
                                .table.do-list td:nth-child(9) { 
                                    min-width: 80px;
                                    width: 80px;
                                    text-align: center;
                                } /* Printed */
                                
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
                                
                                /* Fixed pagination container - separate from scrollable table */
                                .do-list-pagination {
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
                            <div class="table-responsive mt-3 do-list-scrollable">
                                <table class="table table-hover do-list">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>DO Number</th>
                                            <th>Customer Name</th>
                                            <th>Amount</th>
                                            <th>Salesman</th>
                                            <th>Status</th>
                                            <th>Created by</th>
                                            <th>Last edited by</th>
                                            <th>Printed</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($delivery_orders as $delivery_order)
                                            <tr>
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}"> {{ $delivery_order->created_at->format('d/m/Y') }}</a></td>
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}"> {{ $delivery_order->do_num }}</a></td>
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ $delivery_order->customerSnapshot->cust_name ?? $delivery_order->customer->cust_name }}</a></td>
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ $delivery_order->customerSnapshot->currency ?? $delivery_order->customer->currency ?? 'RM' }} {{ number_format($delivery_order->total_amount ?? 0, 2) }}</a></td>
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ $delivery_order->salesman ? strtoupper($delivery_order->salesman->username) : '-' }}</a></td>
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ $delivery_order->status ?? 'Completed' }}</a></td>
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ $delivery_order->user->name ?? '-' }}</a></td>
                                                <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ $delivery_order->updatedBy->name ?? ($delivery_order->user->name ?? '-') }}</a></td>
                                                <td class="text-center">
                                                    <span class="print-status {{ $delivery_order->printed === 'Y' ? 'printed-yes' : 'printed-no' }}">
                                                        {{ $delivery_order->printed }}
                                                    </span>
                                                </td>
                                                
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No delivery orders found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Fixed pagination area - separate from scrollable table -->
                            <div class="do-list-pagination">
                                {{ $delivery_orders->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
