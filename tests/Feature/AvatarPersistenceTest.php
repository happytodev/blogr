<?php

use Happytodev\Blogr\Models\User;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(Happytodev\Blogr\Tests\LocalizedTestCase::class);

beforeEach(function () {
    Storage::fake('public');

    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
    ]);
    $this->admin->assignRole('admin');
});

it('persists avatar_url in database', function () {
    $this->admin->update([
        'avatar_url' => 'avatars/test-avatar.jpg',
    ]);

    $freshUser = User::find($this->admin->id);

    expect($freshUser->avatar_url)->toBe('avatars/test-avatar.jpg');
});

it('persists avatar_url with stored file', function () {
    Storage::disk('public')->put('avatars/persisted.jpg', 'fake-image-content');

    $this->admin->update([
        'avatar_url' => 'avatars/persisted.jpg',
    ]);

    $freshUser = User::find($this->admin->id);

    expect($freshUser->avatar_url)->toBe('avatars/persisted.jpg')
        ->and(Storage::disk('public')->exists('avatars/persisted.jpg'))->toBeTrue();
});

it('prioritises avatar_url over avatar in filament avatar url', function () {
    $this->admin->update([
        'avatar_url' => 'avatars/breezy-upload.jpg',
        'avatar' => 'avatars/legacy-upload.jpg',
    ]);

    $url = $this->admin->getFilamentAvatarUrl();

    expect($url)->toContain('avatars/breezy-upload.jpg')
        ->and($url)->not->toContain('avatars/legacy-upload.jpg');
});

it('returns gravatar when no avatar fields are set', function () {
    $user = User::factory()->create([
        'avatar_url' => null,
        'avatar' => null,
        'email' => 'nobody@test.com',
    ]);

    $url = $user->getFilamentAvatarUrl();

    expect($url)->toContain('gravatar.com');
});

it('has avatar_url in fillable for mass assignment', function () {
    $user = new User;

    expect($user->getFillable())->toContain('avatar_url');
});
