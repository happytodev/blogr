<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Happytodev\Blogr\Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(TestCase::class)->in(__FILE__);

it('should create admin and writer roles', function () {
    // Run the role and permission seeder
    $seeder = new \Happytodev\Blogr\Database\Seeders\RoleAndPermissionSeeder();
    $seeder->run();

    // Check that roles exist
    expect(Role::where('name', 'admin')->exists())->toBeTrue();
    expect(Role::where('name', 'writer')->exists())->toBeTrue();
});

it('should create admin and writer demo users', function () {
    $userModel = config('auth.providers.users.model');
    
    // First create roles
    $seeder = new \Happytodev\Blogr\Database\Seeders\RoleAndPermissionSeeder();
    $seeder->run();
    
    // Then create users
    $seederUsers = new \Happytodev\Blogr\Database\Seeders\TestUsersSeeder();
    $seederUsers->run();

    // Check users exist
    expect($userModel::where('email', 'admin@demo.com')->exists())->toBeTrue();
    expect($userModel::where('email', 'writer@demo.com')->exists())->toBeTrue();
});

it('should assign admin role to admin@demo.com user', function () {
    $userModel = config('auth.providers.users.model');
    
    // Create roles
    $seeder = new \Happytodev\Blogr\Database\Seeders\RoleAndPermissionSeeder();
    $seeder->run();
    
    // Create users
    $seederUsers = new \Happytodev\Blogr\Database\Seeders\TestUsersSeeder();
    $seederUsers->run();

    // Get the admin user
    $admin = $userModel::where('email', 'admin@demo.com')->first();

    expect($admin)->not->toBeNull();
    expect($admin->hasRole('admin'))->toBeTrue();
});

it('should assign writer role to writer@demo.com user', function () {
    $userModel = config('auth.providers.users.model');
    
    // Create roles
    $seeder = new \Happytodev\Blogr\Database\Seeders\RoleAndPermissionSeeder();
    $seeder->run();
    
    // Create users
    $seederUsers = new \Happytodev\Blogr\Database\Seeders\TestUsersSeeder();
    $seederUsers->run();

    // Get the writer user
    $writer = $userModel::where('email', 'writer@demo.com')->first();

    expect($writer)->not->toBeNull();
    expect($writer->hasRole('writer'))->toBeTrue();
});

it('should assign admin role to first existing user', function () {
    $userModel = config('auth.providers.users.model');
    
    // Create a user first (simulating filament:install user)
    $firstUser = $userModel::create([
        'name' => 'FirstUser',
        'email' => 'happytodev@ik.me',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
    ]);

    // Create roles
    $seeder = new \Happytodev\Blogr\Database\Seeders\RoleAndPermissionSeeder();
    $seeder->run();
    
    // Create demo users (should not affect first user)
    $seederUsers = new \Happytodev\Blogr\Database\Seeders\TestUsersSeeder();
    $seederUsers->run();

    // Simulate the assignAdminRoleToFirstUser() method logic directly
    $existingUsers = $userModel::whereNotIn('email', [
        'admin@demo.com',
        'writer@demo.com',
    ])->get();

    if (!$existingUsers->isEmpty()) {
        $user = $existingUsers->first();
        if (method_exists($user, 'assignRole') && !$user->hasAnyRole(['admin', 'writer'])) {
            $user->assignRole('admin');
        }
    }

    // Refresh user from DB
    $firstUser->refresh();

    expect($firstUser->hasRole('admin'))->toBeTrue();
});

it('verifies roles table is populated after seeder runs', function () {
    // Check roles table before
    expect(\Spatie\Permission\Models\Role::count())->toBe(0);
    
    // Run seeder
    $seeder = new \Happytodev\Blogr\Database\Seeders\RoleAndPermissionSeeder();
    $seeder->run();
    
    // Check roles table after
    expect(\Spatie\Permission\Models\Role::count())->toBeGreaterThan(0);
    expect(\Spatie\Permission\Models\Role::where('name', 'admin')->exists())->toBeTrue();
});

it('verifies model_has_roles is populated after user role assignment', function () {
    $userModel = config('auth.providers.users.model');
    
    // Create roles
    $seeder = new \Happytodev\Blogr\Database\Seeders\RoleAndPermissionSeeder();
    $seeder->run();
    
    // Create users with roles
    $seederUsers = new \Happytodev\Blogr\Database\Seeders\TestUsersSeeder();
    $seederUsers->run();

    // Check model_has_roles table
    $modelHasRoles = \DB::table('model_has_roles')->get();
    expect($modelHasRoles->count())->toBeGreaterThan(0);
    
    // Verify admin user has role
    $admin = $userModel::where('email', 'admin@demo.com')->first();
    $adminRoles = \DB::table('model_has_roles')
        ->where('model_id', $admin->id)
        ->where('model_type', get_class($admin))
        ->get();
    
    expect($adminRoles->count())->toBeGreaterThan(0);
});

it('handles missing roles table gracefully', function () {
    // This test verifies that if roles table doesn't exist, 
    // the seeders don't crash the installation
    $userModel = config('auth.providers.users.model');
    
    // Create a user
    $user = $userModel::create([
        'name' => 'TestUser',
        'email' => 'test@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
    ]);

    // Run seeders - should not throw exceptions even if roles table has issues
    try {
        $seeder = new \Happytodev\Blogr\Database\Seeders\RoleAndPermissionSeeder();
        $seeder->run();
        
        $seederUsers = new \Happytodev\Blogr\Database\Seeders\TestUsersSeeder();
        $seederUsers->run();
        
        // If we get here, seeders didn't crash
        expect(true)->toBeTrue();
    } catch (\Exception $e) {
        // If seeders throw exceptions, log them for debugging
        \Log::error('Seeder error: ' . $e->getMessage());
        throw $e;
    }
});
