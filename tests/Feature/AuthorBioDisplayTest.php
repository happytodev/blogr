<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('password123'),
        'avatar' => 'avatars/john.jpg',
        'bio' => 'This is John bio',
    ]);

    $this->category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);
});

test('blog post has author relation', function () {
    $post = BlogPost::create([
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    expect($post->author)->not->toBeNull();
    expect($post->author->id)->toBe($this->user->id);
    expect($post->author->name)->toBe('John Doe');
});

test('author has bio and avatar attributes', function () {
    $post = BlogPost::create([
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    expect($post->author->bio)->toBe('This is John bio');
    expect($post->author->avatar)->toBe('avatars/john.jpg');
});

test('author relation works same as user relation', function () {
    $post = BlogPost::create([
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    expect($post->author->id)->toBe($post->user->id);
    expect($post->author->name)->toBe($post->user->name);
    expect($post->author->bio)->toBe($post->user->bio);
    expect($post->author->avatar)->toBe($post->user->avatar);
});
