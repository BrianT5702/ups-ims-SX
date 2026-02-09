<div>
    <div class="container my-3" style="padding-left: 0.25rem; padding-right: 0.25rem;">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold fs-5 mb-0">
                            @if($filteredCustomer)
                                Quotation for {{ $filteredCustomer->cust_name }} - Total Quotation(s): {{$quotation_count}}
                            @else
                                Manage Quotation
                            @endif
                        </h5>
                        @if($filteredCustomer)
                        <div class="col-4 text-end">
                        <a href="javascript:history.back()" class="btn btn-primary btn-sm">Back</a>
                        </div>
                        @endif
                    </div>
                    <div class="card-body" style="padding-left: 0.5rem; padding-right: 0.5rem;">
                        <div class="row d-flex justify-content-end align-items-end">
                            @if(!$filteredCustomer)
                            <div class="d-flex justify-content-end">
                                <a wire:navigate href="{{route('quotations.add')}}" class="btn btn-primary">Add Quotation</a>
                            </div>
                            @endif
                        </div>
                    <div class="row align-items-end mb-3">
                            <div class="col-md-4">
                                <input type="text" wire:model.live.debounce.100ms="quotationSearchTerm" class="form-control form-control-sm rounded" placeholder="Search Quotation...">
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
                                    overflow-x: auto;
                                    overflow-y: visible;
                                }
                                
                                /* Scrollable table container - separate from pagination */
                                .quotation-list-scrollable {
                                    overflow-x: auto;
                                    overflow-y: visible;
                                    width: 100%;
                                    max-width: 100%;
                                    margin-bottom: 0;
                                    -webkit-overflow-scrolling: touch;
                                    scrollbar-width: thin;
                                    scrollbar-color: #cbd5e0 #f7fafc;
                                }
                                .quotation-list-scrollable::-webkit-scrollbar {
                                    height: 10px;
                                }
                                .quotation-list-scrollable::-webkit-scrollbar-track {
                                    background: #f7fafc;
                                    border-radius: 5px;
                                }
                                .quotation-list-scrollable::-webkit-scrollbar-thumb {
                                    background: #cbd5e0;
                                    border-radius: 5px;
                                }
                                .quotation-list-scrollable::-webkit-scrollbar-thumb:hover {
                                    background: #a0aec0;
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
                                }
                                
                                /* All cells - prevent wrapping and overlapping */
                                .table.quotation-list th,
                                .table.quotation-list td {
                                    white-space: nowrap;
                                    overflow: visible;
                                    text-overflow: clip;
                                    padding: 3px 6px; /* Slightly smaller padding for tighter rows */
                                    vertical-align: middle;
                                    border: 1px solid #dee2e6; /* Clearer border lines */
                                    font-size: 0.9rem; /* Smaller font size for quotation list */
                                }
                                
                                /* Table borders - clearer lines */
                                .table.quotation-list thead th {
                                    border-bottom: 2px solid #212529; /* Thicker header border */
                                    border-top: 1px solid #212529;
                                    border-left: 1px solid #dee2e6;
                                    border-right: 1px solid #dee2e6;
                                    background-color: #f8f9fa;
                                    font-weight: 600;
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
                                
                                /* Fixed pagination container - separate from scrollable table */
                                .quotation-list-pagination {
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
                            <div class="table-responsive quotation-list-scrollable" style="max-width: 100%; overflow-x: auto; margin-top: 0.5rem;">
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
</div>


