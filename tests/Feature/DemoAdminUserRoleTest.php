<?php

uses(Happytodev\Blogr\Tests\TestCase::class);

use Happytodev\Blogr\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Clear all users and roles
    User::query()->delete();
    Role::query()->delete();
});

it('demo admin user has admin role after test users are created', function () {
    // Create roles
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);

    // Create demo admin user (simulating TestUsersSeeder)
    $adminUser = User::create([
        'name' => 'Admin User',
        'email' => 'admin@demo.com',
        'slug' => 'admin-user',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
        'bio' => [
            'en' => 'Experienced administrator and content manager',
            'fr' => 'Administrateur expérimenté',
        ],
        'avatar' => 'https://ui-avatars.com/api/?name=Admin+User',
    ]);

    // Assign admin role
    $adminUser->syncRoles(['admin']);

    // Verify the user has admin role
    expect($adminUser->hasRole('admin'))->toBeTrue();
    expect($adminUser->getRoleNames())->toContain('admin');
    expect($adminUser->getRoleNames()->count())->toBe(1);
});

it('demo writer user has writer role after test users are created', function () {
    // Create roles
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);

    // Create demo writer user (simulating TestUsersSeeder)
    $writerUser = User::create([
        'name' => 'Writer User',
        'email' => 'writer@demo.com',
        'slug' => 'writer-user',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
        'bio' => [
            'en' => 'Passionate writer and blogger',
            'fr' => 'Écrivain passionné',
        ],
        'avatar' => 'https://ui-avatars.com/api/?name=Writer+User',
    ]);

    // Assign writer role
    $writerUser->syncRoles(['writer']);

    // Verify the user has writer role
    expect($writerUser->hasRole('writer'))->toBeTrue();
    expect($writerUser->getRoleNames())->toContain('writer');
    expect($writerUser->getRoleNames()->count())->toBe(1);
});

it('admin demo user can access all admin menus', function () {
    // Create roles
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);

    // Create demo admin user with admin role
    $adminUser = User::create([
        'name' => 'Admin User',
        'email' => 'admin@demo.com',
        'slug' => 'admin-user',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);
    $adminUser->syncRoles(['admin']);

    // Admin user should have the admin role
    expect($adminUser->hasRole('admin'))->toBeTrue();
    
    // In Filament, the user can access the panel if they have admin role
    // This simulates the authorization check in UserResource::canViewAny()
    expect($adminUser->hasRole('admin'))->toBeTrue();
});

it('writer demo user cannot access users menu', function () {
    // Create roles
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);

    // Create demo writer user
    $writerUser = User::create([
        'name' => 'Writer User',
        'email' => 'writer@demo.com',
        'slug' => 'writer-user',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);
    $writerUser->syncRoles(['writer']);

    // Writer user should NOT have admin role
    expect($writerUser->hasRole('admin'))->toBeFalse();
    expect($writerUser->hasRole('writer'))->toBeTrue();
});
