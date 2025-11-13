<div class="container-fluid my-3">
    <div class="row">
        <div class="col-md-10 m-auto">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="fw-bold fs-5 mb-0">Chemical Loading and Unloading</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Select Chemical Type</label>
                                <div class="btn-group w-100">
                                    <button type="button" wire:click="$set('selectedChemical', 'Pentane')" class="btn {{ $selectedChemical == 'Pentane' ? 'btn-primary' : 'btn-outline-secondary' }}">Pentane</button>
                                    <button type="button" wire:click="$set('selectedChemical', 'PH1139')" class="btn {{ $selectedChemical == 'PH1139' ? 'btn-primary' : 'btn-outline-secondary' }}">Polyol (PH1139)</button>
                                    <button type="button" wire:click="$set('selectedChemical', 'KH1250')" class="btn {{ $selectedChemical == 'KH1250' ? 'btn-primary' : 'btn-outline-secondary' }}">Isocyanate (KH1250)</button>
                                </div>
                            </div>

                            @if($selectedChemical)
                            <form wire:submit.prevent="addLoadingUnloading" class="row g-3">
                                <div class="col-md-4">
                                    <label for="tank_id" class="form-label">Tank Number <span class="text-danger">*</span></label>
                                    @error('tank_id')
                                        <p class="text-danger small mt-1">{{ $message }}</p>
                                    @enderror
                                    <select wire:model="tank_id" class="form-select form-select-sm rounded" id="tank_id">
                                        <option value="">Select Tank</option>
                                        @foreach($tankOptions as $tank)
                                            <option value="{{ $tank }}">{{ $tank }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                    @error('date')
                                        <p class="text-danger small mt-1">{{ $message }}</p>
                                    @enderror
                                    <input type="date" wire:model="date" class="form-control form-control-sm rounded" id="date">
                                </div>
                                
                                <div class="col-md-4 px-2 py-4">
                                    <div class="card bg-light border">
                                        <div class="card-body p-2">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" wire:model="isFollowDO" id="isFollowDO" style="transform: scale(1.4);">
                                                <label class="form-check-label fw-bold fs-5" for="isFollowDO">Follow DO</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Time <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            @error('start_time')
                                                <p class="text-danger small mt-1">{{ $message }}</p>
                                            @enderror
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Start</span>
                                                <input type="time" wire:model="start_time" class="form-control form-control-sm rounded" id="start_time">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            @error('stop_time')
                                                <p class="text-danger small mt-1">{{ $message }}</p>
                                            @enderror
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">End</span>
                                                <input type="time" wire:model="stop_time" class="form-control form-control-sm rounded" id="stop_time">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Chemical/Gas % <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            @error('che_before')
                                                <p class="text-danger small mt-1">{{ $message }}</p>
                                            @enderror
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Before</span>
                                                <input type="number" step="0.01" min="0" max="100" wire:model="che_before" class="form-control form-control-sm rounded" id="che_before">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            @error('che_after')
                                                <p class="text-danger small mt-1">{{ $message }}</p>
                                            @enderror
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">After</span>
                                                <input type="number" step="0.01" min="0" max="100" wire:model="che_after" class="form-control form-control-sm rounded" id="che_after">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">Add Record</button>
                                </div>
                            </form>
                            @else
                            <div class="alert alert-info">
                                Please select a chemical type to proceed.
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="row align-items-end mb-3">
                        <div class="col-md-3">
                            <input type="text" wire:model.live.debounce.300ms="searchTerm" class="form-control form-control-sm rounded" placeholder="Search tank, chemical...">
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

                    <div class="mb-3">
                        <div class="btn-group" role="group">
                            <button type="button" wire:click="setFilterChemical('')" class="btn {{ $filterChemical == '' ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm">All</button>
                            <button type="button" wire:click="setFilterChemical('Pentane')" class="btn {{ $filterChemical == 'Pentane' ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm">Pentane</button>
                            <button type="button" wire:click="setFilterChemical('PH1139')" class="btn {{ $filterChemical == 'PH1139' ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm">Polyol</button>
                            <button type="button" wire:click="setFilterChemical('KH1250')" class="btn {{ $filterChemical == 'KH1250' ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm">Isocyanate</button>
                        </div>
                    </div>

                    <style>
                        .table-bordered-groups th, .table-bordered-groups td {
                            vertical-align: middle;
                        }
                        .chemical-percentage {
                            background-color: #f8f9fa;
                            border-left: 1px solid #dee2e6;
                            border-right: 1px solid #dee2e6;
                        }
                        .chemical-percentage-header {
                            background-color: #e9ecef;
                            border-bottom: 2px solid #dee2e6;
                        }
                        .chemical-percentage-subheader {
                            background-color: #f1f3f5;
                            font-weight: normal;
                            border-bottom: 1px solid #dee2e6;
                        }
                        .total-value {
                            font-weight: bold;
                            background-color: #f0f0f0;
                        }
                        .follow-do-yes {
                            background-color: #d4edda;
                            color: #155724;
                            font-weight: bold;
                            padding: 3px 8px;
                            border-radius: 4px;
                        }
                        .follow-do-no {
                            background-color: #e2e3e5;
                            color: #383d41;
                            padding: 3px 8px;
                            border-radius: 4px;
                        }
                    </style>

                    <div class="table-responsive mt-3">
                        <table class="table table-hover table-bordered-groups">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="7%">Tank</th>
                                    <th width="15%">Chemical</th>
                                    <th width="10%">Date</th>
                                    <th width="15%">Time</th>
                                    <th colspan="3" class="text-center chemical-percentage-header" width="24%">Chemical %</th>
                                    <th width="10%">Follow DO</th>
                                    <th width="14%">PIC</th>
                                    <th width="12%" style="white-space: nowrap;">Action</th>
                                </tr>
                                <tr>
                                    <th colspan="5"></th>
                                    <th class="chemical-percentage-subheader text-center" width="8%">Before</th>
                                    <th class="chemical-percentage-subheader text-center" width="8%">After</th>
                                    <th class="chemical-percentage-subheader text-center" width="8%">Total</th>
                                    <th colspan="2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loadingUnloadings as $record)
                                    <tr>
                                        <td>{{ $record->id }}</td>
                                        <td class="fw-bold">
                                            @if($editingId === $record->id)
                                                <input type="text" class="form-control form-control-sm" wire:model.defer="edit_tank_id">
                                            @else
                                                {{ $record->tank_id }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($editingId === $record->id)
                                                <input type="text" class="form-control form-control-sm" wire:model.defer="edit_che_code">
                                            @else
                                                @if($record->che_code == 'Pentane')
                                                    Pentane
                                                @elseif($record->che_code == 'PH1139')
                                                    Polyol (PH1139)
                                                @elseif($record->che_code == 'KH1250')
                                                    Isocyanate (KH1250)
                                                @else
                                                    {{ $record->che_code }}
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if($editingId === $record->id)
                                                <input type="date" class="form-control form-control-sm" wire:model.defer="edit_date">
                                            @else
                                                {{ date('d-m-Y', strtotime($record->date)) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($editingId === $record->id)
                                                <div class="d-flex gap-1">
                                                    <input type="time" class="form-control form-control-sm" wire:model.defer="edit_start_time">
                                                    <input type="time" class="form-control form-control-sm" wire:model.defer="edit_stop_time">
                                                </div>
                                            @else
                                                {{ date('H:i', strtotime($record->start_time)) }} - {{ date('H:i', strtotime($record->stop_time)) }}
                                            @endif
                                        </td>
                                        <td class="chemical-percentage text-end">
                                            @if($editingId === $record->id)
                                                <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm" wire:model.defer="edit_che_before">
                                            @else
                                                {{ number_format($record->che_before, 2) }}%
                                            @endif
                                        </td>
                                        <td class="chemical-percentage text-end">
                                            @if($editingId === $record->id)
                                                <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm" wire:model.defer="edit_che_after">
                                            @else
                                                {{ number_format($record->che_after, 2) }}%
                                            @endif
                                        </td>
                                        <td class="chemical-percentage total-value text-end">{{ number_format(($editingId === $record->id ? (float)$edit_che_after - (float)$edit_che_before : $record->che_after - $record->che_before), 2) }}%</td>
                                        <td class="text-center">
                                            @if($editingId === $record->id)
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox" wire:model.defer="edit_isFollowDO">
                                                </div>
                                            @else
                                                <span class="{{ $record->isFollowDO ? 'follow-do-yes' : 'follow-do-no' }}">
                                                    {{ $record->isFollowDO ? 'Yes' : 'No' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $record->user->name ?? 'Unknown' }}</td>
                                        <td>
                                            <div class="d-inline-flex gap-2 align-items-center flex-nowrap">
                                                @if($editingId === $record->id)
                                                    <button class="btn btn-success btn-sm" wire:click.prevent="saveEdit({{ $record->id }})"><i class="fas fa-check"></i></button>
                                                    <button class="btn btn-secondary btn-sm" wire:click.prevent="cancelEdit"><i class="fas fa-times"></i></button>
                                                @else
                                                    <button class="btn btn-success btn-sm" wire:click.prevent="startEdit({{ $record->id }})"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-danger btn-sm" wire:confirm="Are you sure you want to delete?" wire:click.prevent="deleteRecord({{ $record->id }})"><i class="fas fa-trash-alt"></i></button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No loading/unloading records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $loadingUnloadings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>