<?php

use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\TestCase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(TestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);
});

it('has enable_avatar_upload toggle in settings', function () {
    $response = $this->get(BlogrSettings::getUrl());

    $response->assertStatus(200);
    $response->assertSee('Enable avatar upload on profile page');
});

it('defaults enable_avatar_upload to true in config', function () {
    expect(config('blogr.enable_avatar_upload', null))->toBeTrue();
});

it('saves enable_avatar_upload as false and persists', function () {
    Livewire::test(BlogrSettings::class)
        ->fillForm([
            'enable_avatar_upload' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // The config should now be false
    expect(config('blogr.enable_avatar_upload'))->toBeFalse();
});

it('saves enable_avatar_upload as true', function () {
    Livewire::test(BlogrSettings::class)
        ->fillForm([
            'enable_avatar_upload' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(config('blogr.enable_avatar_upload'))->toBeTrue();
});
