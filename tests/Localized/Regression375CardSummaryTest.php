<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Illuminate\Support\Facades\Storage;

/*
 * Regression tests: #375 — cards printed `tldr` and nothing else. A post written
 * without one (the artwork series: body, no summary) showed as a title and a date
 * with a hole under it, on the author page and everywhere the card is used.
 * The summary now falls back to `excerpt`, then to the beginning of the body.
 */

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create(['name' => 'John Doe', 'slug' => 'johndoe']);
    $this->category = Category::factory()->create(['slug' => 'art', 'name' => 'Art']);

    $this->makePost = function (array $translation): BlogPost {
        $post = BlogPost::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'default_locale' => 'en',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $post->translations()->create([
            'locale' => 'en',
            'title' => 'AF-01 · day 11',
            'slug' => 'af-01-day-11',
            'content' => "**Provenance.** frameworkheroes\n\n## Histoire\n\nUn paragraphe de corps.",
            ...$translation,
        ]);

        return $post->fresh();
    };
});

it('uses the tldr when the post has one', function () {
    $post = ($this->makePost)(['tldr' => 'A short line.']);

    expect($post->summary('en'))->toBe('A short line.');
});

it('falls back to the excerpt', function () {
    $post = ($this->makePost)(['excerpt' => 'An excerpt typed by the editor.']);

    expect($post->summary('en'))->toBe('An excerpt typed by the editor.');
});

it('falls back to the beginning of the body, without markdown', function () {
    $post = ($this->makePost)([]);

    $summary = $post->summary('en');

    expect($summary)->toStartWith('Provenance. frameworkheroes');
    expect($summary)->not->toContain('**');
    expect($summary)->not->toContain('##');
});

it('shows that summary on the author page', function () {
    ($this->makePost)([]);

    $this->get('/en/blog/author/johndoe')
        ->assertStatus(200)
        ->assertSee('Provenance. frameworkheroes');
});

it('shows it on the blog index too', function () {
    ($this->makePost)([]);

    $this->get('/en/blog')
        ->assertStatus(200)
        ->assertSee('Provenance. frameworkheroes');
});
