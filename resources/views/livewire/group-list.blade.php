<div class="container-fluid my-3">
    <div class="row">
        <div class="col-md-10 m-auto">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold fs-5 mb-0">Manage Group</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-end mb-3">
                        <div class="col-md-4">
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
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary btn">Add Group</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Group Name <span wire:click="sortBy('group_name')">
                                            @if ($sortColumn === 'group_name')
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
                                @forelse($groups as $group)
                                    <tr>
                                        <td>{{ ($groups->firstItem() ?? 0) + $loop->index }}</td>
                                        <td>
                                                {{ $group->group_name }}</td>
                                            </a>
                                        <td>
                                            <button wire:confirm="Are you sure you want to delete?" wire:click="deleteGroup({{ $group->id }})" type="button" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No groups found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $groups->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

