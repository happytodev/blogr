<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Workbench\App\Models\User;

beforeEach(function () {
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.available' => ['en', 'fr', 'de']]);
    config(['blogr.locales.default' => 'en']);
});

test('blog post can be created without translatable fields in main table', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    expect($post)->not->toBeNull()
        ->and($post->id)->toBeInt()
        ->and($post->user_id)->toBe($user->id);
});

test('blog post can have multiple translations', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post-unique-1',
        'content' => '# Test Content',
        'tldr' => 'Test summary',
        'reading_time' => 5,
    ]);

    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Article de Test',
        'slug' => 'article-de-test-unique-1',
        'content' => '# Contenu de Test',
        'tldr' => 'Résumé test',
        'reading_time' => 5,
    ]);

    $post->refresh();

    expect($post->translations)->toHaveCount(2);
});
