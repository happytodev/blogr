<?php

use Happytodev\Blogr\Models\AuthorBio;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User as WorkbenchUser;

/*
 * Regression tests: #376 — a bio had to be picked post by post, so a series of
 * twenty artworks meant twenty identical choices. A series and a category now
 * carry one, and the article falls back to them.
 */

beforeEach(function () {
    Storage::fake('public');

    $this->user = WorkbenchUser::factory()->create([
        'name' => 'John Doe',
        'slug' => 'johndoe',
        'bio' => ['en' => 'The default bio.', 'fr' => 'La bio par défaut.'],
    ]);

    $other = WorkbenchUser::factory()->create(['name' => 'Jane Roe', 'slug' => 'janeroe']);

    $this->seriesBio = AuthorBio::create([
        'user_id' => $this->user->id,
        'locale' => 'en',
        'label' => 'series',
        'body' => 'John, who drew this series.',
    ]);

    $this->categoryBio = AuthorBio::create([
        'user_id' => $this->user->id,
        'locale' => 'en',
        'label' => 'category',
        'body' => 'John, of the art section.',
    ]);

    $this->postBio = AuthorBio::create([
        'user_id' => $this->user->id,
        'locale' => 'en',
        'label' => 'post',
        'body' => 'John, on this one article.',
    ]);

    $this->foreignBio = AuthorBio::create([
        'user_id' => $other->id,
        'locale' => 'en',
        'label' => 'someone else',
        'body' => 'A bio written by another author.',
    ]);

    $this->category = Category::factory()->create(['slug' => 'art', 'name' => 'Art']);
    $this->category->translations()->create([
        'locale' => 'en',
        'name' => 'Art',
        'slug' => 'art',
    ]);

    $this->series = BlogSeries::create(['slug' => 'amber-fossils', 'is_published' => true]);
    $this->series->translations()->create([
        'locale' => 'en',
        'title' => 'Amber Fossils',
        'slug' => 'amber-fossils',
        'description' => 'A series.',
    ]);

    $this->makePost = function (array $state = []): BlogPost {
        $post = BlogPost::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'default_locale' => 'en',
            'is_published' => true,
            'published_at' => now()->subDay(),
            ...$state,
        ]);

        $post->translations()->create([
            'locale' => 'en',
            'title' => 'AF-01 · day 11',
            'slug' => 'af-01-day-11',
            'content' => 'Some body.',
        ]);

        return $post->fresh();
    };
});

it('takes the bio of the series the post belongs to', function () {
    $this->series->translations()->where('locale', 'en')->update(['author_bio_id' => $this->seriesBio->id]);

    $post = ($this->makePost)(['blog_series_id' => $this->series->id]);

    expect($post->authorBioFor('en'))->toBe('John, who drew this series.');
});

it('takes the bio of the category when there is no series', function () {
    $this->category->translations()->where('locale', 'en')->update(['author_bio_id' => $this->categoryBio->id]);

    $post = ($this->makePost)();

    expect($post->authorBioFor('en'))->toBe('John, of the art section.');
});

it('prefers the series over the category', function () {
    $this->series->translations()->where('locale', 'en')->update(['author_bio_id' => $this->seriesBio->id]);
    $this->category->translations()->where('locale', 'en')->update(['author_bio_id' => $this->categoryBio->id]);

    $post = ($this->makePost)(['blog_series_id' => $this->series->id]);

    expect($post->authorBioFor('en'))->toBe('John, who drew this series.');
});

it('lets the post win over its series and its category', function () {
    $this->series->translations()->where('locale', 'en')->update(['author_bio_id' => $this->seriesBio->id]);
    $this->category->translations()->where('locale', 'en')->update(['author_bio_id' => $this->categoryBio->id]);

    $post = ($this->makePost)(['blog_series_id' => $this->series->id]);
    $post->translations()->where('locale', 'en')->update(['author_bio_id' => $this->postBio->id]);

    expect($post->fresh()->authorBioFor('en'))->toBe('John, on this one article.');
});

it('ignores a bio written by another author', function () {
    $this->series->translations()->where('locale', 'en')->update(['author_bio_id' => $this->foreignBio->id]);

    $post = ($this->makePost)(['blog_series_id' => $this->series->id]);

    expect($post->authorBioFor('en'))->toBeNull();
});

it('falls back to the author default when nothing is set', function () {
    $post = ($this->makePost)(['blog_series_id' => $this->series->id]);

    expect($post->authorBioFor('en'))->toBeNull();

    $this->get('/en/blog/af-01-day-11')
        ->assertStatus(200)
        ->assertSee('The default bio.');
});

it('shows the series bio on the article page', function () {
    $this->series->translations()->where('locale', 'en')->update(['author_bio_id' => $this->seriesBio->id]);

    ($this->makePost)(['blog_series_id' => $this->series->id]);

    $this->get('/en/blog/af-01-day-11')
        ->assertStatus(200)
        ->assertSee('John, who drew this series.')
        ->assertDontSee('The default bio.');
});
