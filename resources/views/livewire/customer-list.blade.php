<div class="list-page-unified-density">
    <div class="container my-2" style="padding-left: 0.25rem; padding-right: 0.25rem;">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header transaction-log-page-header d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="min-w-0 flex-grow-1">
                            <h5 class="fw-bold mb-0 list-page-unified-title">Manage Customer</h5>
                        </div>
                        <div class="d-flex align-items-start gap-2 flex-shrink-0">
                            <a wire:navigate href="{{ route('customers.add') }}" class="btn btn-primary btn-sm">Add Customer</a>
                        </div>
                    </div>

                    <div class="card-body px-2 pb-3 transaction-log-card-body">
                        <div class="row mb-1 g-2 align-items-end list-page-unified-filters">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" wire:model.live.debounce.100ms="customerSearchTerm" class="form-control form-control-sm rounded" placeholder="Search customer name or account...">
                            </div>

                        </div>
                        <div class="row transaction-log-reset-toolbar mb-1">
                            <div class="col-12 d-flex justify-content-end py-0">
                                <button wire:click="clearFilters" type="button" class="btn btn-outline-secondary btn-sm transaction-log-reset-btn">Reset</button>
                            </div>
                        </div>

                        <div class="customer-list-wrapper" style="position: relative;">
                            @php
                                $customerListInitialColWidths = [52, 110, 240, 120, 180, 120, 110];
                            @endphp
                            <style>
                                /* Wrapper to separate scrollable table from fixed pagination */
                                .customer-list-wrapper {
                                    display: flex;
                                    flex-direction: column;
                                    width: 100%;
                                    max-width: 100%;
                                    overflow: hidden;
                                }
                                
                                /* Constrain Bootstrap table-responsive within wrapper */
                                .customer-list-wrapper .table-responsive {
                                    max-width: 100%;
                                }

                                .customer-list-scrollable {
                                    width: 100%;
                                    max-width: 100%;
                                    margin-bottom: 0;
                                }
                                
                                /* Table borders (layout / clip / resize: partial .list-col-resize-table) */
                                .table.customer-list.list-col-resize-table { 
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
                                
                                .table.customer-list.list-col-resize-table th,
                                .table.customer-list.list-col-resize-table td {
                                    padding: var(--tx-log-cell-py) var(--tx-log-cell-px);
                                    vertical-align: middle;
                                    border: 1px solid #dee2e6;
                                }

                                .table.customer-list tbody td {
                                    font-size: 0.78rem;
                                    line-height: 1.28;
                                }
                                
                                .table.customer-list thead th {
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

                                .table.customer-list .list-col-resize-handle::after {
                                    content: '';
                                    position: absolute;
                                    top: 0;
                                    bottom: 0;
                                    right: 3px;
                                    width: 1px;
                                    background: transparent;
                                }
                                
                                .table.customer-list thead th:first-child {
                                    border-left: 1px solid #212529;
                                }
                                
                                .table.customer-list thead th:last-child {
                                    border-right: 1px solid #212529;
                                }
                                
                                .table.customer-list tbody tr {
                                    border-bottom: 1px solid #dee2e6;
                                }
                                
                                .table.customer-list tbody tr:hover {
                                    background-color: #f8f9fa;
                                }
                                
                                .table.customer-list tbody td:first-child {
                                    border-left: 1px solid #212529;
                                }
                                
                                .table.customer-list tbody td:last-child {
                                    border-right: 1px solid #212529;
                                }
                                
                                .table.customer-list tbody tr:last-child td {
                                    border-bottom: 1px solid #212529;
                                }
                                
                                .table.customer-list .customer-list-sort-btn {
                                    font-size: 0.82rem;
                                    line-height: 1.3;
                                }
                                .table.customer-list.list-col-resize-table tbody td:last-child {
                                    overflow: visible;
                                    text-overflow: clip;
                                }
                                .table.customer-list .action-buttons {
                                    display: flex;
                                    flex-wrap: nowrap;
                                    gap: 0.25rem;
                                    align-items: center;
                                }
                                .table.customer-list .action-buttons .btn {
                                    font-size: 0.78rem;
                                }
                            </style>
                            
                            <!-- Scrollable table area -->
                            <div class="table-responsive customer-list-scrollable list-sticky-table-scroll">
                                <table class="table table-hover customer-list list-col-resize-table" data-list-col-storage-key="customerList" data-list-col-variant="default">
                                    <colgroup>
                                        @foreach($customerListInitialColWidths as $idx => $wPx)
                                            <col data-list-col-index="{{ $idx }}" style="width: {{ $wPx }}px;">
                                        @endforeach
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th class="text-center"><span class="list-th-label">No</span><span class="list-col-resize-handle" data-list-col-index="0" title="Drag to resize"></span></th>
                                            <th>
                                                <span class="list-th-label">
                                                    <button type="button" wire:click="sortBy('account')" class="btn btn-sm p-0 border-0 bg-transparent text-dark text-decoration-none fw-semibold customer-list-sort-btn">
                                                        Account{{ $sortColumn === 'account' ? ($sortOrder === 'asc' ? ' ↑' : ' ↓') : '' }}
                                                    </button>
                                                </span>
                                                <span class="list-col-resize-handle" data-list-col-index="1" title="Drag to resize"></span>
                                            </th>
                                            <th>
                                                <span class="list-th-label">
                                                    <button type="button" wire:click="sortBy('cust_name')" class="btn btn-sm p-0 border-0 bg-transparent text-dark text-decoration-none fw-semibold customer-list-sort-btn">
                                                        Name{{ $sortColumn === 'cust_name' ? ($sortOrder === 'asc' ? ' ↑' : ' ↓') : '' }}
                                                    </button>
                                                </span>
                                                <span class="list-col-resize-handle" data-list-col-index="2" title="Drag to resize"></span>
                                            </th>
                                            <th><span class="list-th-label">Phone</span><span class="list-col-resize-handle" data-list-col-index="3" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Email</span><span class="list-col-resize-handle" data-list-col-index="4" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Salesperson</span><span class="list-col-resize-handle" data-list-col-index="5" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Action</span><span class="list-col-resize-handle" data-list-col-index="6" title="Drag to resize"></span></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($customers as $customer)
                                            <tr>
                                                <td class="text-center text-muted">{{ ($customers->firstItem() ?? 0) + $loop->index }}</td>
                                                <td><a wire:navigate href="{{ route('customers.view', $customer->id) }}">{{ $customer->account ?: '-' }}</a></td>
                                                <td><a wire:navigate href="{{ route('customers.view', $customer->id) }}">{{ $customer->cust_name }}</a></td>
                                                <td><a wire:navigate href="{{ route('customers.view', $customer->id) }}">{{ $customer->phone_num }}</a></td>
                                                <td><a wire:navigate href="{{ route('customers.view', $customer->id) }}">{{ $customer->email }}</a></td>
                                                <td>{{ $customer->salesman ? $customer->salesman->name : 'N/A' }}</td>
                                                <td>
                                                    <div class="action-buttons">
                                                        @can('Manage DO')
                                                            <button wire:click.prevent="showCustomerDO({{ $customer->id }})" class="btn btn-info btn-sm" title="Delivery orders">
                                                                <i class="fa-solid fa-clock-rotate-left"></i>
                                                            </button>
                                                        @endcan
                                                        <a href="{{ route('customers.edit', $customer->id) }}" wire:navigate class="btn btn-success btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                                        <button wire:confirm="Are you sure you want to delete?" wire:click="deleteCustomer({{ $customer->id }})" type="button" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No customers found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Fixed pagination area - separate from scrollable table -->
                            <div class="customer-list-pagination d-flex justify-content-between align-items-center flex-wrap">
                                <div class="small text-muted">
                                    @php
                                        $from = $customers->firstItem() ?? 0;
                                        $to = $customers->lastItem() ?? 0;
                                        $total = $customers->total();
                                    @endphp
                                    Showing {{ $from }} to {{ $to }} of {{ $total }} results
                                </div>
                                <div>
                                    @if ($customers->hasPages())
                                        {{ $customers->links() }}
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
