<?php

use Workbench\App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('user model has slug field', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'slug' => 'john-doe',
    ]);

    expect($user->slug)->toBe('john-doe');
});

test('user slug must be unique', function () {
    User::factory()->create([
        'slug' => 'johndoe',
    ]);

    $this->expectException(\Illuminate\Database\QueryException::class);
    
    User::factory()->create([
        'slug' => 'johndoe',
    ]);
});

test('user can have a null slug', function () {
    $user = User::factory()->create([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'slug' => null,
    ]);

    expect($user->slug)->toBeNull();
});

test('slug can be auto-generated from name', function () {
    $name = 'Test User Name';
    $expectedSlug = Str::slug($name);
    
    expect($expectedSlug)->toBe('test-user-name');
});
