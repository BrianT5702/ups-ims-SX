<div class="container my-3">
    <div class="row">
        <div class="col-7 m-auto">
            <div class="card shadow-sm" style="overflow: visible;">
                <div class="card-header">
                    <div class="row d-flex align-items-center justify-content-between">
                        <div class="col-8">
                            <h5 class="fw-bold fs-5">{{ $isView ? 'View' : ($user ? 'Edit' : 'Add') }} User </h5>
                        </div>
                        <div class="col-4 text-end">
                            <a wire:navigate href="{{ route('users') }}" class="btn btn-primary btn-sm">Back</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="addUser">

                        <div class="form-group mb-2">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" wire:model="name" class="form-control form-control-sm rounded" id="name" {{ $isView ? 'disabled' : '' }}>
                            @error('name')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" wire:model="username" class="form-control form-control-sm rounded" id="username" {{ $isView ? 'disabled' : '' }}>
                            @error('username')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" wire:model="email" class="form-control form-control-sm rounded" id="email" {{ $isView ? 'disabled' : '' }}>
                            @error('email')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="text" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone_num" wire:model="phone_num" class="form-control form-control-sm rounded" id="phone_num" {{ $isView ? 'disabled' : '' }}>
                            @error('phone_num')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            @if($isView)
                                <input type="text" id="password" class="form-control" value="********" disabled>
                            @else
                                <input type="password" id="password" class="form-control" wire:model="password">
                            @endif
                            @error('password')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(!$isView)
                            <div class="form-group mb-2">
                                <label for="confirmPassword" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="confirmPassword" wire:model="confirmPassword" class="form-control form-control-sm rounded" id="confirmPassword">
                                @error('confirmPassword')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <div class="form-group mb-2">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select form-select-sm" id="role" wire:model="role" {{ $isView ? 'disabled' : '' }}>
                                <option value="" disabled selected>Select a role</option>
                                @foreach($roles as $roleOption)
                                    <option value="{{ $roleOption->name }}">{{ $roleOption->name }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(!$hidePermissions)
                            <div class="form-group mb-3">
                                <label class="form-label">User-specific Permissions</label>
                                <div id="permissions-container" class="border rounded p-2">
                                    <div class="permission-header">Select permissions to grant directly to this user</div>
                                    <div class="select-all">
                                        <button class="btn btn-outline-secondary btn-sm" type="button" wire:click="selectAllPermissions">Toggle Select All</button>
                                    </div>
                                    <div class="row">
                                        @foreach($permissions as $perm)
                                            <div class="col-md-4 permission-item">
                                                <input type="checkbox" id="perm_{{ $perm->id }}" value="{{ $perm->name }}" wire:model="selectedPermissions" {{ $isView ? 'disabled' : '' }}>
                                                <label for="perm_{{ $perm->id }}" class="ms-1">{{ $perm->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif


                        @if(!$isView)
                            <div class="card-footer mb-2">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-sm">{{ $user ? 'Update' : 'Add' }}</button>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const style = document.createElement('style');
style.textContent = `
#permissions-container {
    max-height: 250px;
    overflow-y: auto;
}

.permission-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.permission-item input {
    margin-right: 10px;
}

.permission-header {
    font-weight: bold;
    margin-bottom: 10px;
    text-align: center;
}

.select-all {
    margin-bottom: 15px;
    text-align: center;
}

.form-control-sm {
    font-size: 0.875rem;
}

.card-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #e9ecef;
}`;
</script>


