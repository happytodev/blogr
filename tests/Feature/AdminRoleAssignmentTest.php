<?php

uses(Happytodev\Blogr\Tests\TestCase::class);

use Happytodev\Blogr\Commands\BlogrInstallCommand;
use Happytodev\Blogr\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\StringInput;

beforeEach(function () {
    // Clear all users and roles
    User::query()->delete();
    Role::query()->delete();
});

function createMockCommand(): BlogrInstallCommand {
    $command = new BlogrInstallCommand();
    $output = new OutputStyle(
        new StringInput(''),
        new BufferedOutput()
    );
    $command->setOutput($output);
    return $command;
}

it('assigns admin role to first user created before blogr install', function () {
    // Setup: Create a user BEFORE running blogr:install
    $user = User::factory()->create([
        'email' => 'existing@demo.test',
        'name' => 'Existing User',
    ]);

    // Create roles and permissions
    createRolesAndPermissions();

    // Run the installation logic that assigns admin role
    $command = createMockCommand();
    
    $reflection = new \ReflectionMethod($command, 'assignAdminRoleToFirstUser');
    $reflection->setAccessible(true);
    $reflection->invoke($command);

    // Assert: User should have admin role
    expect($user->fresh()->hasRole('admin'))->toBeTrue();
});

it('does not reassign admin role if user already has one', function () {
    // Setup: Create user with existing admin role
    $user = User::factory()->create([
        'email' => 'admin@demo.test',
        'name' => 'Admin User',
    ]);

    createRolesAndPermissions();
    $user->assignRole('admin');

    // Verify initial state
    expect($user->hasRole('admin'))->toBeTrue();

    // Run the assignment method
    $command = createMockCommand();
    
    $reflection = new \ReflectionMethod($command, 'assignAdminRoleToFirstUser');
    $reflection->setAccessible(true);
    $reflection->invoke($command);

    // Assert: User should still have admin role (not added twice)
    expect($user->fresh()->hasRole('admin'))->toBeTrue();
    expect($user->fresh()->getRoleNames()->count())->toBe(1);
});

it('assigns admin role to non-test users only', function () {
    // Setup: Create multiple users
    $user1 = User::factory()->create([
        'email' => 'admin@demo.com',
    ]);

    $user2 = User::factory()->create([
        'email' => 'writer@demo.com',
    ]);

    $user3 = User::factory()->create([
        'email' => 'real-user@example.com',
    ]);

    createRolesAndPermissions();

    // Run the assignment
    $command = createMockCommand();
    
    $reflection = new \ReflectionMethod($command, 'assignAdminRoleToFirstUser');
    $reflection->setAccessible(true);
    $reflection->invoke($command);

    // Assert: Only the real user should have admin role
    expect($user3->fresh()->hasRole('admin'))->toBeTrue();
    expect($user1->fresh()->hasRole('admin'))->toBeFalse();
    expect($user2->fresh()->hasRole('admin'))->toBeFalse();
});

it('does nothing if no non-test users exist', function () {
    // Setup: Only test users exist
    $user1 = User::factory()->create([
        'email' => 'admin@demo.com',
    ]);

    $user2 = User::factory()->create([
        'email' => 'writer@demo.com',
    ]);

    createRolesAndPermissions();

    // Run the assignment
    $command = createMockCommand();
    
    $reflection = new \ReflectionMethod($command, 'assignAdminRoleToFirstUser');
    $reflection->setAccessible(true);
    $reflection->invoke($command);

    // Assert: No users should have admin role
    expect($user1->fresh()->hasRole('admin'))->toBeFalse();
    expect($user2->fresh()->hasRole('admin'))->toBeFalse();
});

function createRolesAndPermissions(): void {
    $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $writer = Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);
}
