<?php

use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Filament\Facades\Filament;

beforeEach(function () {
    // Create roles
    \Spatie\Permission\Models\Role::create(['name' => 'admin']);
    \Spatie\Permission\Models\Role::create(['name' => 'writer']);
});

it('prevents writer from accessing settings page', function () {
    $writer = User::factory()->create();
    $writer->assignRole('writer');

    // Set the auth user for Filament
    Filament::auth()->login($writer);
    
    expect(BlogrSettings::canAccess())->toBeFalse();
});

it('allows admin to access settings page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Set the auth user for Filament
    Filament::auth()->login($admin);
    
    expect(BlogrSettings::canAccess())->toBeTrue();
});

it('prevents users without any role from accessing settings', function () {
    $user = User::factory()->create();
    // No role assigned

    Filament::auth()->login($user);
    
    expect(BlogrSettings::canAccess())->toBeFalse();
});
