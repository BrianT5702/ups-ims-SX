<div>
    <div class="container my-3">
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
                    <div class="card-body">
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


                        <div class="table-responsive mt-3">
                            <style>
                                .table.quotation-list { table-layout: fixed; }
                                .table.quotation-list th:nth-child(1), .table.quotation-list td:nth-child(1) { width: 100px; white-space: nowrap; } /* Date */
                                .table.quotation-list th:nth-child(2), .table.quotation-list td:nth-child(2) { width: 160px; white-space: nowrap; } /* Quotation Number */
                                .table.quotation-list th:nth-child(3), .table.quotation-list td:nth-child(3) { width: 20%; } /* Customer Name - smaller */
                                .table.quotation-list th:nth-child(4), .table.quotation-list td:nth-child(4) { width: 100px; white-space: nowrap; } /* Amount */
                                .table.quotation-list th:nth-child(5), .table.quotation-list td:nth-child(5) { width: 120px; white-space: nowrap; } /* Salesman */
                                .table.quotation-list th:nth-child(6), .table.quotation-list td:nth-child(6) { width: 100px; white-space: nowrap; } /* Status */
                                .table.quotation-list th:nth-child(7), .table.quotation-list td:nth-child(7) { width: 120px; white-space: nowrap; } /* Created by */
                                .table.quotation-list th:nth-child(8), .table.quotation-list td:nth-child(8) { width: 80px; text-align: center; } /* Printed */
                                
                                .table.quotation-list th, .table.quotation-list td { overflow: hidden; text-overflow: ellipsis; }

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
                                        <th>Printed</th>
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
                                            <td class="text-center">
                                                <span class="print-status {{ $quotation->printed === 'Y' ? 'printed-yes' : 'printed-no' }}">
                                                    {{ $quotation->printed }}
                                                </span>
                                            </td>
                                            
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No quotations found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{ $quotations->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


