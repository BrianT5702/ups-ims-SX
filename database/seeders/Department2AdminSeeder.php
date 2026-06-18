<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates Department 2 Admin account (UPS database only — shared auth store).
 * Does not modify existing Department 1 admin/user accounts.
 */
class Department2AdminSeeder extends Seeder
{
    public function run(): void
    {
        $connection = 'ups';

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $adminRole = Role::on($connection)->where('name', 'Admin')->first();
        $permissionNames = $adminRole
            ? $adminRole->permissions->pluck('name')->toArray()
            : Permission::on($connection)->pluck('name')->toArray();

        $dept2AdminRole = Role::on($connection)->firstOrCreate(['name' => 'Department 2 Admin']);
        $dept2AdminRole->syncPermissions($permissionNames);

        $user = User::on($connection)->updateOrCreate(
            ['username' => 'admin2'],
            [
                'name' => 'Department 2 Admin',
                'email' => 'admin2@example.com',
                'phone_num' => '0123456789',
                'password' => 'admin212345',
            ]
        );

        $user->syncRoles([$dept2AdminRole]);

        // Tenant DBs require a matching users row for FK columns (user_id, updated_by, etc.).
        foreach (['ups2', 'urs2', 'ucs2'] as $tenantConnection) {
            if (!config("database.connections.{$tenantConnection}")) {
                continue;
            }

            User::on($tenantConnection)->updateOrCreate(
                ['username' => 'admin2'],
                [
                    'name' => 'Department 2 Admin',
                    'email' => 'admin2@example.com',
                    'phone_num' => '0123456789',
                    'password' => 'admin212345',
                ]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Department 2 Admin ready: username admin2 (companies: UPS2, URS2, UCS2 only).');
    }
}
