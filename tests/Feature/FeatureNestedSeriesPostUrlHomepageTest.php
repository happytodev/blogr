<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Tests\LocalizedHomepageTestCase;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;

/*
 * Feature test: feature_nested_series_post_urls (issue #359), blog served as homepage.
 *
 * The same nesting must hold when the blog sits at the root of the locale
 * (/{locale}/series/{seriesSlug}/{postSlug}) instead of under /blog.
 */

uses(LocalizedHomepageTestCase::class);

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

    $this->nestedEn = '/en/series/phage-the-birth/what-is-phage';
    $this->flatEn = '/en/what-is-phage';
});

it('serves a series post under its series URL when the blog is the homepage', function () {
    $response = $this->get($this->nestedEn);

    $response->assertStatus(200);
    $response->assertSee('What Is Phage', false);
});

it('redirects the flat URL to the nested URL when the blog is the homepage', function () {
    $response = $this->get($this->flatEn);

    $response->assertStatus(301);
    $response->assertRedirect($this->nestedEn);
});

it('declares the nested URL as canonical when the blog is the homepage', function () {
    $response = $this->get($this->nestedEn);

    $response->assertStatus(200);
    $response->assertSee('<link rel="canonical" href="'.url($this->nestedEn).'">', false);
});
