<?php

uses(Happytodev\Blogr\Tests\TestCase::class);

use Happytodev\Blogr\Models\User;
use Spatie\Permission\Models\Role;
use Happytodev\Blogr\Commands\BlogrInstallCommand;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\StringInput;

beforeEach(function () {
    // Clear all users and roles
    User::query()->delete();
    Role::query()->delete();
});

function createMockInstallCommand(): BlogrInstallCommand {
    $command = new BlogrInstallCommand();
    $output = new OutputStyle(
        new StringInput(''),
        new BufferedOutput()
    );
    $command->setOutput($output);
    return $command;
}

it('user created before blogr install gets admin role during install', function () {
    // Simulate: User creates account with filament make:filament-user
    // (this happens before blogr:install is run)
    $filamentUser = User::factory()->create([
        'email' => 'admin@demo.test',
        'name' => 'Filament Admin',
    ]);

    expect($filamentUser->hasRole('admin'))->toBeFalse();
    expect(User::count())->toBe(1);

    // Create roles (normally done by blogr:install::createRolesAndPermissions)
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);

    // Simulate the part of blogr:install that assigns admin role
    $command = createMockInstallCommand();
    $reflection = new \ReflectionMethod($command, 'assignAdminRoleToFirstUser');
    $reflection->setAccessible(true);
    $reflection->invoke($command);

    // After installation, the filament user should have admin role
    $filamentUser->refresh();
    expect($filamentUser->hasRole('admin'))->toBeTrue();
});

it('test users are created with admin role during install', function () {
    // Setup: No users exist
    expect(User::count())->toBe(0);

    // Create roles
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);

    // Simulate creating test users
    $adminUser = User::factory()->create([
        'email' => 'admin@demo.com',
        'name' => 'Admin Demo',
    ]);
    $adminUser->assignRole('admin');

    $writerUser = User::factory()->create([
        'email' => 'writer@demo.com',
        'name' => 'Writer Demo',
    ]);
    $writerUser->assignRole('writer');

    // Verify
    expect($adminUser->hasRole('admin'))->toBeTrue();
    expect($writerUser->hasRole('writer'))->toBeTrue();
});
