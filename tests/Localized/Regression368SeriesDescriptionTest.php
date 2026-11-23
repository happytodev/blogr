<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;

/*
 * Regression tests: #368 (the series description was rendered twice on the series
 * page) and #369 (it lost its paragraphs, typed in a textarea and printed inside
 * a single <p>).
 */

beforeEach(function () {
    Storage::fake('public');

    config(['blogr.seo.site_name' => ['en' => 'My Blog', 'fr' => 'Mon Blog']]);

    $this->user = User::factory()->create(['name' => 'John Doe', 'slug' => 'johndoe']);

    $this->category = Category::factory()->create();

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
        'description' => "**Une série** de dessins génératifs.\n\nChaque pièce est un dépôt arrêté à un jour précis.\n\n- jour 11\n- jour 33",
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

    $this->body = function (string $url): string {
        $html = $this->get($url)->assertStatus(200)->getContent();

        return explode('</head>', $html)[1] ?? $html;
    };
});

it('renders the series description once on the series page', function () {
    $body = ($this->body)('/fr/blog/series/amber-fossils');

    expect(substr_count($body, 'dessins génératifs'))->toBe(1);
});

it('renders the series description as markdown, paragraphs and lists included', function () {
    $body = ($this->body)('/fr/blog/series/amber-fossils');

    expect($body)->toContain('<strong>Une série</strong>');
    // Two paragraphs plus the list: three blank-line separated blocks.
    expect(substr_count($body, '<p'))->toBeGreaterThanOrEqual(2);
    expect($body)->toContain('<li>jour 11</li>');
    expect($body)->not->toContain('**Une série**');
});

it('keeps the header of the series list when the component is used alone', function () {
    $html = Blade::render(
        '<x-blogr::series-list :series="$series" />',
        ['series' => $this->series]
    );

    expect($html)->toContain('Amber Fossils');
    expect(substr_count($html, 'dessins génératifs'))->toBe(1);
});
