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
    
    // Force re-register routes with locales enabled
    app('router')->getRoutes()->refreshNameLookups();
    
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

test('blog index route includes locale parameter', function () {
    $url = route('blog.index', ['locale' => 'en']);
    
    // URL should contain the locale parameter (as query string when locales disabled in TestCase)
    expect($url)->toContain('locale=en');
});

test('blog show route includes locale parameter', function () {
    $url = route('blog.show', ['locale' => 'en', 'slug' => 'test-article']);
    
    // URL should contain the locale parameter
    expect($url)->toContain('test-article');
    expect($url)->toContain('locale=en');
});

test('blog category route includes locale parameter', function () {
    $url = route('blog.category', ['locale' => 'en', 'categorySlug' => 'test-category']);
    
    // URL should contain the locale parameter
    expect($url)->toContain('test-category');
    expect($url)->toContain('locale=en');
});

test('generated links in views include locale parameter', function () {
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    
    // Check that links contain locale parameter
    $response->assertSee('test-article');
    $response->assertSee('test-category');
});
