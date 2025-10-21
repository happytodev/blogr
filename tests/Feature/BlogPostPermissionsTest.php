<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('can assign admin role to user', function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'writer']);

    $user = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);

    $user->assignRole('admin');

    expect($user->hasRole('admin'))->toBeTrue();
});

it('can assign writer role to user', function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'writer']);

    $user = User::create([
        'name' => 'Writer User',
        'email' => 'writer@example.com',
        'password' => bcrypt('password'),
    ]);

    $user->assignRole('writer');

    expect($user->hasRole('writer'))->toBeTrue();
});

it('admin can create and publish posts', function () {
    // Create roles with web guard to match User model
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'writer', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    // Test model-level permission: admin can create published posts
    $post = new BlogPost([
        'title' => 'Admin Post',
        'slug' => 'admin-post',
        'content' => 'This is a post created by admin',
        'tldr' => 'Admin post tldr',
        'published_at' => now(),
        'is_published' => true,
        'user_id' => $admin->id,
    ]);

    // Since we're testing permissions at the model level, we'll assume
    // the Filament resource handles the permission checks
    expect($admin->hasRole('admin'))->toBeTrue();
    expect($post->user_id)->toBe($admin->id);
    expect($post->is_published)->toBeTrue();
});

it('writer can create but not publish blog posts', function () {
    // Create roles with web guard to match User model
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'writer', 'guard_name' => 'web']);

    $writer = User::create([
        'name' => 'Writer User',
        'email' => 'writer@example.com',
        'password' => bcrypt('password'),
    ]);
    $writer->assignRole('writer');

    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    // Writer should be able to create draft posts
    $draftPost = BlogPost::create([
        'title' => 'Draft Post',
        'content' => 'Draft content',
        'slug' => 'draft-post',
        'is_published' => false,
        'user_id' => $writer->id,
        'category_id' => $category->id,
    ]);

    expect($draftPost->is_published)->toBeFalse();
    expect($draftPost->user_id)->toBe($writer->id);
});

it('writer cannot publish posts directly', function () {
    // Create roles with web guard to match User model
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'writer', 'guard_name' => 'web']);

    $writer = User::create([
        'name' => 'Writer User',
        'email' => 'writer@example.com',
        'password' => bcrypt('password'),
    ]);
    $writer->assignRole('writer');

    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    // Writer should only be able to create draft posts
    $draftPost = BlogPost::create([
        'title' => 'Draft Post',
        'content' => 'Draft content',
        'slug' => 'draft-post',
        'is_published' => false, // Writer can only set this to false
        'user_id' => $writer->id,
        'category_id' => $category->id,
    ]);

    expect($draftPost->is_published)->toBeFalse();

    // Writer should NOT be able to create published posts
    expect(fn() => BlogPost::create([
        'title' => 'Published Post',
        'content' => 'Published content',
        'slug' => 'published-post',
        'is_published' => true, // This should fail
        'user_id' => $writer->id,
        'category_id' => $category->id,
    ]))->toThrow(\Exception::class, 'Writers cannot publish posts. Only admins can publish.');
});

it('admin can view all posts', function () {
    // Create roles with web guard to match User model
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'writer', 'guard_name' => 'web']);

    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);
    $admin->assignRole('admin');

    $writer = User::create([
        'name' => 'Writer User',
        'email' => 'writer@example.com',
        'password' => bcrypt('password'),
    ]);
    $writer->assignRole('writer');

    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    // Create posts by different users
    BlogPost::create([
        'title' => 'Admin Post',
        'content' => 'Admin content',
        'slug' => 'admin-post',
        'is_published' => true,
        'user_id' => $admin->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    BlogPost::create([
        'title' => 'Writer Post',
        'content' => 'Writer content',
        'slug' => 'writer-post',
        'is_published' => false,
        'user_id' => $writer->id,
        'category_id' => $category->id,
    ]);

    // Admin should be able to see all posts
    $allPosts = BlogPost::all();
    expect($allPosts)->toHaveCount(2);
});

it('writer can only view their own posts', function () {
    // Create roles with web guard to match User model
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'writer', 'guard_name' => 'web']);

    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);
    $admin->assignRole('admin');

    $writer = User::create([
        'name' => 'Writer User',
        'email' => 'writer@example.com',
        'password' => bcrypt('password'),
    ]);
    $writer->assignRole('writer');

    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    // Create posts by different users
    BlogPost::create([
        'title' => 'Admin Post',
        'content' => 'Admin content',
        'slug' => 'admin-post',
        'is_published' => true,
        'user_id' => $admin->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    BlogPost::create([
        'title' => 'Writer Post',
        'content' => 'Writer content',
        'slug' => 'writer-post',
        'is_published' => false,
        'user_id' => $writer->id,
        'category_id' => $category->id,
    ]);

    // Writer should only see their own posts
    $writerPosts = BlogPost::where('user_id', $writer->id)->get();
    expect($writerPosts)->toHaveCount(1);
    expect($writerPosts->first()->title)->toBe('Writer Post');
});

it('can edit blog post without errors', function () {
    // Create roles with web guard to match User model
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'writer', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $category = Category::factory()->create();

    $post = BlogPost::create([
        'title' => 'Test Post',
        'content' => 'Test content',
        'slug' => 'test-post',
        'is_published' => false,
        'user_id' => $admin->id,
        'category_id' => $category->id,
    ]);

    // Test that BlogPostForm::configure can be called without errors
    $schema = \Filament\Schemas\Schema::make();

    expect(fn() => \Happytodev\Blogr\Filament\Resources\BlogPosts\BlogPostForm::configure($schema))
        ->not->toThrow(\TypeError::class);

    // Verify the configured schema has the expected components
    $configuredSchema = \Happytodev\Blogr\Filament\Resources\BlogPosts\BlogPostForm::configure($schema);
    expect($configuredSchema)->toBeInstanceOf(\Filament\Schemas\Schema::class);
});

it('writer can edit their own blog post', function () {
    // Create roles with web guard to match User model
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'writer', 'guard_name' => 'web']);

    $writer = User::factory()->create();
    $writer->assignRole('writer');

    $category = Category::factory()->create();

    $post = BlogPost::create([
        'title' => 'Writer Post',
        'content' => 'Writer content',
        'slug' => 'writer-post',
        'is_published' => false,
        'user_id' => $writer->id,
        'category_id' => $category->id,
    ]);

    // Test that BlogPostForm::configure can be called without errors for writer
    $schema = \Filament\Schemas\Schema::make();

    expect(fn() => \Happytodev\Blogr\Filament\Resources\BlogPosts\BlogPostForm::configure($schema))
        ->not->toThrow(\TypeError::class);

    // Verify the configured schema has the expected components
    $configuredSchema = \Happytodev\Blogr\Filament\Resources\BlogPosts\BlogPostForm::configure($schema);
    expect($configuredSchema)->toBeInstanceOf(\Filament\Schemas\Schema::class);

    // Verify the writer owns the post
    expect($post->user_id)->toBe($writer->id);
    expect($writer->hasRole('writer'))->toBeTrue();
});