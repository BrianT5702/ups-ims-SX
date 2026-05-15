<div class="list-page-unified-density">
    <div class="container my-2" style="padding-left: 0.25rem; padding-right: 0.25rem;">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header transaction-log-page-header">
                        <h5 class="fw-bold mb-0 list-page-unified-title">Manage Group</h5>
                    </div>
                    <div class="card-body px-2 pb-3 transaction-log-card-body">
                        <div class="row mb-2 g-2 align-items-end list-page-unified-filters">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" wire:model.live.debounce.300ms="groupSearchTerm" class="form-control form-control-sm rounded" placeholder="Search group...">
                            </div>
                            <div class="col-md-8">
                                <form wire:submit.prevent="addGroup" class="row g-2 align-items-end">
                                    <div class="col">
                                        <label for="group_name" class="form-label">Group Name <span class="text-danger">*</span></label>
                                        @error('group_name')
                                            <p class="text-danger small mt-1">{{ $message }}</p>
                                        @enderror
                                        <input type="text" name="group_name" wire:model="group_name" class="form-control form-control-sm rounded" id="group_name">
                                    </div>
                                    <div class="col-auto d-flex align-items-end pb-1">
                                        <button type="submit" class="btn btn-primary btn-sm">Add Group</button>
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
                                            <button type="button" wire:click="sortBy('group_name')" class="btn btn-sm p-0 border-0 bg-transparent text-dark text-decoration-none fw-semibold master-data-sort-btn">
                                                Group Name{{ $sortColumn === 'group_name' ? ($sortOrder === 'asc' ? ' ↑' : ' ↓') : '' }}
                                            </button>
                                        </th>
                                        <th class="master-data-col-action text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($groups as $group)
                                        <tr>
                                            <td class="text-muted">{{ ($groups->firstItem() ?? 0) + $loop->index }}</td>
                                            <td>{{ $group->group_name }}</td>
                                            <td class="text-end">
                                                <button wire:confirm="Are you sure you want to delete?" wire:click="deleteGroup({{ $group->id }})" type="button" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No groups found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="list-simple-pagination">
                                {{ $groups->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('partials.unified-list-page-styles')
</div>
