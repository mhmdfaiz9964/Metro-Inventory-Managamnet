<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesAndUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = ['Member', 'Sales Rep', 'Cashier'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Optional: assign roles to existing users
        $users = [
            1 => 'Member',
            2 => 'Sales Rep',
            3 => 'Cashier',
        ];

        foreach ($users as $userId => $roleName) {
            $user = User::find($userId);
            if ($user) {
                $user->assignRole($roleName);
            }
        }
    }
}
