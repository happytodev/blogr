<?php

use Happytodev\Blogr\Filament\Livewire\AuthorBio;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\LocalizedTestCase;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

if (! class_exists(MyProfileComponent::class)) {
    return;
}

uses(LocalizedTestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    config()->set('blogr.translation', [
        'provider' => 'libretranslate',
        'libretranslate' => ['url' => 'http://localhost:5000'],
    ]);

    $this->admin = User::factory()->create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'bio' => [
            'en' => 'Hello from the admin',
            'fr' => 'Bonjour de la part de l\'admin',
        ],
    ]);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('mounts with existing bio data', function () {
    Livewire::test(AuthorBio::class)
        ->assertSet('data.bio.en', 'Hello from the admin')
        ->assertSet('data.bio.fr', "Bonjour de la part de l'admin");
});

it('mounts with empty bio when user has none', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user);

    Livewire::test(AuthorBio::class)
        ->assertSet('data.bio', ['en' => null, 'fr' => null]);
});

it('shows translate with AI button when provider is configured', function () {
    Livewire::test(AuthorBio::class)
        ->assertSee('Translate with AI');
});

it('has bio tabs for each configured locale', function () {
    Livewire::test(AuthorBio::class)
        ->assertSee('English')
        ->assertSee('Français');
});

it('returns only locales with content as source options', function () {
    $component = Livewire::test(AuthorBio::class);

    $sourceOptions = $component->instance()->getSourceLocaleOptions();

    expect($sourceOptions)->toHaveKeys(['en', 'fr'])
        ->and($sourceOptions)->not->toHaveKeys(['de']);
});
