<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;

/*
 * Regression test: #362 — a category or tag page answered 500 as soon as it
 * listed a post that has no translation in the current locale: the controller
 * eager-loaded the translations filtered on that locale, the card's fallback
 * read the same filtered relation, found nothing, and built a route() without a
 * slug.
 */

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create(['name' => 'John Doe', 'slug' => 'johndoe']);

    $this->category = Category::create(['slug' => 'tutoriels', 'name' => 'Tutoriels', 'is_default' => true]);
    $this->tag = Tag::create(['slug' => 'laravel', 'name' => 'Laravel']);

    // A published post translated in French only, in that category and tag.
    $this->post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'blog_series_id' => null,
        'series_position' => null,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'default_locale' => 'fr',
    ]);

    $this->post->translations()->create([
        'locale' => 'fr',
        'title' => 'Un article en francais',
        'slug' => 'un-article-en-francais',
        'content' => '## Le sujet',
        'reading_time' => 2,
    ]);

    $this->post->tags()->attach($this->tag->id);
});

it('renders a category page that lists a post translated in one locale only', function () {
    $response = $this->get('/en/blog/category/tutoriels');

    $response->assertStatus(200);
});

it('renders a tag page that lists a post translated in one locale only', function () {
    $response = $this->get('/en/blog/tag/laravel');

    $response->assertStatus(200);
});

it('links such a post with a real slug instead of an empty one', function () {
    $response = $this->get('/en/blog/category/tutoriels');

    $response->assertStatus(200);
    // The post is served in this locale with the translation-warning banner
    // (pre-existing behaviour), but the link must carry its slug.
    $response->assertSee('/en/blog/un-article-en-francais', false);
});
