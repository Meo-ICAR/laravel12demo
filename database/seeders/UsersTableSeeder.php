<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'company_id' => NULL,
                'name' => 'super',
                'email' => 'super@example.com',
                'email_verified_at' => NULL,
                'password' => '$2y$12$oSzH5UeWXlK46b09PXDwz.BpME16WwjUiha1/uLVIo03V6EY.KSO.',
                'remember_token' => NULL,
                'role' => 'super_admin',
                'created_at' => '2025-06-18 14:01:32',
                'updated_at' => '2025-06-18 14:01:32',
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'company_id' => NULL,
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'email_verified_at' => '2025-06-18 14:03:24',
                'password' => '$2y$12$A9CdovOKDoGSD68O7kvMG.oa0Vp3zFwMXaM7EwwD7sf41LsSWXTUK',
                'remember_token' => '8jwQWme63PAdDG6W9L7XzZc42I2QrOmXXJyogSskgNFynGrJfcPismlUBQxN',
                'role' => 'super_admin',
                'created_at' => '2025-06-18 14:03:24',
                'updated_at' => '2025-06-18 14:03:24',
                'deleted_at' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'company_id' => NULL,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'email_verified_at' => '2025-06-18 14:03:24',
                'password' => '$2y$12$NrzDplGYsuKZ5eWjrhKy3.Rkh3w.9ZZvYUn/f4Mdj7aQWqlg.hWau',
                'remember_token' => 'T3fur2B1t8',
                'role' => 'super_admin',
                'created_at' => '2025-06-18 14:03:24',
                'updated_at' => '2025-06-18 14:03:24',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}