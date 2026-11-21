<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;

/*
 * Regression test: #360 — the language switcher kept the current slug, so the
 * link to the other locale answered 404 on every page whose slug is translated.
 *
 * blogrSwitcherLinks() reads only the switcher's own links (its class is
 * unique), so a translated link rendered elsewhere on the page — a tag chip on
 * a card, for instance — cannot make these tests pass by accident.
 */

function blogrSwitcherLinks(string $html): array
{
    preg_match_all('/<a href="([^"]+)"\s+class="block px-4 py-2 text-sm/', $html, $matches);

    return $matches[1];
}

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create(['name' => 'John Doe', 'slug' => 'johndoe']);

    // Series with a translated slug per locale
    $this->series = BlogSeries::create([
        'slug' => 'phage-series',
        'position' => 1,
        'is_featured' => false,
        'published_at' => now()->subDay(),
    ]);

    $this->series->translations()->create([
        'locale' => 'en',
        'title' => 'Phage: the birth',
        'description' => 'The birth of Phage',
        'slug' => 'phage-the-birth',
    ]);

    $this->series->translations()->create([
        'locale' => 'fr',
        'title' => 'Phage : la naissance',
        'description' => 'La naissance de Phage',
        'slug' => 'phage-la-naissance',
    ]);

    // A post of that series, with a translated slug per locale
    $this->category = Category::create(['slug' => 'open-source', 'name' => 'Open Source', 'is_default' => true]);
    $this->category->translations()->create(['locale' => 'fr', 'name' => 'Open Source', 'slug' => 'open-source-fr']);

    $this->post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'blog_series_id' => $this->series->id,
        'series_position' => 1,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'default_locale' => 'en',
    ]);

    $this->post->translations()->create([
        'locale' => 'en',
        'title' => 'What is Phage',
        'slug' => 'what-is-phage',
        'content' => '## The what',
        'reading_time' => 3,
    ]);

    $this->post->translations()->create([
        'locale' => 'fr',
        'title' => 'Qu est-ce que Phage',
        'slug' => 'quest-ce-que-phage',
        'content' => '## Le quoi',
        'reading_time' => 3,
    ]);

    // Tag with a translated slug per locale
    $this->tag = Tag::create(['slug' => 'php', 'name' => 'PHP']);
    $this->tag->translations()->create(['locale' => 'fr', 'name' => 'PHP', 'slug' => 'php-fr']);
});

it('links the series switcher to the translated slug of the other locale', function () {
    $response = $this->get('/en/blog/series/phage-the-birth');

    $response->assertStatus(200);
    $links = blogrSwitcherLinks($response->getContent());
    expect($links)->toContain(url('/fr/blog/series/phage-la-naissance'));
    expect($links)->not->toContain(url('/fr/blog/series/phage-the-birth'));

    $response = $this->get('/fr/blog/series/phage-la-naissance');

    $response->assertStatus(200);
    $links = blogrSwitcherLinks($response->getContent());
    expect($links)->toContain(url('/en/blog/series/phage-the-birth'));
});

it('links the series post switcher to the translated slugs of the other locale', function () {
    $response = $this->get('/en/blog/series/phage-the-birth/what-is-phage');

    $response->assertStatus(200);
    $links = blogrSwitcherLinks($response->getContent());
    expect($links)->toContain(url('/fr/blog/series/phage-la-naissance/quest-ce-que-phage'));
    expect($links)->not->toContain(url('/fr/blog/series/phage-the-birth/what-is-phage'));

    $response = $this->get('/fr/blog/series/phage-la-naissance/quest-ce-que-phage');

    $response->assertStatus(200);
    $links = blogrSwitcherLinks($response->getContent());
    expect($links)->toContain(url('/en/blog/series/phage-the-birth/what-is-phage'));
});

it('links the category switcher to the translated slug of the other locale', function () {
    $response = $this->get('/en/blog/category/open-source');

    $response->assertStatus(200);
    $links = blogrSwitcherLinks($response->getContent());
    expect($links)->toContain(url('/fr/blog/category/open-source-fr'));
    expect($links)->not->toContain(url('/fr/blog/category/open-source'));
});

it('links the tag switcher to the translated slug of the other locale', function () {
    $response = $this->get('/en/blog/tag/php');

    $response->assertStatus(200);
    $links = blogrSwitcherLinks($response->getContent());
    expect($links)->toContain(url('/fr/blog/tag/php-fr'));
    expect($links)->not->toContain(url('/fr/blog/tag/php'));
});
