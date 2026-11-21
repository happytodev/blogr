<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;

/*
 * Feature test: feature_nested_series_post_urls (issue #359)
 *
 * A post that belongs to a published series must be served under that series
 * (/{locale}/blog/series/{seriesSlug}/{postSlug}), and the former flat URL must
 * answer with a 301 to the nested one.
 */

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

    $this->series->translations()->create([
        'locale' => 'fr',
        'title' => 'Série Phage',
        'description' => 'La naissance de Phage',
        'slug' => 'phage-la-naissance',
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

    $this->post->translations()->create([
        'locale' => 'fr',
        'title' => 'Quest Ce Que Phage',
        'slug' => 'quest-ce-que-phage',
        'content' => '## Le quoi',
        'reading_time' => 3,
    ]);

    $this->nestedEn = '/en/blog/series/phage-the-birth/what-is-phage';
    $this->nestedFr = '/fr/blog/series/phage-la-naissance/quest-ce-que-phage';
    $this->flatEn = '/en/blog/what-is-phage';
    $this->flatFr = '/fr/blog/quest-ce-que-phage';
});

it('serves a series post under its series URL', function () {
    $response = $this->get($this->nestedEn);

    $response->assertStatus(200);
    $response->assertSee('What Is Phage', false);
});

it('serves the localized nested URL with the translated slugs', function () {
    $response = $this->get($this->nestedFr);

    $response->assertStatus(200);
    $response->assertSee('Quest Ce Que Phage', false);
});

it('redirects the former flat URL of a series post to the nested URL', function () {
    $response = $this->get($this->flatEn);

    $response->assertStatus(301);
    $response->assertRedirect($this->nestedEn);

    $response = $this->get($this->flatFr);

    $response->assertStatus(301);
    $response->assertRedirect($this->nestedFr);
});

it('declares the nested URL as canonical and in the hreflang alternates', function () {
    $response = $this->get($this->nestedEn);

    $response->assertStatus(200);
    $response->assertSee('<link rel="canonical" href="'.url($this->nestedEn).'">', false);
    $response->assertSee('hreflang="fr" href="'.url($this->nestedFr).'"', false);
    $response->assertDontSee('<link rel="canonical" href="'.url($this->flatEn).'">', false);
});

it('keeps the flat URL of a post that is not part of a series', function () {
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

    $response = $this->get('/en/blog/standalone-post');

    $response->assertStatus(200);
    $response->assertSee('Standalone Post', false);
});

it('keeps the flat URL serving when the series is not published', function () {
    $this->series->update(['published_at' => null]);

    $response = $this->get($this->flatEn);

    $response->assertStatus(200);
    $response->assertSee('What Is Phage', false);
});

it('returns 404 when the post does not belong to the given series', function () {
    $otherSeries = BlogSeries::create([
        'slug' => 'other-series',
        'position' => 2,
        'is_featured' => false,
        'published_at' => now()->subDay(),
    ]);

    $otherSeries->translations()->create([
        'locale' => 'en',
        'title' => 'Other Series',
        'description' => 'Another one',
        'slug' => 'other-series',
    ]);

    $response = $this->get('/en/blog/series/other-series/what-is-phage');

    $response->assertStatus(404);
});

it('links to the nested URL from the blog index', function () {
    $response = $this->get('/en/blog');

    $response->assertStatus(200);
    $response->assertSee($this->nestedEn, false);
});

it('lists the nested URL in the sitemap', function () {
    $response = $this->get('/en/blog/sitemap.xml');

    $response->assertStatus(200);
    $response->assertSee($this->nestedEn, false);
});
