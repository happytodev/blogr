<?php

namespace Happytodev\Blogr\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        // Create admin user
        $admin = $userModel::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role
        if (method_exists($admin, 'assignRole')) {
            $admin->assignRole('admin');
        }

        // Create writer user
        $writer = $userModel::firstOrCreate(
            ['email' => 'writer@demo.com'],
            [
                'name' => 'Writer User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign writer role
        if (method_exists($writer, 'assignRole')) {
            $writer->assignRole('writer');
        }

        $this->command->info('✅ Test users created successfully.');
        $this->command->info('   • admin@demo.com (password: password) - Admin role');
        $this->command->info('   • writer@demo.com (password: password) - Writer role');
    }
}
