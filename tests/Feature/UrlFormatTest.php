<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Enable locales in config
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.default' => 'en']);
    config(['blogr.locales.available' => ['en', 'fr']]);
    
    // Set up the app locale
    app()->setLocale('en');
    
    // Create test category
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);
    
    // Create test blog post
    $post = BlogPost::create([
        'user_id' => 1,
        'category_id' => $category->id,
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => 'This is test content',
        'is_published' => true,
        'published_at' => now(),
    ]);
    
    $this->category = $category;
    $this->post = $post;
});

test('blog index route uses locale in path not query string', function () {
    $url = route('blog.index', ['locale' => 'en']);
    
    // URL should contain /en/ in the path
    expect($url)->toContain('/en/blog');
    
    // URL should NOT contain ?locale=en
    expect($url)->not()->toContain('?locale=en');
});

test('blog show route uses locale in path not query string', function () {
    $url = route('blog.show', ['locale' => 'en', 'slug' => 'test-article']);
    
    // URL should contain /en/ in the path
    expect($url)->toContain('/en/blog/');
    
    // URL should NOT contain ?locale=en
    expect($url)->not()->toContain('?locale=en');
});

test('blog category route uses locale in path not query string', function () {
    $url = route('blog.category', ['locale' => 'en', 'categorySlug' => 'test-category']);
    
    // URL should contain /en/ in the path
    expect($url)->toContain('/en/blog/category/');
    
    // URL should NOT contain ?locale=en
    expect($url)->not()->toContain('?locale=en');
});

test('generated links in views use locale in path', function () {
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    
    // Check that article links use /en/blog/ format
    $response->assertSee('href="http://localhost/en/blog/test-article"', false);
    
    // Check that category links use /en/blog/category/ format
    $response->assertSee('href="http://localhost/en/blog/category/test-category"', false);
});
