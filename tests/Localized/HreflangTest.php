<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;

// NOTE: This test is already using LocalizedTestCase via Pest.php in('Localized')

it('emits hreflang alternates on the blog post page with translated slugs', function () {
    $post = BlogPost::factory()->create([
        'title' => 'English Title',
        'slug' => 'english-slug',
        'content' => 'English content',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $post->translations()->create([
        'locale' => 'fr',
        'title' => 'Titre Français',
        'slug' => 'titre-francais',
        'content' => 'Contenu français',
        'excerpt' => 'Extrait français',
    ]);

    $response = $this->get('/en/blog/english-slug');

    $response->assertStatus(200);

    $response->assertSee(
        '<link rel="alternate" hreflang="en" href="'.route('blog.show', ['locale' => 'en', 'slug' => 'english-slug']).'"',
        false
    );
    $response->assertSee(
        '<link rel="alternate" hreflang="fr" href="'.route('blog.show', ['locale' => 'fr', 'slug' => 'titre-francais']).'"',
        false
    );
    $response->assertSee(
        '<link rel="alternate" hreflang="x-default" href="'.route('blog.show', ['locale' => 'en', 'slug' => 'english-slug']).'"',
        false
    );
});

it('emits hreflang alternates on the blog index page', function () {
    $response = $this->get('/en/blog');

    $response->assertStatus(200);

    $response->assertSee(
        '<link rel="alternate" hreflang="en" href="'.route('blog.index', ['locale' => 'en']).'"',
        false
    );
    $response->assertSee(
        '<link rel="alternate" hreflang="fr" href="'.route('blog.index', ['locale' => 'fr']).'"',
        false
    );
    $response->assertSee(
        '<link rel="alternate" hreflang="x-default" href="'.route('blog.index', ['locale' => 'en']).'"',
        false
    );
});

it('emits hreflang alternates on the category page with translated category slugs', function () {
    $category = Category::factory()->create(['name' => 'Tutorials', 'slug' => 'tutorials']);
    $category->translations()->create([
        'locale' => 'fr',
        'name' => 'Tutoriels',
        'slug' => 'tutoriels',
    ]);

    $response = $this->get('/en/blog/category/tutorials');

    $response->assertStatus(200);

    $response->assertSee(
        '<link rel="alternate" hreflang="en" href="'.route('blog.category', ['locale' => 'en', 'categorySlug' => 'tutorials']).'"',
        false
    );
    $response->assertSee(
        '<link rel="alternate" hreflang="fr" href="'.route('blog.category', ['locale' => 'fr', 'categorySlug' => 'tutoriels']).'"',
        false
    );
    $response->assertSee(
        '<link rel="alternate" hreflang="x-default" href="'.route('blog.category', ['locale' => 'en', 'categorySlug' => 'tutorials']).'"',
        false
    );
});

it('emits hreflang alternates on the tag page with translated tag slugs', function () {
    $tag = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $tag->translations()->create([
        'locale' => 'fr',
        'name' => 'Laravel',
        'slug' => 'laravel-fr',
    ]);

    $response = $this->get('/en/blog/tag/laravel');

    $response->assertStatus(200);

    $response->assertSee(
        '<link rel="alternate" hreflang="en" href="'.route('blog.tag', ['locale' => 'en', 'tagSlug' => 'laravel']).'"',
        false
    );
    $response->assertSee(
        '<link rel="alternate" hreflang="fr" href="'.route('blog.tag', ['locale' => 'fr', 'tagSlug' => 'laravel-fr']).'"',
        false
    );
    $response->assertSee(
        '<link rel="alternate" hreflang="x-default" href="'.route('blog.tag', ['locale' => 'en', 'tagSlug' => 'laravel']).'"',
        false
    );
});

it('emits hreflang alternates on the series page with translated series slugs', function () {
    $series = BlogSeries::factory()->published()->create(['slug' => 'livewire-vs-alpine']);
    $series->translations()->create([
        'locale' => 'fr',
        'title' => 'Livewire contre Alpine',
        'slug' => 'livewire-vs-alpine-fr',
    ]);

    $response = $this->get('/en/blog/series/livewire-vs-alpine');

    $response->assertStatus(200);

    $response->assertSee(
        '<link rel="alternate" hreflang="en" href="'.route('blog.series', ['locale' => 'en', 'seriesSlug' => 'livewire-vs-alpine']).'"',
        false
    );
    $response->assertSee(
        '<link rel="alternate" hreflang="fr" href="'.route('blog.series', ['locale' => 'fr', 'seriesSlug' => 'livewire-vs-alpine-fr']).'"',
        false
    );
    $response->assertSee(
        '<link rel="alternate" hreflang="x-default" href="'.route('blog.series', ['locale' => 'en', 'seriesSlug' => 'livewire-vs-alpine']).'"',
        false
    );
});
