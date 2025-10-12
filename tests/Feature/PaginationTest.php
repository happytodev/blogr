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
    // Create 15 articles (more than default per page)
    for ($i = 1; $i <= 15; $i++) {
        BlogPost::create([
            'title' => "Article $i",
            'slug' => "article-$i",
            'content' => 'Content',
            'user_id' => $this->author->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now()->subDays($i),
        ]);
    }

    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    // Should see first 10 articles (default pagination)
    $response->assertSee('Article 1');
    $response->assertSee('Article 10');
    // Should not see article 11 on first page
    $response->assertDontSee('Article 11');
});

test('homepage shows pagination links when needed', function () {
    // Create 15 articles
    for ($i = 1; $i <= 15; $i++) {
        BlogPost::create([
            'title' => "Article $i",
            'slug' => "article-$i",
            'content' => 'Content',
            'user_id' => $this->author->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now()->subDays($i),
        ]);
    }

    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    // Should have pagination links (check for "Next" link)
    $response->assertSee('Next &raquo;', false); // false = don't escape HTML entities
});

test('category page paginates articles', function () {
    // Create 15 articles in same category
    for ($i = 1; $i <= 15; $i++) {
        BlogPost::create([
            'title' => "Tech Article $i",
            'slug' => "tech-article-$i",
            'content' => 'Content',
            'user_id' => $this->author->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now()->subDays($i),
        ]);
    }

    $response = $this->get(route('blog.category', ['locale' => 'en', 'categorySlug' => 'tech']));
    
    $response->assertStatus(200);
    $response->assertSee('Tech Article 1');
    $response->assertDontSee('Tech Article 11');
});
