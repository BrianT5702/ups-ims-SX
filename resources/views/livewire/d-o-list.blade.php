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


                        <div class="table-responsive mt-3">
                            <style>
                                .table.do-list { table-layout: fixed; }
                                .table.do-list th:nth-child(1), .table.do-list td:nth-child(1) { width: 100px; white-space: nowrap; } /* Date */
                                .table.do-list th:nth-child(2), .table.do-list td:nth-child(2) { width: 120px; white-space: nowrap; } /* DO Number */
                                .table.do-list th:nth-child(3), .table.do-list td:nth-child(3) { width: 20%; } /* Customer Name - smaller */
                                .table.do-list th:nth-child(4), .table.do-list td:nth-child(4) { width: 100px; white-space: nowrap; } /* Amount */
                                .table.do-list th:nth-child(5), .table.do-list td:nth-child(5) { width: 120px; white-space: nowrap; } /* Salesman */
                                .table.do-list th:nth-child(6), .table.do-list td:nth-child(6) { width: 100px; white-space: nowrap; } /* Status */
                                .table.do-list th:nth-child(7), .table.do-list td:nth-child(7) { width: 120px; white-space: nowrap; } /* Created by */
                                .table.do-list th:nth-child(8), .table.do-list td:nth-child(8) { width: 80px; text-align: center; } /* Printed */
                                /* Removed Action column */
                                
                                /* Action buttons layout */
                                .action-buttons {
                                    display: flex;
                                    flex-direction: column;
                                    gap: 0.25rem;
                                    align-items: center;
                                }
                                .table.do-list td { overflow: hidden; text-overflow: ellipsis; }

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
                                            <td><a wire:navigate href="{{ route('delivery-orders.view', $delivery_order->id)}}">{{ Auth::user()->name }}</a></td>
                                            <td class="text-center">
                                                <span class="print-status {{ $delivery_order->printed === 'Y' ? 'printed-yes' : 'printed-no' }}">
                                                    {{ $delivery_order->printed }}
                                                </span>
                                            </td>
                                            
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No delivery orders found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{ $delivery_orders->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
