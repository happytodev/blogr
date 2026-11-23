<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;

/*
 * Regression tests: #367 — a post without an SEO description got the first 160
 * characters of its body, markdown included: "**Provenance.** … ## Histoire" is
 * what Google and every social card displayed.
 */

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create(['name' => 'John Doe', 'slug' => 'johndoe']);

    $this->category = Category::factory()->create();

    $this->makePost = function (array $translation): BlogPost {
        $post = BlogPost::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now()->subDay(),
            'default_locale' => 'fr',
        ]);

        $post->translations()->create([
            'locale' => 'fr',
            'content' => 'Un corps.',
            ...$translation,
        ]);

        return $post;
    };
});

it('flattens the markdown of the body instead of showing its syntax', function () {
    ($this->makePost)([
        'title' => 'AF-01 · day 11',
        'slug' => 'af-01-day-11',
        'content' => "**Provenance.** frameworkheroes · 7a32a883\n\n## Histoire\n\nAu jour 11, le dépôt comptait 115 fichiers.",
    ]);

    $response = $this->get('/fr/blog/af-01-day-11');

    $response->assertStatus(200);
    $response->assertSee('<meta name="description" content="Provenance. frameworkheroes · 7a32a883 Histoire Au jour 11', false);
    $response->assertDontSee('**Provenance.**', false);
    $response->assertDontSee('## Histoire', false);
});

it('prefers the excerpt the editor wrote', function () {
    ($this->makePost)([
        'title' => 'Article avec extrait',
        'slug' => 'article-avec-extrait',
        'content' => '**Provenance.** du markdown brut.',
        'excerpt' => 'Un extrait écrit à la main.',
    ]);

    $this->get('/fr/blog/article-avec-extrait')
        ->assertStatus(200)
        ->assertSee('<meta name="description" content="Un extrait écrit à la main."', false);
});

it('lets the SEO description win over everything', function () {
    ($this->makePost)([
        'title' => 'Article avec SEO',
        'slug' => 'article-avec-seo',
        'content' => 'Un corps.',
        'excerpt' => 'Un extrait.',
        'seo_description' => 'La description choisie pour les moteurs.',
    ]);

    $this->get('/fr/blog/article-avec-seo')
        ->assertStatus(200)
        ->assertSee('<meta name="description" content="La description choisie pour les moteurs."', false);
});

it('uses the tldr when there is no excerpt', function () {
    ($this->makePost)([
        'title' => 'Article avec tldr',
        'slug' => 'article-avec-tldr',
        'content' => '**Provenance.** du markdown brut.',
        'tldr' => 'Le résumé court.',
    ]);

    $this->get('/fr/blog/article-avec-tldr')
        ->assertStatus(200)
        ->assertSee('<meta name="description" content="Le résumé court."', false);
});

it('gives the series page a plain text description too', function () {
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
        'description' => "**Une série** de dessins.\n\nSecond paragraphe.",
    ]);

    $response = $this->get('/fr/blog/series/amber-fossils');

    $response->assertStatus(200);
    $response->assertSee('<meta name="description" content="Une série de dessins. Second paragraphe."', false);
});
