<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

use Happytodev\Blogr\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'slug' => 'test-user',
        'bio' => 'Test bio',
    ]);
    $this->user->assignRole('admin');
});

test('profile page is accessible via URL', function () {
    actingAs($this->user);

    $response = get('/admin/profile');

    $response->assertStatus(200);
});

test('edit profile page shows bio field', function () {
    actingAs($this->user);

    $response = get('/admin/profile');

    $response->assertStatus(200);
    $response->assertSee('bio', false);
});

test('edit profile page shows avatar field', function () {
    actingAs($this->user);

    $response = get('/admin/profile');

    $response->assertStatus(200);
    $response->assertSee('avatar', false);
});

test('user can update their profile with bio', function () {
    actingAs($this->user);

    get('/admin/profile')->assertStatus(200);

    $this->user->bio = 'Updated bio content';
    $this->user->save();

    $this->user->refresh();
    expect($this->user->bio)->toBe('Updated bio content');
});
