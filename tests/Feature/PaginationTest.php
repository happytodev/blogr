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
    // Create 15 articles with explicit created_at timestamps to guarantee order
    // NOTE: Articles are sorted by created_at DESC in the controller (.latest())
    // We need distinct timestamps to ensure deterministic ordering
    for ($i = 1; $i <= 15; $i++) {
        $post = BlogPost::create([
            'user_id' => $this->author->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now()->subDays(15 - $i),
            'created_at' => now()->subSeconds(15 - $i), // Ensure distinct timestamps
            'updated_at' => now()->subSeconds(15 - $i),
            'default_locale' => 'en',
        ]);
        
        // Create translation for the post
        // Use unique title pattern to avoid confusion with widgets/sidebars
        $post->translations()->create([
            'locale' => 'en',
            'title' => "Pagination Test Article Number $i",
            'slug' => "pagination-test-article-$i",
            'content' => 'Content for pagination testing',
        ]);
    }

    // Get first page
    $response = $this->get(route('blog.index'));

    $response->assertStatus(200);
    
    // Should show pagination info indicating 15 total articles
    // But only 10 on first page
    $response->assertSee('Showing');
    $response->assertSee('10'); // Page size or "to 10"
    $response->assertSee('15'); // Total results
    
    // Should have "Next" link since there are more pages
    $response->assertSee('Next', false);
});

test('homepage shows pagination links', function () {
    // Create 15 articles with explicit timestamps for deterministic ordering
    for ($i = 1; $i <= 15; $i++) {
        $post = BlogPost::create([
            'user_id' => $this->author->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now()->subDays(15 - $i),
            'created_at' => now()->subSeconds(15 - $i),
            'updated_at' => now()->subSeconds(15 - $i),
            'default_locale' => 'en',
        ]);
        
        $post->translations()->create([
            'locale' => 'en',
            'title' => "Pagination Links Test Article $i",
            'slug' => "pagination-links-test-$i",
            'content' => 'Content',
        ]);
    }

    $response = $this->get(route('blog.index'));
    
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
    // Create 15 articles in same category with explicit timestamps
    // Sorted by created_at DESC, need distinct timestamps for deterministic order
    for ($i = 1; $i <= 15; $i++) {
        $post = BlogPost::create([
            'user_id' => $this->author->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now()->subDays(15 - $i),
            'created_at' => now()->subSeconds(15 - $i),
            'updated_at' => now()->subSeconds(15 - $i),
            'default_locale' => 'en',
        ]);
        
        $post->translations()->create([
            'locale' => 'en',
            'title' => "Category Pagination Tech Article $i",
            'slug' => "category-pagination-tech-$i",
            'content' => 'Content',
        ]);
    }

    $response = $this->get(route('blog.category', ['categorySlug' => 'tech']));
    
    $response->assertStatus(200);
    
    // Verify pagination is working - should have pagination links
    $response->assertSee('Showing');
    $response->assertSee('10'); // Page size
    $response->assertSee('15'); // Total results
    
    // Check for pagination navigation
    $response->assertSee('Next', false);
});
