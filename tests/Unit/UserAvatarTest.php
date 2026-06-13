<?php

use Happytodev\Blogr\Models\User;

it('returns gravatar url for user with email', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    expect($user->gravatar_url)->toBe(
        'https://www.gravatar.com/avatar/' . md5('test@example.com') . '?s=80&d=mp'
    );
});

it('returns null gravatar url for user without email', function () {
    $user = User::factory()->make(['email' => null]);

    expect($user->gravatar_url)->toBeNull();
});

it('prioritises avatar_url in filament avatar url', function () {
    $user = User::factory()->create([
        'avatar_url' => 'avatars/breezy.jpg',
        'avatar' => 'avatars/old.jpg',
        'email' => 'test@example.com',
    ]);

    $url = $user->getFilamentAvatarUrl();

    expect($url)->toContain('avatars/breezy.jpg')
        ->and($url)->not->toContain('avatars/old.jpg')
        ->and($url)->not->toContain('gravatar.com');
});

it('falls back to avatar when avatar_url is null', function () {
    $user = User::factory()->create([
        'avatar_url' => null,
        'avatar' => 'avatars/old.jpg',
        'email' => 'test@example.com',
    ]);

    $url = $user->getFilamentAvatarUrl();

    expect($url)->toContain('avatars/old.jpg')
        ->and($url)->not->toContain('gravatar.com');
});

it('falls back to gravatar when all avatar fields are null', function () {
    $user = User::factory()->create([
        'avatar_url' => null,
        'avatar' => null,
        'email' => 'test@example.com',
    ]);

    $url = $user->getFilamentAvatarUrl();

    expect($url)->toContain('gravatar.com')
        ->and($url)->toContain(md5('test@example.com'));
});

it('returns null from filament avatar url when no avatar and no email', function () {
    $user = User::factory()->make([
        'avatar_url' => null,
        'avatar' => null,
        'email' => null,
    ]);

    expect($user->getFilamentAvatarUrl())->toBeNull();
});

it('has avatar_url in fillable', function () {
    $user = new User;

    expect($user->getFillable())->toContain('avatar_url');
});
