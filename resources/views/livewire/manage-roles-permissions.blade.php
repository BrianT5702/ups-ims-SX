<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">Manage Roles and Permissions</h1>
    

    <div class="mb-6">
        <form wire:submit.prevent="createRole" class="flex items-center space-x-2 mb-4">
            <input type="text" wire:model="roleName" placeholder="Role Name" required
                class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit"
                class="bg-blue-500 text-white rounded-lg px-4 py-2 hover:bg-blue-600 transition duration-200">Create Role</button>
        </form>

        <form wire:submit.prevent="createPermission" class="flex items-center space-x-2">
            <input type="text" wire:model="permissionName" placeholder="Permission Name" required
                class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit"
                class="bg-blue-500 text-white rounded-lg px-4 py-2 hover:bg-blue-600 transition duration-200">Create Permission</button>
        </form>
    </div>

    
    @if (session()->has('success'))
        <div class="mt-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mt-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <h2 class="text-xl font-semibold mt-6 mb-2">Roles</h2>
    <ul class="mb-4 border border-gray-300 rounded-lg shadow-sm">
        @foreach($roles as $role)
            <li class="flex justify-between items-center px-4 py-2 border-b last:border-b-0">
                <span>{{ $role->name }}</span>
                <button wire:click="confirmDeleteRole({{ $role->id }})"
                    class="text-red-600 hover:text-red-700 transition duration-200">Delete</button>
            </li>
        @endforeach
    </ul>

    @if ($confirmDeleteRoleId)
        <div class="mb-4 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
            Are you sure you want to delete this role?
            <div class="mt-2">
                <button wire:click="deleteRole"
                    class="bg-red-500 text-white rounded-lg px-4 py-1 hover:bg-red-600 transition duration-200">Yes</button>
                <button wire:click="$set('confirmDeleteRoleId', null)"
                    class="ml-2 bg-gray-300 text-black rounded-lg px-4 py-1 hover:bg-gray-400 transition duration-200">No</button>
            </div>
        </div>
    @endif

    <h2 class="text-xl font-semibold mt-6 mb-2">Permissions</h2>
    <ul class="border border-gray-300 rounded-lg shadow-sm">
        @foreach($permissions as $permission)
            <li class="flex justify-between items-center px-4 py-2 border-b last:border-b-0">
                <span>{{ $permission->name }}</span>
                <button wire:click="confirmDeletePermission({{ $permission->id }})"
                    class="text-red-600 hover:text-red-700 transition duration-200">Delete</button>
            </li>
        @endforeach
    </ul>

    @if ($confirmDeletePermissionId)
        <div class="mb-4 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
            Are you sure you want to delete this permission?
            <div class="mt-2">
                <button wire:click="deletePermission"
                    class="bg-red-500 text-white rounded-lg px-4 py-1 hover:bg-red-600 transition duration-200">Yes</button>
                <button wire:click="$set('confirmDeletePermissionId', null)"
                    class="ml-2 bg-gray-300 text-black rounded-lg px-4 py-1 hover:bg-gray-400 transition duration-200">No</button>
            </div>
        </div>
    @endif

</div>
