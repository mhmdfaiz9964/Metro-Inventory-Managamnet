<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class UpdateMemberRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if "Member" role exists
        $memberRole = Role::where('name', 'Member')->first();

        if ($memberRole) {
            // Rename "Member" role to "Manager"
            $memberRole->name = 'Manager';
            $memberRole->save();

            // Update users who had "Member" role
            $users = User::role('Member')->get();
            foreach ($users as $user) {
                $user->removeRole('Member');
                $user->assignRole('Manager');
            }
        } else {
            // If "Member" role doesn't exist, just ensure "Manager" exists
            Role::firstOrCreate(['name' => 'Manager']);
        }
    }
}
