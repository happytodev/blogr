<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource;
use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\CreateBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\TestCase;
use Spatie\Permission\Models\Role;

uses(TestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    config()->set('blogr.auto_save_interval', 30);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);

    Category::factory()->create();
});

it('shows auto-save indicator on create blog post page', function () {
    $response = $this->get(CreateBlogPost::getUrl());
    $response->assertStatus(200);
    $response->assertSee('auto-save-indicator', false);
});

it('shows auto-save indicator on edit blog post page', function () {
    $post = BlogPost::factory()->create();

    $response = $this->get(BlogPostResource::getUrl('edit', ['record' => $post]));
    $response->assertStatus(200);
    $response->assertSee('auto-save-indicator', false);
});
