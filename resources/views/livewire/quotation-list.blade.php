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
                            @php
                                $quotationListInitialColWidths = [90, 160, 280, 120, 120, 120, 120, 120, 90];
                            @endphp
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
                                
                                .table.quotation-list.list-col-resize-table { 
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
                                
                                .table.quotation-list.list-col-resize-table th,
                                .table.quotation-list.list-col-resize-table td {
                                    padding: var(--tx-log-cell-py) var(--tx-log-cell-px);
                                    vertical-align: middle;
                                    border: 1px solid #dee2e6;
                                }

                                .table.quotation-list tbody td {
                                    font-size: 0.78rem;
                                    line-height: 1.28;
                                }
                                
                                .table.quotation-list thead th {
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

                                .table.quotation-list .list-col-resize-handle::after {
                                    content: '';
                                    position: absolute;
                                    top: 0;
                                    bottom: 0;
                                    right: 3px;
                                    width: 1px;
                                    background: transparent;
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
                                <table class="table table-hover quotation-list list-col-resize-table" data-list-col-storage-key="quotationList" data-list-col-variant="default">
                                    <colgroup>
                                        @foreach($quotationListInitialColWidths as $idx => $wPx)
                                            <col data-list-col-index="{{ $idx }}" style="width: {{ $wPx }}px;">
                                        @endforeach
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th><span class="list-th-label">Date</span><span class="list-col-resize-handle" data-list-col-index="0" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Quotation Number</span><span class="list-col-resize-handle" data-list-col-index="1" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Customer Name</span><span class="list-col-resize-handle" data-list-col-index="2" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Amount</span><span class="list-col-resize-handle" data-list-col-index="3" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Salesman</span><span class="list-col-resize-handle" data-list-col-index="4" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Status</span><span class="list-col-resize-handle" data-list-col-index="5" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Created by</span><span class="list-col-resize-handle" data-list-col-index="6" title="Drag to resize"></span></th>
                                            <th><span class="list-th-label">Last edited by</span><span class="list-col-resize-handle" data-list-col-index="7" title="Drag to resize"></span></th>
                                            <th class="text-center"><span class="list-th-label">Print</span><span class="list-col-resize-handle" data-list-col-index="8" title="Drag to resize"></span></th>
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
    @include('partials.list-table-column-resize')
</div>


