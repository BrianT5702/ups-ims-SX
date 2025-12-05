<div class="container-fluid my-3">
    <div class="row">
        <div class="col-md-10 m-auto">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold fs-5 mb-0">Manage Family</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-end mb-3">
                        <div class="col-md-4">
                            <input type="text" wire:model.live.debounce.300ms="familySearchTerm" class="form-control form-control-sm rounded" placeholder="Search family...">
                        </div>
                        <div class="col-md-8">
                            <form wire:submit.prevent="addFamily" class="row g-2 align-items-end">
                                <div class="col">
                                    <label for="family_name" class="form-label">Family Name <span class="text-danger">*</span></label>
                                    @error('family_name')
                                        <p class="text-danger small mt-1">{{ $message }}</p>
                                    @enderror
                                    <input type="text" name="family_name" wire:model="family_name" class="form-control form-control-sm rounded" id="family_name">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary btn">Add Family</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Family Name <span wire:click="sortBy('family_name')">
                                            @if ($sortColumn === 'family_name')
                                                @if ($sortOrder === 'asc')
                                                    <i class="fa-solid fa-sort-up"></i>
                                                @else
                                                    <i class="fa-solid fa-sort-down"></i>
                                                @endif
                                            @else
                                                <i class="fa-solid fa-sort"></i>
                                            @endif
                                    </span>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($families as $family)
                                    <tr>
                                        <td>{{ ($families->firstItem() ?? 0) + $loop->index }}</td>
                                        <td>
                                                {{ $family->family_name }}</td>
                                            </a>
                                        <td>
                                            <button wire:confirm="Are you sure you want to delete?" wire:click="deleteFamily({{ $family->id }})" type="button" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No families found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $families->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
