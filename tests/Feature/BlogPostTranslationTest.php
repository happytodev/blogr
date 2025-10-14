<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'blogr.locales.enabled' => true,
        'blogr.locales.available' => ['en', 'fr', 'es', 'de'],
        'blogr.locales.default' => 'en',
    ]);
});

function createBlogPost(array $overrides = []): BlogPost
{
    $category = Category::firstOrCreate(
        ['slug' => 'test-category'],
        ['name' => 'Test Category']
    );

    return BlogPost::create(array_merge([
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
        'excerpt' => 'Test excerpt',
        'category_id' => $category->id,
        'user_id' => 1,
        'is_published' => true,
        'published_at' => now(),
    ], $overrides));
}

test('can create a translation with tldr', function () {
    $post = createBlogPost();
    
    $translation = BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Titre en français',
        'slug' => 'titre-en-francais',
        'excerpt' => 'Extrait en français',
        'content' => 'Contenu en français',
        'tldr' => 'Résumé court en français',
        'seo_title' => 'SEO Titre FR',
        'seo_description' => 'SEO Description FR',
    ]);
    
    expect($translation->tldr)->toBe('Résumé court en français');
});

test('tldr is saved in database', function () {
    $post = createBlogPost();
    
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Article Test',
        'slug' => 'article-test',
        'content' => 'Contenu',
        'tldr' => 'Résumé sauvegardé',
    ]);
    
    $this->assertDatabaseHas('blog_post_translations', [
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'tldr' => 'Résumé sauvegardé',
    ]);
});

test('seo fields are saved correctly', function () {
    $post = createBlogPost();
    
    $translation = BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'es',
        'title' => 'Titulo',
        'slug' => 'titulo',
        'content' => 'Contenido',
        'seo_title' => 'SEO Titulo ES',
        'seo_description' => 'Descripción SEO ES',
        'seo_keywords' => 'palabra1, palabra2',
    ]);
    
    expect($translation->seo_title)->toBe('SEO Titulo ES');
    expect($translation->seo_description)->toBe('Descripción SEO ES');
});

test('can update translation with tldr and seo fields', function () {
    $post = createBlogPost();
    
    $translation = BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'de',
        'title' => 'Titel',
        'slug' => 'titel',
        'content' => 'Inhalt',
        'tldr' => 'Kurz',
        'seo_title' => 'SEO Titel',
    ]);
    
    $translation->update([
        'tldr' => 'Aktualisierte Zusammenfassung',
        'seo_description' => 'Neue SEO Beschreibung',
    ]);
    
    expect($translation->fresh()->tldr)->toBe('Aktualisierte Zusammenfassung');
    expect($translation->fresh()->seo_description)->toBe('Neue SEO Beschreibung');
});

test('translation belongs to blog post', function () {
    $post = createBlogPost();
    
    $translation = BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Test',
        'slug' => 'test',
        'content' => 'Content',
    ]);
    
    expect($translation->post)->toBeInstanceOf(BlogPost::class);
    expect($translation->post->id)->toBe($post->id);
});

test('blog post can have multiple translations', function () {
    $post = createBlogPost();
    
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Français',
        'slug' => 'francais',
        'content' => 'Contenu FR',
    ]);
    
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'es',
        'title' => 'Español',
        'slug' => 'espanol',
        'content' => 'Contenido ES',
    ]);
    
    $post->load('translations');
    expect($post->translations)->toHaveCount(3); // EN (from hook) + FR + ES
});
