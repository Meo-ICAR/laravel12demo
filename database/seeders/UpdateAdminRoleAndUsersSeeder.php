<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UpdateAdminRoleAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Ensure the admin role has all necessary permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        // Get all permissions
        $permissions = Permission::all();
        $adminRole->syncPermissions($permissions);
        
        // Assign admin role to all existing users
        $users = User::all();
        foreach ($users as $user) {
            if (!$user->hasRole('admin')) {
                $user->assignRole('admin');
            }
        }
        
        $this->command->info('All users have been assigned the admin role with all permissions.');
    }
}
