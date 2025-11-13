<div class="container-fluid my-3">
    <div class="row">
        <div class="col-md-10 m-auto">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="fw-bold fs-5 mb-0">IBC Tank Chemical Stock Level</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form wire:submit.prevent="addChemicalStock" class="row g-3">
                                <div class="col-md-4">
                                    <label for="do_num" class="form-label">DO Number <span class="text-danger">*</span></label>
                                    @error('do_num')
                                        <p class="text-danger small mt-1">{{ $message }}</p>
                                    @enderror
                                    <input type="text" wire:model="do_num" class="form-control form-control-sm rounded" id="do_num">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="batch_no" class="form-label">Batch Number <span class="text-danger">*</span></label>
                                    @error('batch_no')
                                        <p class="text-danger small mt-1">{{ $message }}</p>
                                    @enderror
                                    <input type="text" wire:model="batch_no" class="form-control form-control-sm rounded" id="batch_no">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="date" class="form-label">Loading Date <span class="text-danger">*</span></label>
                                    @error('date')
                                        <p class="text-danger small mt-1">{{ $message }}</p>
                                    @enderror
                                    <input type="date" wire:model="date" class="form-control form-control-sm rounded" id="date">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="che_code" class="form-label">Chemical Code <span class="text-danger">*</span></label>
                                    @error('che_code')
                                        <p class="text-danger small mt-1">{{ $message }}</p>
                                    @enderror
                                    <input type="text" wire:model="che_code" class="form-control form-control-sm rounded" id="che_code">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="qty" class="form-label">Quantity (IBCT) <span class="text-danger">*</span></label>
                                    @error('qty')
                                        <p class="text-danger small mt-1">{{ $message }}</p>
                                    @enderror
                                    <input type="number" min="1" wire:model="qty" class="form-control form-control-sm rounded" id="qty">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                    @error('expiry_date')
                                        <p class="text-danger small mt-1">{{ $message }}</p>
                                    @enderror
                                    <input type="date" wire:model="expiry_date" class="form-control form-control-sm rounded" id="expiry_date">
                                </div>
                                
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Add Stock Record</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row align-items-end mb-3">
                        <div class="col-md-3">
                            <input type="text" wire:model.live.debounce.300ms="searchTerm" class="form-control form-control-sm rounded" placeholder="Search DO, batch, chemical...">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="startDate" class="form-label">From Date</label>
                            <input type="date" wire:model.live="startDate" class="form-control form-control-sm rounded" id="startDate">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="endDate" class="form-label">To Date</label>
                            <input type="date" wire:model.live="endDate" class="form-control form-control-sm rounded" id="endDate">
                        </div>
                        
                        <div class="col-md-3">
                            <button wire:click="clearFilters" class="btn btn-secondary btn-sm">Clear Filters</button>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>DO Number</th>
                                    <th>Batch Number</th>
                                    <th>Chemical Code</th>
                                    <th>Loading Date</th>
                                    <th>Quantity (IBCT)</th>
                                    <th>Expiry Date</th>
                                    <th>Days Until Expiry</th>
                                    <th>PIC</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($chemicalStocks as $stock)
                                    <tr>
                                        <td>{{ $stock->id }}</td>
                                        <td>
                                            @if($editingId === $stock->id)
                                                <input type="text" class="form-control form-control-sm" wire:model.defer="edit_do_num">
                                            @else
                                                {{ $stock->do_num }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($editingId === $stock->id)
                                                <input type="text" class="form-control form-control-sm" wire:model.defer="edit_batch_no">
                                            @else
                                                {{ $stock->batch_no }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($editingId === $stock->id)
                                                <input type="text" class="form-control form-control-sm" wire:model.defer="edit_che_code">
                                            @else
                                                {{ $stock->che_code }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($editingId === $stock->id)
                                                <input type="date" class="form-control form-control-sm" wire:model.defer="edit_date">
                                            @else
                                                {{ date('d-m-Y', strtotime($stock->date)) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($editingId === $stock->id)
                                                <input type="number" min="1" class="form-control form-control-sm" wire:model.defer="edit_qty">
                                            @else
                                                {{ $stock->qty }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($editingId === $stock->id)
                                                <input type="date" class="form-control form-control-sm" wire:model.defer="edit_expiry_date">
                                            @else
                                                @if($stock->expiry_date)
                                                    {{ date('d-m-Y', strtotime($stock->expiry_date)) }}
                                                @else
                                                    -
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if($stock->expiry_date)
                                                @php
                                                    $daysUntilExpiry = \Carbon\Carbon::parse($stock->expiry_date)->diffInDays(\Carbon\Carbon::now(), false);
                                                    $colorClass = $daysUntilExpiry > 0 ? 'text-danger' : ($daysUntilExpiry >= -7 ? 'text-warning' : 'text-success');
                                                @endphp
                                                <span class="{{ $colorClass }}">
                                                    @if($daysUntilExpiry > 0)
                                                        Expired
                                                    @elseif($daysUntilExpiry >= -7)
                                                        {{ abs(round($daysUntilExpiry)) }} days remaining
                                                    @else
                                                        {{ abs(round($daysUntilExpiry)) }} days remaining
                                                    @endif
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $stock->user->name ?? 'Unknown' }}</td>
                                        <td>
                                            @if($editingId === $stock->id)
                                                <button class="btn btn-success btn-sm" wire:click.prevent="saveEdit({{ $stock->id }})"><i class="fas fa-check"></i></button>
                                                <button class="btn btn-secondary btn-sm" wire:click.prevent="cancelEdit"><i class="fas fa-times"></i></button>
                                            @else
                                                <button class="btn btn-success btn-sm" wire:click.prevent="startEdit({{ $stock->id }})"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-danger btn-sm" wire:confirm="Are you sure you want to delete?" wire:click.prevent="deleteRecord({{ $stock->id }})"><i class="fas fa-trash-alt"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No chemical stock records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $chemicalStocks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>