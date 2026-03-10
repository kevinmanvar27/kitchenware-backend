<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class RekTechAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'RekTech Admin',
            'email' => 'rektech.uk@gmail.com',
            'password' => Hash::make('RekTech@27'),
            'user_role' => 'super_admin',
        ]);

        // Assign super_admin role
        $role = Role::where('name', 'super_admin')->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }
    }
}