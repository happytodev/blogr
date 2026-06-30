<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

use Happytodev\Blogr\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $this->user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('password123'),
        'slug' => 'john-doe',
        'avatar' => 'avatars/existing-avatar.jpg',
        'bio' => 'Original bio',
    ]);
    $this->user->assignRole('admin');

    $this->actingAs($this->user);
});

test('it can update user name without touching avatar', function () {
    $this->user->name = 'Jane Smith';
    $this->user->save();

    $this->user->refresh();
    expect($this->user->name)->toBe('Jane Smith');
    expect($this->user->avatar)->toBe('avatars/existing-avatar.jpg');
});

test('it handles empty avatar array', function () {
    $this->user->avatar = null;
    $this->user->save();

    $this->user->name = 'Jane Smith';
    $this->user->save();

    $this->user->refresh();
    expect($this->user->name)->toBe('Jane Smith');
    expect($this->user->avatar)->toBeNull();
});

test('it does not save when name is empty', function () {
    $originalName = $this->user->name;

    $this->user->name = '';
    $this->user->save();

    $this->user->refresh();
    expect($this->user->name)->toBe('');
});
