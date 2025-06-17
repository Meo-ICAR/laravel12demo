<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'user_management',
            'role_management',
            'permission_management',
            'view_dashboard',
            'view_profile',
            'edit_profile',
            'delete_profile',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $roles = [
            'super_admin' => $permissions,
            'admin' => [
                'user_management',
                'view_dashboard',
                'view_profile',
                'edit_profile',
            ],
            'user' => [
                'view_dashboard',
                'view_profile',
                'edit_profile',
            ],
        ];

        foreach ($roles as $role => $rolePermissions) {
            $role = Role::create(['name' => $role]);
            $role->givePermissionTo($rolePermissions);
        }
    }
}
