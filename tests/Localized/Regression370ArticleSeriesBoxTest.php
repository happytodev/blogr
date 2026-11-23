<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;

/*
 * Regression tests: #370 (every post of a series printed the whole series
 * description) and #371 (no way back to the series from an article).
 */

beforeEach(function () {
    Storage::fake('public');

    config(['blogr.seo.site_name' => ['en' => 'My Blog', 'fr' => 'Mon Blog']]);

    $this->user = User::factory()->create(['name' => 'John Doe', 'slug' => 'johndoe']);

    $this->category = Category::factory()->create();

    $long = 'Une série de dessins génératifs faits à partir de mes propres dépôts de code. '
        .str_repeat('Chaque pièce est un dépôt arrêté à un jour précis de sa vie. ', 12)
        .'FIN-DE-LA-DESCRIPTION';

    $this->series = BlogSeries::create([
        'slug' => 'amber-fossils',
        'position' => 1,
        'is_featured' => false,
        'published_at' => now()->subDay(),
    ]);

    $this->series->translations()->create([
        'locale' => 'fr',
        'title' => 'Amber Fossils',
        'slug' => 'amber-fossils',
        'description' => $long,
    ]);

    $post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'blog_series_id' => $this->series->id,
        'series_position' => 1,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'default_locale' => 'fr',
    ]);

    $post->translations()->create([
        'locale' => 'fr',
        'title' => 'AF-01 · day 11',
        'slug' => 'af-01-day-11',
        'content' => 'Un corps.',
    ]);

    $this->article = '/fr/blog/series/amber-fossils/af-01-day-11';
    $this->seriesUrl = '/fr/blog/series/amber-fossils';
});

it('shows a teaser of the series description, not the whole thing', function () {
    $html = $this->get($this->article)->assertStatus(200)->getContent();
    $body = explode('</head>', $html)[1] ?? $html;

    expect($body)->not->toContain('FIN-DE-LA-DESCRIPTION');
    expect($body)->toContain('Une série de dessins génératifs');
});

it('links the series title of the box to the series page', function () {
    $html = $this->get($this->article)->assertStatus(200)->getContent();

    expect($html)->toContain('href="'.url($this->seriesUrl).'"');
});

it('gives the article a breadcrumb that leads back to its series', function () {
    $html = $this->get($this->article)->assertStatus(200)->getContent();

    // The breadcrumb component renders the series as a link before the title.
    expect($html)->toContain('href="'.url($this->seriesUrl).'"');
    expect($html)->toContain('<nav');
});

it('declares the breadcrumb as structured data', function () {
    $html = $this->get($this->article)->assertStatus(200)->getContent();

    expect($html)->toContain('"@type":"BreadcrumbList"');
    expect($html)->toContain(url($this->seriesUrl));
});
