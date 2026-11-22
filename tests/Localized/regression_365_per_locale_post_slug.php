<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;

/*
 * Regression test: #365 — a post slug was unique across every locale, although
 * the route carries the locale. Two translations of the same article could not
 * share a slug, and the second one was refused by the admin.
 */

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create(['name' => 'John Doe', 'slug' => 'johndoe']);

    $this->category = Category::factory()->create();

    $this->makePost = function (array $translations, array $state = []): BlogPost {
        $post = BlogPost::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now()->subDay(),
            'default_locale' => 'en',
            ...$state,
        ]);

        foreach ($translations as $translation) {
            $post->translations()->create($translation);
        }

        return $post;
    };
});

it('accepts the same slug in two locales', function () {
    $post = ($this->makePost)([
        ['locale' => 'en', 'title' => 'Hello', 'slug' => 'hello', 'content' => 'Hello there.'],
        ['locale' => 'fr', 'title' => 'Bonjour', 'slug' => 'hello', 'content' => 'Bonjour à tous.'],
    ]);

    expect($post->translations()->count())->toBe(2);
    expect($post->translations()->where('locale', 'en')->value('slug'))->toBe('hello');
    expect($post->translations()->where('locale', 'fr')->value('slug'))->toBe('hello');
});

it('serves each locale on its own URL with its own content', function () {
    ($this->makePost)([
        ['locale' => 'en', 'title' => 'Hello', 'slug' => 'hello', 'content' => 'Hello there.'],
        ['locale' => 'fr', 'title' => 'Bonjour', 'slug' => 'hello', 'content' => 'Bonjour à tous.'],
    ]);

    $this->get('/en/blog/hello')->assertStatus(200)->assertSee('Hello', false);
    $this->get('/fr/blog/hello')->assertStatus(200)->assertSee('Bonjour', false);
});

it('still serves an old flat URL whose post is not translated in that locale', function () {
    ($this->makePost)([
        ['locale' => 'fr', 'title' => 'Bonjour', 'slug' => 'bonjour', 'content' => 'Bonjour à tous.'],
    ], ['default_locale' => 'fr']);

    // No English translation: the slug alone must still resolve, as before.
    $this->get('/en/blog/bonjour')->assertStatus(200);
});

it('never serves a foreign post that owns the slug in another locale', function () {
    // Two different posts, each with its own locale: both may use "shared".
    ($this->makePost)([
        ['locale' => 'en', 'title' => 'English shared', 'slug' => 'shared', 'content' => 'English.'],
    ]);

    ($this->makePost)([
        ['locale' => 'fr', 'title' => 'French shared', 'slug' => 'shared', 'content' => 'Français.'],
    ], ['default_locale' => 'fr']);

    $this->get('/en/blog/shared')->assertStatus(200)->assertSee('English shared', false);
    $this->get('/fr/blog/shared')->assertStatus(200)->assertSee('French shared', false);
});

it('does not serve a translation under a locale the post is translated in', function () {
    // The post has two slugs, one per locale. Asking its French slug under /en/
    // must not answer with the French translation: the English URL exists and
    // owns the English slug, so /en/<french-slug> is not a URL of this post.
    ($this->makePost)([
        ['locale' => 'en', 'title' => 'English article', 'slug' => 'english-article', 'content' => 'English.'],
        ['locale' => 'fr', 'title' => 'Article français', 'slug' => 'article-francais', 'content' => 'Français.'],
    ]);

    $this->get('/en/blog/english-article')->assertStatus(200)->assertSee('English article', false);
    $this->get('/fr/blog/article-francais')->assertStatus(200)->assertSee('Article français', false);
    $this->get('/en/blog/article-francais')->assertStatus(404);
});

it('never lets a fallback answer with another post that owns the slug', function () {
    // Only the French post owns "hidden"; the English request has no translation
    // with that slug, so the fallback applies — but it must not pick a post that
    // is translated in English under another slug.
    ($this->makePost)([
        ['locale' => 'fr', 'title' => 'Version française', 'slug' => 'hidden', 'content' => 'Français.'],
    ], ['default_locale' => 'fr']);

    ($this->makePost)([
        ['locale' => 'en', 'title' => 'English owner', 'slug' => 'owner', 'content' => 'English.'],
        ['locale' => 'fr', 'title' => 'Propriétaire français', 'slug' => 'proprietaire', 'content' => 'Français.'],
    ]);

    $this->get('/en/blog/hidden')->assertStatus(200)->assertSee('Version française', false);
});
