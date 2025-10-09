<?php

use Happytodev\Blogr\Helpers\SEOHelper;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;

beforeEach(function () {
    $this->app['config']->set('blogr.locales.enabled', false);
    $this->app['config']->set('blogr.route.prefix', 'blog');
    $this->app['config']->set('blogr.route.homepage', false);
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);
});

test('SEOHelper generates correct metadata for listing pages', function () {
    // Test index page
    $seoData = SEOHelper::forListingPage('index');

    expect($seoData)->toHaveKey('title')
        ->and($seoData)->toHaveKey('description')
        ->and($seoData)->toHaveKey('keywords')
        ->and($seoData)->toHaveKey('canonical')
        ->and($seoData)->toHaveKey('og_type')
        ->and($seoData)->toHaveKey('schema_type')
        ->and($seoData)->toHaveKey('robots');

    expect($seoData['og_type'])->toBe('website');
    expect($seoData['schema_type'])->toBe('WebPage');
});

test('SEOHelper generates correct metadata for category pages', function () {
    $categoryName = 'Technology';
    $seoData = SEOHelper::forListingPage('category', $categoryName);

    expect($seoData['title'])->toContain($categoryName);
    expect($seoData['description'])->toContain($categoryName);
    expect($seoData['keywords'])->toContain($categoryName);
});

test('SEOHelper generates correct metadata for tag pages', function () {
    $tagName = 'Laravel';
    $seoData = SEOHelper::forListingPage('tag', $tagName);

    expect($seoData['title'])->toContain($tagName);
    expect($seoData['description'])->toContain($tagName);
    expect($seoData['keywords'])->toContain($tagName);
});

test('SEOHelper generates correct metadata for blog posts', function () {
    // Create test data directly without factories
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'email_verified_at' => now(),
    ]);

    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    $post = BlogPost::create([
        'title' => 'Test Blog Post',
        'content' => 'Test content',
        'slug' => 'test-post',
        'user_id' => $user->id,
        'category_id' => $category->id,
        'meta_title' => 'Custom Meta Title',
        'meta_description' => 'Custom meta description',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $seoData = SEOHelper::forBlogPost($post);

    expect($seoData)->toHaveKey('title')
        ->and($seoData)->toHaveKey('description')
        ->and($seoData)->toHaveKey('og_type')
        ->and($seoData)->toHaveKey('schema_type');

    expect($seoData['title'])->toBe('Custom Meta Title');
    expect($seoData['og_type'])->toBe('article');
    expect($seoData['schema_type'])->toBe('BlogPosting');
});

test('SEOHelper falls back to post data when meta fields are empty', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test2@example.com',
        'password' => 'password',
        'email_verified_at' => now(),
    ]);

    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category-2',
    ]);

    $post = BlogPost::create([
        'title' => 'Amazing Blog Post Title',
        'content' => 'This is an amazing blog post with lots of interesting content that should be used to generate a description when meta_description is not provided.',
        'slug' => 'amazing-post',
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $seoData = SEOHelper::forBlogPost($post);

    expect($seoData['title'])->toBe('Amazing Blog Post Title');
    expect($seoData['description'])->toContain('amazing blog post');
});

test('SEOHelper generates valid JSON-LD for web pages', function () {
    $seoData = [
        'title' => 'Test Page',
        'description' => 'Test description',
        'canonical' => 'https://example.com/test',
        'schema_type' => 'WebPage',
    ];

    $jsonLd = SEOHelper::generateJsonLd($seoData);

    expect($jsonLd)->toBeString();

    $decoded = json_decode($jsonLd, true);
    expect($decoded)->toBeArray();
    expect($decoded)->toHaveKey('@context');
    expect($decoded)->toHaveKey('@type');
    expect($decoded)->toHaveKey('name');
    expect($decoded)->toHaveKey('description');
    expect($decoded)->toHaveKey('url');
    expect($decoded)->toHaveKey('publisher');

    expect($decoded['@context'])->toBe('https://schema.org');
    expect($decoded['@type'])->toBe('WebPage');
    expect($decoded['name'])->toBe('Test Page');
});

test('SEOHelper generates valid JSON-LD for blog posts', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test3@example.com',
        'password' => 'password',
        'email_verified_at' => now(),
    ]);
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category-3',
    ]);

    $post = BlogPost::create([
        'title' => 'Test Blog Post',
        'content' => 'Test content',
        'slug' => 'test-post-3',
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $seoData = SEOHelper::forBlogPost($post);
    $jsonLd = SEOHelper::generateJsonLd($seoData);

    expect($jsonLd)->toBeString();

    $decoded = json_decode($jsonLd, true);
    expect($decoded)->toBeArray();
    expect($decoded['@type'])->toBe('BlogPosting');
    expect($decoded)->toHaveKey('headline');
    expect($decoded)->toHaveKey('author');
    expect($decoded)->toHaveKey('datePublished');
    expect($decoded)->toHaveKey('dateModified');
});

test('SEOHelper handles special characters in JSON-LD', function () {
    $seoData = [
        'title' => 'Test with "quotes" and \'apostrophes\'',
        'description' => 'Description with <script>alert("xss")</script> and special chars éñü',
        'canonical' => 'https://example.com/test',
        'schema_type' => 'WebPage',
    ];

    $jsonLd = SEOHelper::generateJsonLd($seoData);

    // Should not throw an exception
    $decoded = json_decode($jsonLd, true);
    expect($decoded)->toBeArray();
    expect($decoded['name'])->toBe('Test with "quotes" and \'apostrophes\'');
    expect($decoded['description'])->toContain('special chars');
});

test('SEOHelper generates correct canonical URLs', function () {
    $seoData = SEOHelper::forListingPage('index');

    expect($seoData['canonical'])->toBeString();
    expect($seoData['canonical'])->toStartWith('http');
});

test('SEOHelper includes image data when post has photo', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test4@example.com',
        'password' => 'password',
        'email_verified_at' => now(),
    ]);
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category-4',
    ]);

    $post = BlogPost::create([
        'title' => 'Test Blog Post',
        'content' => 'Test content',
        'slug' => 'test-post-4',
        'user_id' => $user->id,
        'category_id' => $category->id,
        'photo' => 'test-image.jpg',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $seoData = SEOHelper::forBlogPost($post);

    expect($seoData)->toHaveKey('image');
    expect($seoData)->toHaveKey('image_width');
    expect($seoData)->toHaveKey('image_height');
});

test('SEOHelper includes tag data in metadata', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test5@example.com',
        'password' => 'password',
        'email_verified_at' => now(),
    ]);
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category-5',
    ]);

    $post = BlogPost::create([
        'title' => 'Test Blog Post',
        'content' => 'Test content',
        'slug' => 'test-post-5',
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    // Create some tags
    $tag1 = Tag::create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);
    $tag2 = Tag::create([
        'name' => 'PHP',
        'slug' => 'php',
    ]);

    $post->tags()->attach([$tag1->id, $tag2->id]);

    $seoData = SEOHelper::forBlogPost($post);

    expect($seoData)->toHaveKey('tags');
    expect($seoData['tags'])->toBeArray();
    expect($seoData['tags'])->toContain('Laravel');
    expect($seoData['tags'])->toContain('PHP');
});
