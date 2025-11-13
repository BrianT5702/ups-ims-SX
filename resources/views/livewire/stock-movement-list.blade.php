<div>
    <div class="container my-3">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold fs-5 mb-0">Manage Stock Movement</h5>
                    </div>
                    <div class="card-body">
                        <div class="row d-flex justify-content-end align-items-end">
                            <div class="d-flex justify-content-end">
                                <a wire:navigate href="{{route('stock-movements.add')}}" class="btn btn-primary">Add Movement</a>
                            </div>
                        </div>
                        
                        <div class="row align-items-end mb-3">
                            <div class="col-md-3">
                                <input type="text" wire:model.live.debounce.100ms="search" class="form-control form-control-sm rounded" placeholder="Search movements...">
                            </div>
                            <div class="col-md-2">
                                <select wire:model.live="movementTypeFilter" class="form-control rounded">
                                    <option value="">All Movement Types</option>
                                    <option value="In">In</option>
                                    <option value="Out">Out</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">From Date</label>
                                <input type="date" wire:model.live="startDate" class="form-control rounded">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">To Date</label>
                                <input type="date" wire:model.live="endDate" class="form-control rounded">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button wire:click="resetFilters" class="btn btn-outline-secondary">
                                    Reset
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Date / Time</th>
                                        <th>Movement Type</th>
                                        <th>Reference No</th>
                                        <th>User</th>
                                        <th>Total Qty</th>
                                        <th>Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stockMovements as $movement)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><a wire:navigate href="{{ route('stock-movements.view', $movement->id)}}">{{ $movement->movement_date->format('Y-m-d H:i:s') }}</a></td>
                                            <td><a wire:navigate href="{{ route('stock-movements.view', $movement->id)}}">
                                                <span class="badge {{ $movement->movement_type === 'In' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $movement->movement_type }}
                                                </span>
                                            </a></td>
                                            <td><a wire:navigate href="{{ route('stock-movements.view', $movement->id)}}">{{ $movement->reference_no ?? 'N/A' }}</a></td>
                                            <td><a wire:navigate href="{{ route('stock-movements.view', $movement->id)}}">{{ $movement->user->name }}</a></td>
                                            <td><a wire:navigate href="{{ route('stock-movements.view', $movement->id)}}">
                                                <strong>{{ $movement->items->sum('quantity') }}</strong> 
                                                <small class="text-muted">({{ $movement->items->count() }} items)</small>
                                            </a></td>
                                            <td><a wire:navigate href="{{ route('stock-movements.view', $movement->id)}}">{{ Str::limit($movement->remarks, 50) }}</a></td>
                                            <td class="align-item-center">
                                                @can('Manage Stock Movement')
                                                    <a href="{{ route('stock-movements.edit', $movement) }}" 
                                                       class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                <a href="{{ route('print.stock-movement.preview', $movement) }}" 
                                                   class="btn btn-info btn-sm" title="Print">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                @can('Manage Stock Movement')
                                                    <button wire:click="deleteStockMovement({{ $movement->id }})" 
                                                            class="btn btn-danger btn-sm" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this stock movement?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No stock movements found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{ $stockMovements->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
