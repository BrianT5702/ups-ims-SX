<div class="list-page-unified-density">
    <div class="container my-2" style="padding-left: 0.25rem; padding-right: 0.25rem;">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header transaction-log-page-header d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="min-w-0 flex-grow-1">
                            @if($filteredCustomer)
                                <div class="text-muted fw-semibold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.08em;">Quotations</div>
                                <h5 class="fw-bold mb-0 list-page-unified-title mt-1">{{ $filteredCustomer->cust_name }}</h5>
                                <p class="small text-muted mb-0 mt-1">Total quotation(s): {{ $quotation_count }}</p>
                            @else
                                <h5 class="fw-bold mb-0 list-page-unified-title">Quotation List</h5>
                            @endif
                        </div>
                        <div class="d-flex align-items-start gap-2 flex-shrink-0">
                            @if($filteredCustomer)
                                <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">Back</a>
                            @else
                                <a wire:navigate href="{{ route('quotations.add') }}" class="btn btn-primary btn-sm">Add Quotation</a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body px-2 pb-3 transaction-log-card-body">
                        <div class="row mb-1 g-2 align-items-end list-page-unified-filters">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" wire:model.live.debounce.100ms="quotationSearchTerm" class="form-control form-control-sm rounded" placeholder="Search quotation or customer...">
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


                        <div class="quotation-list-wrapper" style="position: relative;">
                            <style>
                                /* Wrapper to separate scrollable table from fixed pagination */
                                .quotation-list-wrapper {
                                    display: flex;
                                    flex-direction: column;
                                    width: 100%;
                                    max-width: 100%;
                                    overflow: hidden;
                                }
                                
                                /* Constrain Bootstrap table-responsive within wrapper */
                                .quotation-list-wrapper .table-responsive {
                                    max-width: 100%;
                                }

                                .quotation-list-scrollable {
                                    width: 100%;
                                    max-width: 100%;
                                    margin-bottom: 0;
                                }
                                
                                /* Table styling - auto layout to prevent overlapping */
                                .table.quotation-list { 
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
                                .table.quotation-list th,
                                .table.quotation-list td {
                                    white-space: nowrap;
                                    overflow: visible;
                                    text-overflow: clip;
                                    padding: var(--tx-log-cell-py) var(--tx-log-cell-px);
                                    vertical-align: middle;
                                    border: 1px solid #dee2e6; /* Clearer border lines */
                                }

                                .table.quotation-list tbody td {
                                    font-size: 0.78rem;
                                    line-height: 1.28;
                                }
                                
                                /* Table borders - clearer lines */
                                .table.quotation-list thead th {
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
                                
                                .table.quotation-list thead th:first-child {
                                    border-left: 1px solid #212529;
                                }
                                
                                .table.quotation-list thead th:last-child {
                                    border-right: 1px solid #212529;
                                }
                                
                                .table.quotation-list tbody tr {
                                    border-bottom: 1px solid #dee2e6;
                                }
                                
                                .table.quotation-list tbody tr:hover {
                                    background-color: #f8f9fa;
                                }
                                
                                .table.quotation-list tbody td:first-child {
                                    border-left: 1px solid #212529;
                                }
                                
                                .table.quotation-list tbody td:last-child {
                                    border-right: 1px solid #212529;
                                }
                                
                                .table.quotation-list tbody tr:last-child td {
                                    border-bottom: 1px solid #212529;
                                }
                                
                                /* Column widths - fixed minimum widths to prevent overlap */
                                .table.quotation-list th:nth-child(1), 
                                .table.quotation-list td:nth-child(1) { 
                                    min-width: 90px;
                                    width: 90px;
                                } /* Date */
                                
                                .table.quotation-list th:nth-child(2), 
                                .table.quotation-list td:nth-child(2) { 
                                    min-width: 160px;
                                    width: 160px;
                                } /* Quotation Number */
                                
                                .table.quotation-list th:nth-child(3), 
                                .table.quotation-list td:nth-child(3) { 
                                    min-width: 250px;
                                    width: auto; /* Allow expansion for long customer names */
                                } /* Customer Name - full text, no truncation */
                                
                                .table.quotation-list th:nth-child(4), 
                                .table.quotation-list td:nth-child(4) { 
                                    min-width: 120px;
                                    width: 120px;
                                } /* Amount */
                                
                                .table.quotation-list th:nth-child(5), 
                                .table.quotation-list td:nth-child(5) { 
                                    min-width: 120px;
                                    width: 120px;
                                } /* Salesman */
                                
                                .table.quotation-list th:nth-child(6), 
                                .table.quotation-list td:nth-child(6) { 
                                    min-width: 120px;
                                    width: 120px;
                                } /* Status */
                                
                                .table.quotation-list th:nth-child(7), 
                                .table.quotation-list td:nth-child(7) { 
                                    min-width: 120px;
                                    width: 120px;
                                } /* Created by */
                                
                                .table.quotation-list th:nth-child(8), 
                                .table.quotation-list td:nth-child(8) { 
                                    min-width: 120px;
                                    width: 120px;
                                } /* Last edited by */
                                
                                .table.quotation-list th:nth-child(9), 
                                .table.quotation-list td:nth-child(9) { 
                                    min-width: 90px;
                                    width: 90px;
                                    text-align: center;
                                } /* Printed */
                                
                                /* Ensure links don't cause wrapping */
                                .table.quotation-list td a {
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
                                .quotation-print-flag {
                                    font-weight: 500;
                                }
                                .quotation-print-flag.printed-yes {
                                    color: #0d6efd; /* Bootstrap primary blue */
                                }
                                .quotation-print-flag.printed-no {
                                    color: #dc3545; /* Bootstrap danger red */
                                }
                            </style>
                            
                            <!-- Scrollable table area -->
                            <div class="table-responsive quotation-list-scrollable list-sticky-table-scroll">
                                <table class="table table-hover quotation-list">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Quotation Number</th>
                                            <th>Customer Name</th>
                                            <th>Amount</th>
                                            <th>Salesman</th>
                                            <th>Status</th>
                                            <th>Created by</th>
                                            <th>Last edited by</th>
                                            <th>Print</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($quotations as $quotation)
                                            <tr>
                                                <td><a wire:navigate href="{{ route('quotations.view', $quotation->id)}}"> {{ $quotation->created_at->format('d/m/Y') }}</a></td>
                                                <td><a wire:navigate href="{{ route('quotations.view', $quotation->id)}}"> {{ $quotation->quotation_num }}</a></td>
                                                <td><a wire:navigate href="{{ route('quotations.view', $quotation->id)}}">{{ $quotation->customerSnapshot->cust_name ?? $quotation->customer->cust_name }}</a></td>
                                                <td><a wire:navigate href="{{ route('quotations.view', $quotation->id)}}">{{ $quotation->customerSnapshot->currency ?? $quotation->customer->currency ?? 'RM' }} {{ number_format($quotation->total_amount ?? 0, 2) }}</a></td>
                                                <td><a wire:navigate href="{{ route('quotations.view', $quotation->id)}}">{{ $quotation->salesman->name ?? '-' }}</a></td>
                                                <td><a wire:navigate href="{{ route('quotations.view', $quotation->id)}}">{{ $quotation->status ?? 'Save to Draft' }}</a></td>
                                                <td><a wire:navigate href="{{ route('quotations.view', $quotation->id)}}">{{ $quotation->user->name ?? '-' }}</a></td>
                                                <td><a wire:navigate href="{{ route('quotations.view', $quotation->id)}}">{{ $quotation->updatedBy->name ?? ($quotation->user->name ?? '-') }}</a></td>
                                                <td class="text-center">
                                                    <span class="quotation-print-flag {{ $quotation->printed === 'Y' ? 'printed-yes' : 'printed-no' }}">
                                                        {{ $quotation->printed === 'Y' ? 'Y' : 'N' }}
                                                    </span>
                                                </td>
                                                
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No quotations found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Fixed pagination area - separate from scrollable table -->
                            <div class="quotation-list-pagination d-flex justify-content-between align-items-center flex-wrap">
                                <div class="small text-muted">
                                    @php
                                        $from = $quotations->firstItem() ?? 0;
                                        $to = $quotations->lastItem() ?? 0;
                                        $total = $quotations->total();
                                    @endphp
                                    Showing {{ $from }} to {{ $to }} of {{ $total }} results
                                </div>
                                <div>
                                    @if ($quotations->hasPages())
                                        {{ $quotations->links() }}
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


