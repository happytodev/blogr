<?php

uses(Happytodev\Blogr\Tests\TestCase::class);

use Happytodev\Blogr\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles for testing
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);
});

it('admin user can view any users', function () {
    // Create admin and regular user
    $admin = User::factory()->create();
    $admin->syncRoles('admin');
    
    // Admin can view any users
    expect($admin->can('viewAny', User::class))->toBeTrue();
});

it('writer user cannot view users', function () {
    // Create writer user
    $writer = User::factory()->create();
    $writer->syncRoles('writer');
    
    // Writer cannot view users
    expect($writer->can('viewAny', User::class))->toBeFalse();
});

it('admin can create users', function () {
    $admin = User::factory()->create();
    $admin->syncRoles('admin');
    
    expect($admin->can('create', User::class))->toBeTrue();
});

it('admin can edit users', function () {
    $admin = User::factory()->create();
    $admin->syncRoles('admin');
    
    $targetUser = User::factory()->create();
    
    expect($admin->can('update', $targetUser))->toBeTrue();
});

it('admin can delete users', function () {
    $admin = User::factory()->create();
    $admin->syncRoles('admin');
    
    $targetUser = User::factory()->create();
    
    expect($admin->can('delete', $targetUser))->toBeTrue();
});

it('writer cannot perform user management actions', function () {
    $writer = User::factory()->create();
    $writer->syncRoles('writer');
    
    $targetUser = User::factory()->create();
    
    expect($writer->can('create', User::class))->toBeFalse();
    expect($writer->can('update', $targetUser))->toBeFalse();
    expect($writer->can('delete', $targetUser))->toBeFalse();
});
