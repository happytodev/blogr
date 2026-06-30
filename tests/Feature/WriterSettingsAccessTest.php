<?php

use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\TestCase;
use Spatie\Permission\Models\Role;

uses(TestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'writer', 'guard_name' => 'web']);
});

it('prevents writer from accessing settings page', function () {
    $writer = User::factory()->create();
    $writer->assignRole('writer');

    $this->actingAs($writer);

    $this->get(BlogrSettings::getUrl())->assertForbidden();
});

it('allows admin to access settings page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    $this->get(BlogrSettings::getUrl())->assertSuccessful();
});

it('prevents users without any role from accessing settings', function () {
    $user = User::factory()->create();
    // No role assigned

    $this->actingAs($user);

    $this->get(BlogrSettings::getUrl())->assertForbidden();
});
