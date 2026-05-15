<div class="container my-3 customer-form-page compact-form-typography">
    <style>
        .customer-form-page .customer-form-section {
            border: 1px solid transparent;
            padding: 0.5rem 0.65rem;
        }
        .customer-form-page .customer-form-section-ids {
            margin-bottom: 0.5rem;
            background-color: #e8f0fe;
            border-color: #c5d4f0;
        }
        .customer-form-page .customer-form-sections-row {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }
        .customer-form-page .card-body {
            padding: 0.75rem 1rem;
        }
        .customer-form-page .customer-form-section-contact {
            background-color: #e8f6ee;
            border-color: #bfe0cc;
        }
        .customer-form-page .customer-form-section-commercial {
            background-color: #fff4e5;
            border-color: #f0d9b8;
        }
        .customer-form-page .customer-form-section-permissions {
            margin-top: 0.5rem;
            background-color: #f4f5f8;
            border-color: #d8dde8;
        }
        .customer-form-page.compact-form-typography label,
        .customer-form-page.compact-form-typography .form-label {
            font-size: 0.82em;
            font-weight: 600;
            margin-bottom: 0.1rem;
        }
        .customer-form-page.compact-form-typography .form-control,
        .customer-form-page.compact-form-typography .form-select,
        .customer-form-page.compact-form-typography input {
            font-size: 0.8em;
        }
        .customer-form-page.compact-form-typography .form-control-sm,
        .customer-form-page.compact-form-typography .form-select-sm {
            font-size: 0.8em;
            padding: 0.12rem 0.35rem;
            min-height: calc(1.35em + 0.24rem + 2px);
        }
        .customer-form-page.compact-form-typography p,
        .customer-form-page.compact-form-typography small {
            font-size: 0.85em;
        }
        .customer-form-page.compact-form-typography .form-group {
            margin-bottom: 0.15rem;
        }
        .customer-form-page.compact-form-typography .btn-sm {
            font-size: 0.85em;
        }
        .customer-form-page #permissions-container {
            max-height: 320px;
            overflow-y: auto;
            padding: 0.5rem 0.65rem;
            border: 1px dashed rgba(0, 0, 0, 0.1);
            border-radius: 0.25rem;
            background-color: #fff;
        }
        .customer-form-page .permission-checkbox {
            padding: 0.2rem 0.35rem;
            margin: 0;
            border-radius: 0.2rem;
        }
        .customer-form-page .permission-checkbox:hover {
            background-color: #e9ecef;
        }
        .customer-form-page .permission-checkbox .form-check-label {
            font-size: 0.8em;
            cursor: pointer;
            user-select: none;
        }
        .customer-form-page .permission-checkbox .form-check-input {
            margin-top: 0.2em;
            width: 0.95em;
            height: 0.95em;
        }
        .customer-form-page .permissions-toolbar {
            font-size: 0.82em;
            margin-bottom: 0.35rem;
            padding-bottom: 0.35rem;
            border-bottom: 1px solid #e0e4ec;
        }
    </style>
    <div class="row">
        <div class="col-lg-10 col-xl-9 m-auto">
            <div class="card shadow-sm rounded" style="overflow: visible;">
                <div class="card-header">
                    <div class="row d-flex align-items-center justify-content-between">
                        <div class="col-8">
                            <h5 class="fw-bold fs-5">{{ $isView ? 'View' : ($user ? 'Edit' : 'Add') }} User</h5>
                        </div>
                        <div class="col-4 text-end">
                            <a wire:navigate href="{{ route('users') }}" class="btn btn-primary btn-sm rounded">Back</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="addUser">
                        {{-- Top: login & contact ids --}}
                        <div class="customer-form-section customer-form-section-ids rounded">
                            <div class="row g-1">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                        <input type="text" name="username" wire:model="username" class="form-control form-control-sm rounded" id="username" {{ $isView ? 'disabled' : '' }}>
                                        @error('username')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" wire:model="email" class="form-control form-control-sm rounded" id="email" {{ $isView ? 'disabled' : '' }}>
                                        @error('email')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="phone_num" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" name="phone_num" wire:model="phone_num" class="form-control form-control-sm rounded" id="phone_num" {{ $isView ? 'disabled' : '' }}>
                                        @error('phone_num')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Profile (left) & access (right) --}}
                        <div class="row customer-form-sections-row g-2">
                            <div class="col-md-8">
                                <div class="customer-form-section customer-form-section-contact rounded h-100">
                                    <div class="form-group mb-0">
                                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" wire:model="name" class="form-control form-control-sm rounded" id="name" {{ $isView ? 'disabled' : '' }}>
                                        @error('name')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="customer-form-section customer-form-section-commercial rounded h-100">
                                    <div class="form-group">
                                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                        <select name="role" class="form-select form-select-sm rounded" id="role" wire:model="role" {{ $isView ? 'disabled' : '' }}>
                                            <option value="" disabled {{ empty($role) ? 'selected' : '' }}>Select a role</option>
                                            @foreach($roles as $roleOption)
                                                <option value="{{ $roleOption->name }}">{{ $roleOption->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('role')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="password" class="form-label">Password @if(!$user)<span class="text-danger">*</span>@endif</label>
                                        @if($isView)
                                            <input type="text" id="password" class="form-control form-control-sm rounded" value="********" disabled>
                                        @else
                                            <input type="password" id="password" name="password" class="form-control form-control-sm rounded" wire:model="password" placeholder="{{ $user ? 'Leave blank to keep current' : '' }}">
                                        @endif
                                        @error('password')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    @if(!$isView)
                                    <div class="form-group mb-0">
                                        <label for="confirmPassword" class="form-label">Confirm Password @if(!$user)<span class="text-danger">*</span>@endif</label>
                                        <input type="password" name="confirmPassword" wire:model="confirmPassword" class="form-control form-control-sm rounded" id="confirmPassword">
                                        @error('confirmPassword')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if(!$hidePermissions)
                        <div class="customer-form-section customer-form-section-permissions rounded">
                            <div class="form-group mb-0">
                                <label class="form-label mb-1">User-specific Permissions</label>
                                <div id="permissions-container">
                                    <div class="permissions-toolbar d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <span class="text-muted">Select permissions to grant directly to this user</span>
                                        @if(!$isView)
                                        <button class="btn btn-outline-primary btn-sm" type="button" wire:click="selectAllPermissions">
                                            Toggle Select All
                                        </button>
                                        @endif
                                    </div>
                                    <div class="row g-1">
                                        @foreach($permissions as $perm)
                                            <div class="col-md-4 col-lg-3">
                                                <div class="form-check permission-checkbox">
                                                    <input class="form-check-input" type="checkbox" id="perm_{{ $perm->id }}" value="{{ $perm->name }}" wire:model="selectedPermissions" {{ $isView ? 'disabled' : '' }}>
                                                    <label class="form-check-label" for="perm_{{ $perm->id }}">{{ $perm->name }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(!$isView)
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary btn-sm">{{ $user ? 'Update' : 'Add' }}</button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
