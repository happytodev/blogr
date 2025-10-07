<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Config;
use function Pest\Laravel\get;

beforeEach(function () {
    Config::set('blogr.locales.enabled', true);
    Config::set('blogr.locales.default', 'en');
    Config::set('blogr.locales.available', ['en', 'fr', 'es']);
    Config::set('blogr.posts.show_language_switcher', true);
});

test('post language indicator is not shown when disabled in config', function () {
    Config::set('blogr.posts.show_language_switcher', false);
    
    $category = Category::factory()->create();
    $post = BlogPost::factory()->create([
        'category_id' => $category->id,
        'is_published' => true,
        'slug' => 'test-post',
        'title' => 'Test Post',
    ]);
    
    // The observer automatically creates an 'en' translation
    
    $response = get(route('blog.show', ['slug' => 'test-post']));
    
    $response->assertStatus(200);
    $response->assertDontSee('Available in:');
});

test('post language indicator is not shown when only one translation exists', function () {
    $category = Category::factory()->create();
    $post = BlogPost::factory()->create([
        'category_id' => $category->id,
        'is_published' => true,
        'slug' => 'test-post-single',
        'title' => 'Test Post',
    ]);
    
    // The observer automatically creates an 'en' translation
    // We only have one translation
    
    $response = get(route('blog.show', ['slug' => 'test-post-single']));
    
    $response->assertStatus(200);
    $response->assertDontSee('Available in:');
});
