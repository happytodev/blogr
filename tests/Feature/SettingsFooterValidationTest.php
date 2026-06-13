<?php

use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\TestCase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(TestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('saves settings with empty footer URL fields without validation error', function () {
    $this->actingAs($this->admin);

    Livewire::test(BlogrSettings::class)
        ->fillForm([
            'footer_enabled' => true,
            'footer_show_social_links' => true,
            'footer_twitter' => '',
            'footer_github' => '',
            'footer_linkedin' => '',
            'footer_facebook' => '',
            'footer_bluesky' => '',
            'footer_youtube' => '',
            'footer_instagram' => '',
            'footer_tiktok' => '',
            'footer_mastodon' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('saves settings with valid footer URLs', function () {
    $this->actingAs($this->admin);

    Livewire::test(BlogrSettings::class)
        ->fillForm([
            'footer_enabled' => true,
            'footer_show_social_links' => true,
            'footer_twitter' => 'https://twitter.com/test',
            'footer_github' => 'https://github.com/test',
            'footer_linkedin' => 'https://linkedin.com/in/test',
            'footer_facebook' => 'https://facebook.com/test',
            'footer_bluesky' => 'https://bsky.app/profile/test',
            'footer_youtube' => 'https://youtube.com/@test',
            'footer_instagram' => 'https://instagram.com/test',
            'footer_tiktok' => 'https://tiktok.com/@test',
            'footer_mastodon' => 'https://mastodon.social/@test',
        ])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('does not require footer fields when footer is disabled', function () {
    $this->actingAs($this->admin);

    Livewire::test(BlogrSettings::class)
        ->fillForm([
            'footer_enabled' => false,
            'footer_show_social_links' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();
});
