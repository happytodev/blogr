<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->author = User::create([
        'name' => 'Test Author',
        'email' => 'author@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->category = Category::create([
        'name' => 'Tech',
        'slug' => 'tech',
    ]);
});

test('homepage paginates articles', function () {
    // Create 15 articles
    // NOTE: Articles are sorted by created_at DESC in the controller (.latest())
    // When created at the same time, they appear in insertion order (1, 2, 3...)
    for ($i = 1; $i <= 15; $i++) {
        BlogPost::create([
            'title' => "Article $i",
            'slug' => "article-$i",
            'content' => 'Content',
            'user_id' => $this->author->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now()->subDays(15 - $i),
        ]);
    }

    $response = $this->get(route('blog.index', ['locale' => 'en']));

    $response->assertStatus(200);
    // Should show 15 articles pagination info
    $response->assertSee('results');

    // First page shows first 10 articles (insertion order when created_at is same)
    $response->assertSee('Article 1');
    $response->assertSee('Article 10');

    // Should NOT see Article 11 (on page 2)
    $response->assertDontSee('Article 11');
});

test('homepage shows pagination links', function () {
    // Create 15 articles - will be shown in insertion order
    for ($i = 1; $i <= 15; $i++) {
        BlogPost::create([
            'title' => "Article $i",
            'slug' => "article-$i",
            'content' => 'Content',
            'user_id' => $this->author->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now()->subDays(15 - $i),
        ]);
    }

    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    // Should have pagination links (check for "Next" link)
    $response->assertSee('Next &raquo;', false); // false = don't escape HTML entities
    
    // Should show pagination info
    $response->assertSee('Showing');
    $response->assertSee('1');
    $response->assertSee('to');
    $response->assertSee('10');
    $response->assertSee('of');
    $response->assertSee('15');
});

test('category page paginates articles', function () {
    // Create 15 articles in same category
    // Sorted by created_at DESC, but when created at same time, order may vary
    for ($i = 1; $i <= 15; $i++) {
        BlogPost::create([
            'title' => "Tech Article $i",
            'slug' => "tech-article-$i",
            'content' => 'Content',
            'user_id' => $this->author->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now()->subDays(15 - $i),
        ]);
    }

    $response = $this->get(route('blog.category', ['locale' => 'en', 'categorySlug' => 'tech']));
    
    $response->assertStatus(200);
    
    // Verify pagination is working - should have pagination links
    $response->assertSee('Showing');
    $response->assertSee('10'); // Page size
    $response->assertSee('15'); // Total results
    
    // Check for pagination navigation
    $response->assertSee('Next', false);
});
