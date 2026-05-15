<div class="list-page-unified-density">
    <div class="container my-2" style="padding-left: 0.25rem; padding-right: 0.25rem;">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header transaction-log-page-header">
                        <h5 class="fw-bold mb-0 list-page-unified-title">Manage Family</h5>
                    </div>
                    <div class="card-body px-2 pb-3 transaction-log-card-body">
                        <div class="row mb-2 g-2 align-items-end list-page-unified-filters">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
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
                                    <div class="col-auto d-flex align-items-end pb-1">
                                        <button type="submit" class="btn btn-primary btn-sm">Add Family</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive mt-2">
                            <table class="table table-hover list-simple-table list-simple-table-compact master-data-list-table">
                                <thead>
                                    <tr>
                                        <th class="master-data-col-no">No</th>
                                        <th class="master-data-col-name">
                                            <button type="button" wire:click="sortBy('family_name')" class="btn btn-sm p-0 border-0 bg-transparent text-dark text-decoration-none fw-semibold master-data-sort-btn">
                                                Family Name{{ $sortColumn === 'family_name' ? ($sortOrder === 'asc' ? ' ↑' : ' ↓') : '' }}
                                            </button>
                                        </th>
                                        <th class="master-data-col-action text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($families as $family)
                                        <tr>
                                            <td class="text-muted">{{ ($families->firstItem() ?? 0) + $loop->index }}</td>
                                            <td>{{ $family->family_name }}</td>
                                            <td class="text-end">
                                                <button wire:confirm="Are you sure you want to delete?" wire:click="deleteFamily({{ $family->id }})" type="button" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No families found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="list-simple-pagination">
                                {{ $families->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('partials.unified-list-page-styles')
</div>
