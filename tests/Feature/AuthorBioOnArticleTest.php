<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->author = User::create([
        'name' => 'Jane Author',
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
        'slug' => 'jane-author',
        'avatar' => 'avatars/jane.jpg',
        'bio' => [
            'en' => 'Jane is an experienced writer with a passion for technology.',
            'fr' => 'Jane est une écrivaine expérimentée passionnée par la technologie.',
        ],
    ]);

    $this->category = Category::create([
        'name' => 'Technology',
        'slug' => 'technology',
    ]);

    $this->post = BlogPost::create([
        'title' => 'Understanding Laravel',
        'slug' => 'understanding-laravel',
        'content' => '# Introduction\n\nThis is a great article about Laravel.',
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);
});

test('article page displays author bio component', function () {
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => $this->post->slug]));
    
    $response->assertStatus(200);
    $response->assertSee('Jane Author');
    $response->assertSee('Jane is an experienced writer');
});

test('article page displays author avatar', function () {
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => $this->post->slug]));
    
    $response->assertStatus(200);
    $response->assertSee('avatars/jane.jpg');
});

test('article page shows author initials when no avatar', function () {
    $authorNoAvatar = User::create([
        'name' => 'Bob Writer',
        'email' => 'bob@example.com',
        'password' => Hash::make('password123'),
        'slug' => 'bob-writer',
        'bio' => 'Bob loves writing.',
    ]);

    $post = BlogPost::create([
        'title' => 'Another Article',
        'slug' => 'another-article',
        'content' => '# Content here',
        'user_id' => $authorNoAvatar->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    
    $response->assertStatus(200);
    $response->assertSee('Bob Writer');
    $response->assertSee('B'); // Initial
});

test('article page links to author profile', function () {
    // Enable locales temporarily for this test
    config(['blogr.locales.enabled' => true]);
    
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => $this->post->slug]));
    
    $response->assertStatus(200);
    $response->assertSee(route('blog.author', ['locale' => 'en', 'userSlug' => $this->author->slug]));
});
