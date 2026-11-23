<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;

/*
 * Regression tests: #366 — a page title carried nothing but the entity title, so
 * every page of a blog read as an internal reference. The title now comes from a
 * configurable pattern.
 */

beforeEach(function () {
    Storage::fake('public');

    config([
        'blogr.seo.site_name' => ['en' => 'My Blog', 'fr' => 'Mon Blog'],
        'blogr.seo.title_pattern' => '{title} — {site}',
    ]);

    $this->user = User::factory()->create(['name' => 'John Doe', 'slug' => 'johndoe']);

    $this->category = Category::factory()->create(['slug' => 'art', 'name' => 'Art']);

    $this->makePost = function (array $translation, array $state = []): BlogPost {
        $post = BlogPost::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now()->subDay(),
            'default_locale' => 'fr',
            ...$state,
        ]);

        $post->translations()->create([
            'locale' => 'fr',
            'content' => 'Un corps de texte.',
            ...$translation,
        ]);

        return $post;
    };
});

it('appends the site name to the post title', function () {
    ($this->makePost)(['title' => 'Mon article', 'slug' => 'mon-article']);

    $this->get('/fr/blog/mon-article')
        ->assertStatus(200)
        ->assertSee('<title>Mon article — Mon Blog</title>', false);
});

it('adds the series to the title of a post that belongs to one', function () {
    config(['blogr.seo.title_pattern' => '{title} — {series} — {site}']);

    $series = BlogSeries::create([
        'slug' => 'amber-fossils',
        'position' => 1,
        'is_featured' => false,
        'published_at' => now()->subDay(),
    ]);

    $series->translations()->create([
        'locale' => 'fr',
        'title' => 'Amber Fossils',
        'slug' => 'amber-fossils',
    ]);

    ($this->makePost)(
        ['title' => 'AF-01 · day 11', 'slug' => 'af-01-day-11'],
        ['blog_series_id' => $series->id, 'series_position' => 1]
    );

    $this->get('/fr/blog/series/amber-fossils/af-01-day-11')
        ->assertStatus(200)
        ->assertSee('<title>AF-01 · day 11 — Amber Fossils — Mon Blog</title>', false);
});

it('drops a placeholder that has no value instead of leaving a separator', function () {
    config(['blogr.seo.title_pattern' => '{title} — {series} — {site}']);

    ($this->makePost)(['title' => 'Sans série', 'slug' => 'sans-serie']);

    $this->get('/fr/blog/sans-serie')
        ->assertStatus(200)
        ->assertSee('<title>Sans série — Mon Blog</title>', false)
        ->assertDontSee('Sans série —  — Mon Blog', false);
});

it('keeps the old bare title when the pattern has no placeholder', function () {
    config(['blogr.seo.title_pattern' => '{title}']);

    ($this->makePost)(['title' => 'Sans site', 'slug' => 'sans-site']);

    $this->get('/fr/blog/sans-site')
        ->assertStatus(200)
        ->assertSee('<title>Sans site</title>', false);
});

it('does not override a title an editor wrote', function () {
    ($this->makePost)([
        'title' => 'Mon article',
        'slug' => 'mon-article-2',
        'seo_title' => 'Titre choisi à la main',
    ]);

    $this->get('/fr/blog/mon-article-2')
        ->assertStatus(200)
        ->assertSee('<title>Titre choisi à la main</title>', false);
});

it('builds the series page title the same way', function () {
    $series = BlogSeries::create([
        'slug' => 'amber-fossils',
        'position' => 1,
        'is_featured' => false,
        'published_at' => now()->subDay(),
    ]);

    $series->translations()->create([
        'locale' => 'fr',
        'title' => 'Amber Fossils',
        'slug' => 'amber-fossils',
        'description' => 'Une série.',
    ]);

    $this->get('/fr/blog/series/amber-fossils')
        ->assertStatus(200)
        ->assertSee('<title>Amber Fossils — Mon Blog</title>', false);
});
