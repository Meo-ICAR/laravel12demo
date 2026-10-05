<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    protected $signature = 'create:super-admin';
    protected $description = 'Create a super admin user';

    public function handle()
    {
        $name = $this->ask('What is the name of the super admin?');
        $email = $this->ask('What is the email of the super admin?');
        $password = $this->secret('What is the password for the super admin?');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $user->assignRole('super_admin');

        $this->info('Super admin user created successfully!');
        $this->info('Email: ' . $email);
        $this->info('Password: ' . $password);

        return 0;
    }
}
