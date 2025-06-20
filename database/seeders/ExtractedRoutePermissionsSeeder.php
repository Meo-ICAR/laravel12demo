<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ExtractedRoutePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'role_management',
            'permission_management',
            'user_management',
            'fornitori_management',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Insert default roles
        $roles = ['super_admin', 'admin', 'user'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Insert additional roles
        $extraRoles = ['admin', 'backoffice', 'segreteria', 'centralino'];
        foreach ($extraRoles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Associate all permissions to super_admin
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
        }
    }
}
