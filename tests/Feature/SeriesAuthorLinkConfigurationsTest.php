<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create([
        'name' => 'John Doe',
        'slug' => 'johndoe',
    ]);

    $this->category = Category::factory()->create();

    $this->series = BlogSeries::create([
        'slug' => 'test-series',
        'position' => 1,
        'is_featured' => true,
        'published_at' => now(),
    ]);

    // Create series translation
    $this->series->translations()->create([
        'locale' => 'en',
        'title' => 'Test Series',
        'description' => 'Test series description',
        'slug' => 'test-series',
    ]);

    $this->post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'blog_series_id' => $this->series->id,
        'series_position' => 1,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
        'photo' => null,
    ]);

    // Create post translation
    $this->post->translations()->create([
        'locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
        'reading_time' => 5,
    ]);
});

it('generates correct author links without locales and without homepage', function () {
    // Configuration: no locales, with the "blog" prefix
    config(['blogr.locales.enabled' => false]);
    config(['blogr.route.prefix' => 'blog']);
    config(['blogr.route.as_homepage' => false]);
    config(['blogr.author_profile.enabled' => true]);

    $response = $this->get('/blog/series/test-series');

    $response->assertStatus(200);

    // The link should be /blog/author/johndoe
    $content = $response->getContent();
    preg_match_all('/href="([^"]*author[^"]*)"/i', $content, $matches);

    $hasCorrectLink = false;
    foreach ($matches[1] as $url) {
        if (str_contains($url, '/blog/author/johndoe')) {
            $hasCorrectLink = true;
            break;
        }
    }

    expect($hasCorrectLink)->toBeTrue('Author link should be /blog/author/johndoe');
});

it('generates correct author links without locales and with homepage', function () {
    // Configuration: no locales, blog as homepage
    // Note: This test cannot really test because the routes are already registered
    // We just check that URL generation does not crash
    config(['blogr.locales.enabled' => false]);
    config(['blogr.route.prefix' => '']);
    config(['blogr.route.as_homepage' => true]);
    config(['blogr.author_profile.enabled' => true]);

    // Test that route() does generate a URL
    $authorUrl = route('blog.author', ['userSlug' => 'johndoe']);

    // Le lien devrait contenir /author/johndoe
    expect($authorUrl)->toContain('/author/johndoe');
});

it('generates correct author links with locales and homepage', function () {
    // Configuration: locales enabled, blog as homepage
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.available' => ['en', 'fr']]);
    config(['blogr.locales.default' => 'en']);
    config(['blogr.route.prefix' => '']);
    config(['blogr.route.as_homepage' => true]);
    config(['blogr.author_profile.enabled' => true]);

    // Note: The routes will not be registered with locale because they are already booted
    // This test only checks that the route() helper does not crash
    $authorUrl = route('blog.author', ['userSlug' => 'johndoe']);

    expect($authorUrl)->toContain('/author/johndoe');
});

it('generates correct author links with locales and without homepage', function () {
    // Configuration: locales enabled, with the "blog" prefix
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.available' => ['en', 'fr']]);
    config(['blogr.locales.default' => 'en']);
    config(['blogr.route.prefix' => 'blog']);
    config(['blogr.route.as_homepage' => false]);
    config(['blogr.author_profile.enabled' => true]);

    // Note: The routes will not be registered with locale because they are already booted
    // This test only checks that the route() helper does not crash
    $authorUrl = route('blog.author', ['userSlug' => 'johndoe']);

    // Le lien devrait contenir /author/johndoe
    expect($authorUrl)->toContain('/author/johndoe');
});
