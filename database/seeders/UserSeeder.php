<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use HasinHayder\Tyro\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure super_admin, admin and user roles exist
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            ['slug' => 'super_admin']
        );

        $adminRole = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['slug' => 'admin']
        );

        $userRole = Role::firstOrCreate(
            ['name' => 'User'],
            ['slug' => 'user']
        );

        // Create default super_admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'su_admin@example.com'],
            [
                'name' => 'Super Admin User',
                'password' => Hash::make('laureal'),
            ]
        );

        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole($superAdminRole);
        }

        // Create default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }

        // Create demo user
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password123'),
            ]
        );

        if (!$user->hasRole('user')) {
            $user->assignRole($userRole);
        }
    }
}
