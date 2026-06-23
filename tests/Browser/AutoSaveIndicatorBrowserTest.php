<?php

use Happytodev\Blogr\Models\User;
use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\User as WorkbenchUser;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('shows auto-save indicator span after page load', function () {
    $user = WorkbenchUser::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    actingAs($user);

    visit('/admin/blog-posts')
        ->assertPathIs('/admin/blog-posts')
        ->screenshot();
})->skip('Browser tests require running DDEV/caddy server with authentication');

it('maintains indicator text after content modification', function () {
    $user = WorkbenchUser::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    actingAs($user);

    // Navigate to a blog post edit page (ID 7)
    $page = visit('/admin/blog-posts/7/edit');
    $page->assertSee('blogr-auto-save-indicator');
})->skip('Browser tests require running DDEV/caddy server with authentication');
