<?php

use Happytodev\Blogr\Helpers\PostUrlHelper;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;

/*
 * Regression test: #363 — the sitemap listed a series post in a locale where it
 * has no translation, because it fell back on the default translation to decide
 * whether to list the post but built the URL for the requested locale: the
 * nested route then answered 404.
 */

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create(['name' => 'John Doe', 'slug' => 'johndoe']);

    $this->series = BlogSeries::create([
        'slug' => 'linkeeper',
        'position' => 1,
        'is_featured' => false,
        'published_at' => now()->subDay(),
    ]);

    $this->series->translations()->create([
        'locale' => 'fr',
        'title' => 'Linkeeper',
        'description' => 'Une alternative a Linktree',
        'slug' => 'linkeeper',
    ]);

    $this->category = Category::create(['slug' => 'tutoriels', 'name' => 'Tutoriels', 'is_default' => true]);

    // A series post translated in French only.
    $this->post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'blog_series_id' => $this->series->id,
        'series_position' => 1,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'default_locale' => 'fr',
    ]);

    $this->post->translations()->create([
        'locale' => 'fr',
        'title' => 'Linkeeper : installation',
        'slug' => 'linkeeper-installation',
        'content' => '## Installation',
        'reading_time' => 2,
    ]);
});

it('never builds a nested URL for a locale the post is not translated in', function () {
    expect(PostUrlHelper::nestedForPost($this->post->fresh(), 'en'))->toBeNull();
    expect(PostUrlHelper::nestedForPost($this->post->fresh(), 'fr'))->not->toBeNull();
});

it('keeps the flat URL servable when the post has no translation for the locale', function () {
    // The flat URL of another locale has always been served with a fallback,
    // so the helper must not point anywhere else.
    expect(PostUrlHelper::forPost($this->post->fresh(), 'en'))
        ->toBe(url('/en/blog/linkeeper-installation'));
});

it('does not list a post in the sitemap of a locale it is not translated in', function () {
    $response = $this->get('/en/blog/sitemap.xml');

    $response->assertStatus(200);
    expect($response->getContent())->not->toContain('linkeeper-installation');
});

it('lists that post in the sitemap of its own locale, nested under its series', function () {
    $response = $this->get('/fr/blog/sitemap.xml');

    $response->assertStatus(200);
    expect($response->getContent())->toContain('/fr/blog/series/linkeeper/linkeeper-installation');
});

it('serves the nested URL the sitemap lists', function () {
    $response = $this->get('/fr/blog/series/linkeeper/linkeeper-installation');

    $response->assertStatus(200);
});
