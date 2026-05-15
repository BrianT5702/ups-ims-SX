<div class="list-page-unified-density">
    <div class="container my-2" style="padding-left: 0.25rem; padding-right: 0.25rem;">
        <div class="row">
            <div class="col-md-11 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header transaction-log-page-header d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <h5 class="fw-bold mb-0 list-page-unified-title">Manage User</h5>
                        <a wire:navigate href="{{ route('users.add') }}" class="btn btn-primary btn-sm flex-shrink-0">Add User</a>
                    </div>
                    <div class="card-body px-2 pb-3 transaction-log-card-body">
                        <div class="row mb-2 g-2 align-items-end list-page-unified-filters">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" wire:model.live.debounce.100ms="userSearchTerm" class="form-control form-control-sm rounded" placeholder="Search user...">
                            </div>
                        </div>

                        <div class="table-responsive mt-2">
                            <table class="table table-hover list-simple-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Name <span wire:click="sortBy('name')">
                                                @if ($sortColumn === 'name')
                                                    @if ($sortOrder === 'asc')
                                                        <i class="fa-solid fa-sort-up"></i>
                                                    @else
                                                        <i class="fa-solid fa-sort-down"></i>
                                                    @endif
                                                @else
                                                    <i class="fa-solid fa-sort"></i>
                                                @endif
                                        </span></th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td>{{ ($users->firstItem() ?? 0) + $loop->index }}</td>
                                            <td><a wire:navigate href="{{ route('users.view', $user->id)}}"> {{ $user->name }}</a></td>
                                            <td><a wire:navigate href="{{ route('users.view', $user->id)}}">{{ $user->username }}</a></td>
                                            <td><a wire:navigate href="{{ route('users.view', $user->id)}}">{{ $user->email }}</a></td>
                                            <td>
                                                @if($user->roles->isNotEmpty())
                                                    {{ $user->roles->first()->name }} <!-- Display the first role name -->
                                                @else
                                                    No Role
                                                @endif
                                            </td>

                                            <td>
                                                <a href="{{ route('users.edit', $user->id) }}" wire:navigate class="btn btn-success btn-sm"><i class="fas fa-edit"></i></a>
                                                <button wire:confirm="Are you sure you want to delete?" wire:click="deleteUser({{ $user->id }})" type="button" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No users found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="list-simple-pagination">
                                {{ $users->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('partials.unified-list-page-styles')
</div>
