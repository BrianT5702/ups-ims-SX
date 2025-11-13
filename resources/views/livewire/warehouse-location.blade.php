<div class="container-fluid my-4">
    <div class="row">
        <!-- Warehouse Management Section -->
        <div class="col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold fs-5 mb-0">Warehouse Management</h5>
                    <button 
                        wire:click="toggleWarehouseForm" 
                        class="btn {{ $showWarehouseForm ? 'btn-danger' : 'btn-primary' }}"
                    >
                        <i class="fas {{ $showWarehouseForm ? 'fa-times' : 'fa-plus' }} me-2"></i>
                        {{ $showWarehouseForm ? 'Cancel' : 'Add New Warehouse' }}
                    </button>
                </div>
                <div class="card-body">
                    @if($showWarehouseForm)
                        <form wire:submit.prevent="addWarehouse" class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label required">Warehouse Name</label>
                                <input 
                                    type="text" 
                                    wire:model.live="warehouse_name" 
                                    class="form-control rounded @error('warehouse_name') is-invalid @enderror" 
                                    placeholder="Enter warehouse name"
                                >
                                @error('warehouse_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Warehouse
                                </button>
                            </div>
                        </form>
                    @endif

                    <!-- Warehouses Table -->
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Warehouse Name</th>
                                    <th>Locations Count</th>
                                    <th>Items Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($warehousesList as $warehouse)
                                    <tr>
                                        <td>{{ $warehouse->warehouse_name }}</td>
                                        <td>{{ $warehouse->locations_count }}</td>
                                        <td>{{ $warehouse->items_count }}</td>
                                        <td>
                                            <button 
                                                wire:click="deleteWarehouse({{ $warehouse->id }})"
                                                class="btn btn-danger btn-sm"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No warehouses found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-3">
                        {{ $warehousesList->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Management Section -->
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold fs-5 mb-0">Location Management</h5>
                    <button 
                        wire:click="toggleLocationForm" 
                        class="btn {{ $showLocationForm ? 'btn-danger' : 'btn-primary' }}"
                    >
                        <i class="fas {{ $showLocationForm ? 'fa-times' : 'fa-plus' }} me-2"></i>
                        {{ $showLocationForm ? 'Cancel' : 'Add New Location' }}
                    </button>
                </div>
                <div class="card-body">
                    @if($showLocationForm)
                        <form wire:submit.prevent="addLocation" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label required">Warehouse</label>
                                <select 
                                    wire:model.live="warehouse_id" 
                                    class="form-select rounded @error('warehouse_id') is-invalid @enderror"
                                >
                                    <option value="">Select Warehouse</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label required">Location Name</label>
                                <input 
                                    type="text" 
                                    wire:model.live="location_name" 
                                    class="form-control rounded @error('location_name') is-invalid @enderror" 
                                    placeholder="Enter location name"
                                >
                                @error('location_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Location
                                </button>
                            </div>
                        </form>
                    @endif

                    <!-- Filter dropdown -->
                    <div class="mb-3 mt-4">
                        <label for="warehouseFilter" class="form-label">Filter by Warehouse</label>
                        <select 
                            wire:model.live="filter_warehouse_id" 
                            id="warehouseFilter" 
                            class="form-select rounded"
                        >
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Locations Table -->
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Location Name</th>
                                    <th>Warehouse</th>
                                    <th>Total Items</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($filteredLocations as $location)
                                    <tr>
                                        <td>{{ $location->location_name }}</td>
                                        <td>{{ $location->warehouse->warehouse_name }}</td>
                                        <td>{{ $location->items_count ?? 0 }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No locations found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $filteredLocations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>