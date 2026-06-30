<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

use Happytodev\Blogr\BlogrServiceProvider;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['blogr.route.frontend.enabled' => true]);
    config(['blogr.route.prefix' => 'blog']);
});

it('can display a blog post without locales enabled', function () {
    config(['blogr.locales.enabled' => false]);
    config(['blogr.locales.default' => 'en']);
    config(['blogr.route.homepage' => false]);

    // Re-register service provider with new config
    $this->app->register(BlogrServiceProvider::class, true);

    $post = BlogPost::factory()->create([
        'title' => 'Original Title',
        'slug' => 'original-slug',
        'content' => 'Original content here',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $translation = $post->translations()->where('locale', 'en')->first();
    expect($translation)->not->toBeNull();

    $response = $this->get('/blog/original-slug');

    $response->assertStatus(200);
    $response->assertSee('Original Title');
    $response->assertSee('Original content');
});

it('returns 404 for missing translation', function () {
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.default' => 'en']);

    // Re-register service provider to apply locale configuration
    $this->app->register(BlogrServiceProvider::class, true);

    $post = BlogPost::factory()->create([
        'title' => 'English Title',
        'slug' => 'english-slug',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get('/fr/blog/titre-inexistant');

    $response->assertStatus(404);
});
