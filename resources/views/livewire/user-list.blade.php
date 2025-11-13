<div>
    <div class="container my-3">
        <div class="row">
            <div class="col-md-10 m-auto">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold fs-5 mb-0">Manage User</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search and Add User -->
                        <div class="row align-items-end mb-3 my-3">
                            <div class="col-md-4">
                                <input type="text" wire:model.live.debounce.100ms="userSearchTerm" class="form-control form-control-sm rounded" placeholder="Search user...">
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex justify-content-end">
                                    <a wire:navigate href="{{ route('users.add') }}" class="btn btn-primary">Add User</a>
                                </div>
                            </div>
                        </div>


                        <!-- User Table -->
                        <div class="table-responsive mt-3">
                            <table class="table table-hover">
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
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
