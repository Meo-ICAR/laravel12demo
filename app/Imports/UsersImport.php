<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UsersImport implements ToCollection, WithHeadingRow
{
    protected $importedCount = 0;
    protected $failedCount = 0;
    protected $errors = [];

    public function collection(Collection $rows)
    {
        $adminCompanyId = Auth::user()->company_id;

        foreach ($rows as $row) {
            try {
                // Check if email already exists
                if (User::where('email', $row['email'])->exists()) {
                    $this->failedCount++;
                    $this->errors[] = "Email {$row['email']} already exists";
                    continue;
                }

                // Find role by name
                $role = Role::where('name', $row['role'])->first();
                if (!$role) {
                    $this->failedCount++;
                    $this->errors[] = "Role {$row['role']} not found for user {$row['email']}";
                    continue;
                }

                // Create user
                $user = User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => Hash::make('demo1234'),
                    'company_id' => $adminCompanyId,
                ]);

                // Assign role
                $user->assignRole($role);

                $this->importedCount++;
            } catch (\Exception $e) {
                $this->failedCount++;
                $this->errors[] = "Error importing {$row['email']}: " . $e->getMessage();
            }
        }
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getFailedCount()
    {
        return $this->failedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
