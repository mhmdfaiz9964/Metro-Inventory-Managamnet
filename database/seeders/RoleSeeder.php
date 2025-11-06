<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $userRole  = Role::firstOrCreate(['name' => 'User']);

        $adminUser = User::find(1);
        if ($adminUser) {
            $adminUser->assignRole($adminRole);
        }

        $normalUser = User::find(2);
        if ($normalUser) {
            $normalUser->assignRole($userRole);
        }
    }
}
