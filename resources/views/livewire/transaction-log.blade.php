<div class="container-fluid my-3">
    <div class="row">
        <div class="col-md-12 m-auto">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold fs-5 mb-0">
                        @if($filteredItem)
                            Transaction Log for {{ $filteredItem->item_name }} ({{ $filteredItem->item_code }})
                        @else
                            Manage Transactions
                        @endif
                    </h5>
                    @if($filteredItem)
                    <div class="col-4 text-end">

                    <a href="javascript:history.back()" class="btn btn-primary btn-sm">Back</a>
                    </div>
                    @endif
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="searchTerm" 
                                class="form-control rounded" 
                                placeholder="Search item code, name, or doc number"
                            >
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Source Type</label>
                            <select 
                                wire:model.live="sourceTypeFilter" 
                                class="form-control rounded"
                            >
                                <option value="">All Types</option>
                                @foreach($sourceTypeOptions as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input 
                                type="date" 
                                wire:model.live="startDate" 
                                class="form-control rounded"
                            >
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input 
                                type="date" 
                                wire:model.live="endDate" 
                                class="form-control rounded"
                            >
                        </div>
                        
                        <div class="col-md-1 d-flex align-items-end">
                            <button 
                                wire:click="clearFilters" 
                                class="btn btn-outline-secondary"
                            >
                                Reset
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-hover">
                            <thead>
                                <tr align="center">
                                    <th>#</th>
                                    <th>Date / Time</th>
                                    <th>Source Type</th>
                                    <th>Source Doc No</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <!-- <th>Qty On Hand</th> -->
                                    <th>Transaction Qty Before</th>
                                    <th>Transaction Qty</th>
                                    <th>Transaction Qty After</th>
                                    <th>Stock Movement</th>
                                    <th>Batch Number</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr align="center"  
                                        style="cursor: pointer;"
                                    >
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td wire:click="redirectToPage('{{ $transaction->source_type }}', {{ $transaction->id }})">{{ $transaction->source_type }}</td>
                                        <td wire:click="redirectToPage('{{ $transaction->source_type }}', {{ $transaction->id }})">{{ $transaction->source_doc_num }}</td>
                                        <td><a href="{{ route('items.view', ['item' => $transaction->item->id]) }}">{{ $transaction->item->item_code }}</a></td>
                                        <td><a href="{{ route('items.view', ['item' => $transaction->item->id]) }}">{{ $transaction->item->item_name }}</a></td>
                                        <!-- <td>{{ $transaction->qty_on_hand }}</td> -->
                                        <td>{{ $transaction->qty_before }}</td>
                                        <td>{{ $transaction->transaction_qty }}</td>
                                        <td>{{ $transaction->qty_after }}</td>
                                        <td>{{ $transaction->transaction_type }}</td>
                                        <td>{{ $transaction->batch->batch_num }}</td>
                                        <td>{{ $transaction->user->name }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">No transactions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>