<?php

use Happytodev\Blogr\Models\AuthorBio;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;

/*
 * Regression tests: #373 — an author had one bio per locale and nothing else, so
 * every article they wrote carried the same paragraph. Now they keep several, and
 * a translation picks the one it shows.
 */

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create([
        'name' => 'John Doe',
        'slug' => 'johndoe',
        'bio' => ['en' => 'The default English bio.', 'fr' => 'La bio française par défaut.'],
    ]);

    $this->category = Category::factory()->create(['slug' => 'art', 'name' => 'Art']);

    $this->makePost = function (array $translation, array $state = []): BlogPost {
        $post = BlogPost::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now()->subDay(),
            'default_locale' => 'en',
            ...$state,
        ]);

        $post->translations()->create([
            'locale' => 'en',
            'title' => 'A post',
            'slug' => 'a-post',
            'content' => 'Some body text.',
            ...$translation,
        ]);

        return $post->fresh();
    };

    $this->artistBio = AuthorBio::create([
        'user_id' => $this->user->id,
        'locale' => 'en',
        'label' => 'artwork',
        'body' => 'John draws generative landscapes.',
    ]);

    $this->devBio = AuthorBio::create([
        'user_id' => $this->user->id,
        'locale' => 'en',
        'label' => 'dev',
        'body' => 'John writes PHP for a living.',
        'is_default' => true,
    ]);
});

it('shows the bio the translation picked', function () {
    $post = ($this->makePost)(['author_bio_id' => $this->artistBio->id]);

    $this->get('/en/blog/a-post')
        ->assertStatus(200)
        ->assertSee('John draws generative landscapes.')
        ->assertDontSee('The default English bio.');
});

it('falls back to the author default bio when nothing is picked', function () {
    $post = ($this->makePost)([]);

    expect($post->authorBioFor('en'))->toBeNull();

    $this->get('/en/blog/a-post')
        ->assertStatus(200)
        ->assertSee('The default English bio.');
});

it('picks the bio of the locale being read', function () {
    $post = ($this->makePost)(['author_bio_id' => $this->artistBio->id]);

    $post->translations()->create([
        'locale' => 'fr',
        'title' => 'Un article',
        'slug' => 'un-article',
        'content' => 'Un corps de texte.',
    ]);

    $this->get('/en/blog/a-post')->assertSee('John draws generative landscapes.');
    $this->get('/fr/blog/un-article')->assertSee('La bio française par défaut.');
});

it('replaces an author bios from a form, with one default per language', function () {
    AuthorBio::syncFor($this->user->id, [
        ['locale' => 'fr', 'label' => 'artiste', 'is_default' => true, 'body' => 'Génère des paysages.'],
        ['locale' => 'fr', 'label' => 'dev', 'is_default' => true, 'body' => 'Écrit du PHP.'],
        ['locale' => 'en', 'label' => 'dev', 'is_default' => true, 'body' => 'Writes PHP.'],
        ['locale' => 'en', 'label' => '', 'body' => 'sans nom'],
    ]);

    expect($this->user->authorBios()->orderBy('label')->pluck('label')->all())
        ->toBe(['artiste', 'dev', 'dev']);

    expect($this->user->authorBios()->where('locale', 'fr')->where('is_default', true)->pluck('label')->all())
        ->toBe(['artiste']);

    expect($this->user->authorBios()->where('locale', 'en')->where('is_default', true)->pluck('label')->all())
        ->toBe(['dev']);
});

it('returns the chosen body only when it has one', function () {
    $empty = AuthorBio::create([
        'user_id' => $this->user->id,
        'locale' => 'en',
        'label' => 'blank',
        'body' => '   ',
    ]);

    $post = ($this->makePost)(['author_bio_id' => $empty->id]);

    expect($post->authorBioFor('en'))->toBeNull();
    $this->get('/en/blog/a-post')->assertSee('The default English bio.');
});
