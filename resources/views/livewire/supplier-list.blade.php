<div>
    <div class="container my-3">
        <div class="row">
            <div class="col-md-10 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold fs-5 mb-0">Manage Supplier</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search and Add Customer -->
                        <div class="row align-items-end mb-3 my-3">
                            <div class="col-md-4">
                                <input type="text" wire:model.live.debounce.100ms="supplierSearchTerm" class="form-control form-control-sm rounded" placeholder="Search supplier...">
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex justify-content-end">
                                    <a wire:navigate href="{{ route('suppliers.add') }}" class="btn btn-primary">Add Supplier</a>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Table -->
                        <div class="table-responsive mt-3">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Name <span wire:click="sortBy('sup_name')">
                                                @if ($sortColumn === 'sup_name')
                                                    @if ($sortOrder === 'asc')
                                                        <i class="fa-solid fa-sort-up"></i>
                                                    @else
                                                        <i class="fa-solid fa-sort-down"></i>
                                                    @endif
                                                @else
                                                    <i class="fa-solid fa-sort"></i>
                                                @endif
                                        </span></th>
                                        <th>Phone Number</th>
                                        <th>Email</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($suppliers as $supplier)
                                        <tr>
                                            <td>{{ ($suppliers->firstItem() ?? 0) + $loop->index }}</td>
                                            <td><a wire:navigate href="{{ route('suppliers.view', $supplier->id)}}"> {{ $supplier->sup_name }}</a></td>
                                            <td><a wire:navigate href="{{ route('suppliers.view', $supplier->id)}}">{{ $supplier->phone_num }}</a></td>
                                            <td><a wire:navigate href="{{ route('suppliers.view', $supplier->id)}}">{{ $supplier->email }}</a></td>
                                            <td>
                                                @can('Manage PO')
                                                <button wire:click.prevent="showSupplierPO({{ $supplier->id }})" class="btn btn-info btn-sm">
                                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                                </button>
                                                @endcan
                                                <a href="{{ route('suppliers.edit', $supplier->id) }}" wire:navigate class="btn btn-success btn-sm"><i class="fas fa-edit"></i></a>
                                                <button wire:confirm="Are you sure you want to delete?" wire:click="deleteSupplier({{ $supplier->id }})" type="button" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No suppliers found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{ $suppliers->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
