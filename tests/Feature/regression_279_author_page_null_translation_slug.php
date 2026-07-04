<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    config(['blogr.author_profile.enabled' => true]);

    $this->author = User::create([
        'name' => 'Test Author',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'slug' => 'test-author',
    ]);

    $this->category = Category::create([
        'name' => 'Tech',
        'slug' => 'tech',
    ]);
});

test('author page renders a post and generates blog.show links without crashing', function () {
    BlogPost::create([
        'title' => 'Normal Post',
        'slug' => 'normal-post',
        'content' => 'Some content here',
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $response = $this->get('/blog/author/test-author');

    $response->assertStatus(200);
    $response->assertSee('Normal Post');
});

test('author page does not crash when a post has no translations', function () {
    BlogPost::create([
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    BlogPost::create([
        'title' => 'Good Post',
        'slug' => 'good-post',
        'content' => 'This should work',
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $response = $this->get('/blog/author/test-author');

    $response->assertStatus(200);
    $response->assertSee('Good Post');
});
