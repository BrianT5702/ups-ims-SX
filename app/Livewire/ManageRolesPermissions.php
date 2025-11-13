<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Rules\UniqueInCurrentDatabase;

class ManageRolesPermissions extends Component
{
    public $roles, $permissions;
    public $roleName, $permissionName;
    public $confirmDeleteRoleId = null; // ID for the role to confirm deletion
    public $confirmDeletePermissionId = null; // ID for the permission to confirm deletion

    public function mount()
    {
        $this->roles = Role::with('permissions')->get();
        $this->permissions = Permission::all();
    }

    public function createRole()
    {
        $this->validate(['roleName' => ['required', 'string', new UniqueInCurrentDatabase('roles', 'name')]]);
        Role::create(['name' => $this->roleName]);
        $this->reset('roleName');
        session()->flash('success', 'Role created successfully.');
    }

    public function createPermission()
    {
        $this->validate(['permissionName' => ['required', 'string', new UniqueInCurrentDatabase('permissions', 'name')]]);
        Permission::create(['name' => $this->permissionName]);
        $this->reset('permissionName');
        session()->flash('success', 'Permission created successfully.');
    }

    public function confirmDeleteRole($roleId)
    {
        $this->confirmDeleteRoleId = $roleId; // Set the ID of the role to delete
    }

    public function confirmDeletePermission($permissionId)
    {
        $this->confirmDeletePermissionId = $permissionId; // Set the ID of the permission to delete
    }

    public function deleteRole()
    {
        if ($this->confirmDeleteRoleId) {
            $role = Role::findOrFail($this->confirmDeleteRoleId);
            if ($role->users()->count() > 0) {
                session()->flash('error', 'Role cannot be deleted. It is assigned to users.');
            } else {
                $role->delete();
                session()->flash('success', 'Role deleted successfully.');
            }
            $this->confirmDeleteRoleId = null; // Reset the confirmation ID
        }
    }

    public function deletePermission()
    {
        if ($this->confirmDeletePermissionId) {
            $permission = Permission::findOrFail($this->confirmDeletePermissionId);
            if ($permission->users()->count() > 0) {
                session()->flash('error', 'Permission cannot be deleted. It is assigned to users.');
            } else {
                $permission->delete();
                session()->flash('success', 'Permission deleted successfully.');
            }
            $this->confirmDeletePermissionId = null; // Reset the confirmation ID
        }
    }

    public function render()
    {
        $this->roles = Role::with('permissions')->get();
        $this->permissions = Permission::all();
        return view('livewire.manage-roles-permissions')->layout('layouts.app');
    }
}
