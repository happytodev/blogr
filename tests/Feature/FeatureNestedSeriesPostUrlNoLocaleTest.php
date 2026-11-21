<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

/*
 * Feature test: feature_nested_series_post_urls (issue #359), locales disabled.
 *
 * Without locales the nesting must hold too: /blog/series/{seriesSlug}/{postSlug}.
 */

uses(TestCase::class);

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create([
        'name' => 'John Doe',
        'slug' => 'johndoe',
    ]);

    $this->category = Category::factory()->create();

    $this->series = BlogSeries::create([
        'slug' => 'phage-series',
        'position' => 1,
        'is_featured' => false,
        'published_at' => now()->subDay(),
    ]);

    $this->series->translations()->create([
        'locale' => 'en',
        'title' => 'Phage Series',
        'description' => 'The birth of Phage',
        'slug' => 'phage-the-birth',
    ]);

    $this->post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'blog_series_id' => $this->series->id,
        'series_position' => 1,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'default_locale' => 'en',
        'photo' => null,
    ]);

    $this->post->translations()->create([
        'locale' => 'en',
        'title' => 'What Is Phage',
        'slug' => 'what-is-phage',
        'content' => '## The what',
        'reading_time' => 3,
    ]);

    $this->nested = '/blog/series/phage-the-birth/what-is-phage';
    $this->flat = '/blog/what-is-phage';
});

it('serves a series post under its series URL without locales', function () {
    $response = $this->get($this->nested);

    $response->assertStatus(200);
    $response->assertSee('What Is Phage', false);
});

it('redirects the flat URL to the nested URL without locales', function () {
    $response = $this->get($this->flat);

    $response->assertStatus(301);
    $response->assertRedirect($this->nested);
});

it('keeps the flat URL of a post without series without locales', function () {
    $standalone = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'blog_series_id' => null,
        'series_position' => null,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'default_locale' => 'en',
        'photo' => null,
    ]);

    $standalone->translations()->create([
        'locale' => 'en',
        'title' => 'Standalone Post',
        'slug' => 'standalone-post',
        'content' => 'Body',
        'reading_time' => 1,
    ]);

    $response = $this->get('/blog/standalone-post');

    $response->assertStatus(200);
    $response->assertSee('Standalone Post', false);
});
