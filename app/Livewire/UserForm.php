<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Attributes\Title;

#[Title('UR | Manage User')]

class UserForm extends Component
{
    public $isView = false;
    public $user = null;
    public $roles;
    public $permissions;
    public $selectedPermissions = [];
    public $hidePermissions = false;

    #[Validate('required', message: 'Role is required')]
    public $role = '';

    public $name;
    public $username;
    public $email;
    public $phone_num;
    public $password;
    public $confirmPassword;

    public function mount(User $user)
    {
        // Users are stored in UPS database, so fetch roles from UPS
        $this->roles = Role::on('ups')->get();
        $this->permissions = Permission::on('ups')->get();
        $this->isView = request()->routeIs('users.view');
        
        if ($user->id) {
            $this->user = $user;
            $this->name = $user->name;
            $this->username = $user->username;
            $this->email = $user->email;
            $this->phone_num = $user->phone_num;
            $this->password = '';
            $this->role = $user->roles->first()->name ?? '';
            // Merge direct and role permissions
            $rolePermissions = $user->roles->flatMap->permissions->pluck('name')->toArray();
            $directPermissions = $user->permissions->pluck('name')->toArray();
            $this->selectedPermissions = array_unique(array_merge($rolePermissions, $directPermissions));
        } else {
            $this->selectedPermissions = [];
        }

        $this->hidePermissions = ($this->role === 'Admin');
    }

    public function addUser()
    {
        $this->validate([
            'name' => 'required|min:3|max:60',
            'username' => 'required|min:3|max:60|unique:users,username,' . ($this->user ? $this->user->id : 'NULL'),
            'email' => 'required|email|max:150|unique:users,email,' . ($this->user ? $this->user->id : 'NULL'),
            'phone_num' => 'required|min:9|max:15',
            'password' => $this->user ? 'nullable|min:8' : 'required|min:8',
            'confirmPassword' => $this->user ? 'nullable|same:password' : 'required|same:password',
            'role' => 'required',
            'selectedPermissions' => 'array',
        ]);

        // If Admin role selected, assign all permissions and hide section
        if ($this->role === 'Admin') {
            $this->selectedPermissions = $this->permissions->pluck('name')->toArray();
            $this->hidePermissions = true;
        }

        if ($this->user) {
            $this->user->name = $this->name;
            $this->user->username = $this->username;
            $this->user->email = $this->email;
            $this->user->phone_num = $this->phone_num;
            if (!empty($this->password)) {
                $this->user->password = bcrypt($this->password);
            }

            try {
                $this->user->save();
                $this->user->syncRoles([$this->role]);

                // Assign only user-specific permissions beyond role defaults
                $rolePermissions = [];
                if (!empty($this->role)) {
                    $role = Role::where('name', $this->role)->first();
                    if ($role) {
                        $rolePermissions = $role->permissions->pluck('name')->toArray();
                    }
                }
                $directPermissions = array_values(array_diff($this->selectedPermissions ?? [], $rolePermissions));
                $this->user->syncPermissions($directPermissions);
                toastr()->success('User updated successfully');
            } catch (\Exception $e) {
                toastr()->error('An error occurred while updating the user: ' . $e->getMessage());
            }
        } else {
            try {
                $user = User::create([
                    'name' => $this->name,
                    'username' => $this->username,
                    'email' => $this->email,
                    'phone_num' => $this->phone_num,
                    'password' => bcrypt($this->password),
                ]);

                $user->assignRole($this->role);

                // Assign only user-specific permissions beyond role defaults
                $rolePermissions = [];
                if (!empty($this->role)) {
                    $role = Role::on('ups')->where('name', $this->role)->first();
                    if ($role) {
                        $rolePermissions = $role->permissions->pluck('name')->toArray();
                    }
                }
                $directPermissions = array_values(array_diff($this->selectedPermissions ?? [], $rolePermissions));
                $user->syncPermissions($directPermissions);

                toastr()->success('User added successfully');
            } catch (\Exception $e) {
                toastr()->error('An error occurred while adding the user: ' . $e->getMessage());
            }
        }

        return $this->redirect('/users', navigate: true);
    }

    public function updatedRole($value)
    {
        $this->hidePermissions = ($value === 'Admin');
        if ($this->hidePermissions) {
            // Admin gets all permissions; hide the section
            $this->selectedPermissions = $this->permissions->pluck('name')->toArray();
        }
        // When switching away from Admin, keep current selections and show the section
    }

    public function selectAllPermissions()
    {
        if (count($this->selectedPermissions) === count($this->permissions)) {
            // If all permissions are already selected, clear the selection
            $this->selectedPermissions = [];
        } else {
            // Otherwise, select all permissions
            $this->selectedPermissions = $this->permissions->pluck('name')->toArray();
        }
    }

    public function render()
    {
        $this->hidePermissions = ($this->role === 'Admin');
        return view('livewire.user-form')->layout('layouts.app');
    }
}