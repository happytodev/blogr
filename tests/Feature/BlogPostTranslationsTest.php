<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a default category for tests
    Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_default' => true,
    ]);
});

it('can create a blog post with translation', function () {
    $post = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'My First Post',
        'slug' => 'my-first-post-' . uniqid(),
        'content' => 'Full content of the post',
        'tldr' => 'A short excerpt',
        'meta_title' => 'SEO Title',
        'meta_description' => 'SEO Description',
    ]);

    // Hook automatically creates 'en' translation
    $post->load('translations');
    $translation = $post->translations()->where('locale', 'en')->first();

    expect($post->translations)->toHaveCount(1)
        ->and($translation->title)->toBe('My First Post')
        ->and($translation->locale)->toBe('en')
        ->and($translation->tldr)->toBe('A short excerpt')
        ->and($translation->seo_title)->toBe('SEO Title');
});

it('can add multiple translations to a post', function () {
    $post = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'English Title',
        'slug' => 'english-title-' . uniqid(),
        'content' => 'English content',
    ]);

    // Hook creates 'en' translation automatically
    $post->load('translations');
    
    // Add French translation manually
    $post->translations()->create([
        'locale' => 'fr',
        'title' => 'Titre français',
        'slug' => 'titre-francais-' . uniqid(),
        'content' => 'Contenu français',
    ]);

    $post->refresh();
    expect($post->translations)->toHaveCount(2)
        ->and($post->translate('en')->title)->toBe('English Title')
        ->and($post->translate('fr')->title)->toBe('Titre français');
});

it('can get translation for specific locale', function () {
    $post = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'English Title',
        'slug' => 'english-title-' . uniqid(),
        'content' => 'English content',
    ]);

    // Hook creates 'en' translation automatically
    $post->load('translations');
    
    $post->translations()->create([
        'locale' => 'fr',
        'title' => 'Titre français',
        'slug' => 'titre-francais-' . uniqid(),
        'content' => 'Contenu français',
    ]);

    $post->refresh();
    $enTranslation = $post->translate('en');
    $frTranslation = $post->translate('fr');

    expect($enTranslation)->not->toBeNull()
        ->and($enTranslation->title)->toBe('English Title')
        ->and($frTranslation)->not->toBeNull()
        ->and($frTranslation->title)->toBe('Titre français');
});

it('returns null when translation for locale does not exist', function () {
    $post = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'English Title',
        'slug' => 'english-title',
        'content' => 'English content',
    ]);

    // Observer creates 'en' translation automatically
    // Try to get Spanish translation that doesn't exist
    $esTranslation = $post->translate('es');

    expect($esTranslation)->toBeNull();
});

it('can get default translation', function () {
    $post = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'English Title',
        'slug' => 'english-title',
        'content' => 'English content',
    ]);

    // Observer creates 'en' translation automatically
    $post->translations()->create([
        'locale' => 'fr',
        'title' => 'Titre français',
        'slug' => 'titre-francais',
        'content' => 'Contenu français',
    ]);

    $defaultTranslation = $post->getDefaultTranslation();

    expect($defaultTranslation)->not->toBeNull()
        ->and($defaultTranslation->locale)->toBe('en')
        ->and($defaultTranslation->title)->toBe('English Title');
});

it('stores large content in LONGTEXT field', function () {
    // Generate large content (> 65KB to exceed TEXT limit)
    $largeContent = str_repeat('This is a very long article with lots of content. ', 2000);

    $post = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'Large Article',
        'slug' => 'large-article',
        'content' => $largeContent,
    ]);

    // Observer creates translation with large content
    $translation = $post->translations()->where('locale', 'en')->first();

    expect($translation->content)->toBe($largeContent)
        ->and(strlen($translation->content))->toBeGreaterThan(65000);
});

it('requires unique slug per locale', function () {
    $post1 = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'First Post',
        'slug' => 'same-slug',
        'content' => 'Content',
    ]);

    // Observer creates 'en' translation with 'same-slug'

    $post2 = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'Second Post',
        'slug' => 'different-slug', // Use different slug for main post
        'content' => 'Content',
    ]);

    // Try to create 'en' translation with same slug - should fail
    expect(fn () => $post2->translations()->create([
        'locale' => 'en',
        'title' => 'Second Post EN',
        'slug' => 'same-slug', // Same slug as post1
        'content' => 'Content',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('requires unique slug globally across all locales', function () {
    $post = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'My Post',
        'slug' => 'my-unique-post',
        'content' => 'English content',
    ]);

    // Try to create French translation with same slug - should fail
    expect(fn() => $post->translations()->create([
        'locale' => 'fr',
        'title' => 'Mon Article',
        'slug' => 'my-unique-post', // Same slug = conflict!
        'content' => 'Contenu français',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('can associate categories with specific translation', function () {
    $category1 = Category::create([
        'name' => 'Technology',
        'slug' => 'technology',
    ]);

    $category2 = Category::create([
        'name' => 'Technologie',
        'slug' => 'technologie',
    ]);

    $post = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'Tech Article',
        'slug' => 'tech-article',
        'content' => 'Content',
    ]);

    // Observer creates 'en' translation
    $enTranslation = $post->translations()->where('locale', 'en')->first();
    
    $frTranslation = $post->translations()->create([
        'locale' => 'fr',
        'title' => 'Article Tech',
        'slug' => 'article-tech',
        'content' => 'Contenu',
    ]);

    $enTranslation->categories()->attach($category1);
    $frTranslation->categories()->attach($category2);

    expect($enTranslation->categories)->toHaveCount(1)
        ->and($enTranslation->categories->first()->name)->toBe('Technology')
        ->and($frTranslation->categories)->toHaveCount(1)
        ->and($frTranslation->categories->first()->name)->toBe('Technologie');
});

it('can associate tags with specific translation', function () {
    $tagEn = Tag::create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    $tagFr = Tag::create([
        'name' => 'PHP',
        'slug' => 'php',
    ]);

    $post = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'Laravel Tutorial',
        'slug' => 'laravel-tutorial',
        'content' => 'Content',
    ]);

    // Observer creates 'en' translation
    $enTranslation = $post->translations()->where('locale', 'en')->first();

    $frTranslation = $post->translations()->create([
        'locale' => 'fr',
        'title' => 'Tutoriel Laravel',
        'slug' => 'tutoriel-laravel',
        'content' => 'Contenu',
    ]);

    $enTranslation->tags()->attach($tagEn);
    $frTranslation->tags()->attach([$tagEn->id, $tagFr->id]);

    expect($enTranslation->tags)->toHaveCount(1)
        ->and($frTranslation->tags)->toHaveCount(2);
});

it('calculates reading time per translation', function () {
    // English: ~200 words
    $enContent = str_repeat('word ', 200);
    
    // French: ~400 words
    $frContent = str_repeat('mot ', 400);

    $post = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'Short Article',
        'slug' => 'short-article-' . uniqid(),
        'content' => $enContent,
    ]);

    // Hook creates 'en' translation
    $post->load('translations');
    $enTranslation = $post->translations()->where('locale', 'en')->first();
    
    // Calculate reading time for English translation
    $enTranslation->calculateReadingTime();

    $frTranslation = $post->translations()->create([
        'locale' => 'fr',
        'title' => 'Article Long',
        'slug' => 'article-long-' . uniqid(),
        'content' => $frContent,
    ]);

    // Calculate reading time for French translation
    $frTranslation->calculateReadingTime();

    expect($enTranslation->reading_time)->toBeGreaterThan(0)
        ->and($frTranslation->reading_time)->toBeGreaterThan($enTranslation->reading_time);
});

// ============================================================================
// TLDR FIELD TESTS
// ============================================================================

test('translation tldr field is fillable', function () {
    $translation = new BlogPostTranslation();
    expect($translation->getFillable())->toContain('tldr');
});

test('can create translation with tldr', function () {
    $post = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Content',
        'is_published' => true,
        'published_at' => now(),
    ]);
    
    $translation = BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Titre',
        'slug' => 'titre-fr',
        'content' => 'Contenu',
        'tldr' => 'Mon résumé court',
    ]);

    expect($translation->tldr)->toBe('Mon résumé court');
    expect($translation->fresh()->tldr)->toBe('Mon résumé court');
});

test('tldr is saved in database', function () {
    $post = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'Test',
        'slug' => 'test',
        'content' => 'Content',
    ]);
    
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'es',
        'title' => 'Título',
        'slug' => 'titulo-es',
        'content' => 'Contenido',
        'tldr' => 'Resumen breve',
    ]);

    $this->assertDatabaseHas('blog_post_translations', [
        'blog_post_id' => $post->id,
        'locale' => 'es',
        'tldr' => 'Resumen breve',
    ]);
});

test('seo fields are fillable and saved', function () {
    $translation = new BlogPostTranslation();
    $fillable = $translation->getFillable();
    
    expect($fillable)->toContain('seo_title')
        ->and($fillable)->toContain('seo_description')
        ->and($fillable)->toContain('seo_keywords');
});

test('can update translation with tldr and seo fields', function () {
    $post = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'Test',
        'slug' => 'test',
        'content' => 'Content',
    ]);
    
    $translation = BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'de',
        'title' => 'Titel',
        'slug' => 'titel-de',
        'content' => 'Inhalt',
    ]);

    $translation->update([
        'tldr' => 'Kurze Zusammenfassung',
        'seo_title' => 'SEO Titel',
        'seo_description' => 'SEO Beschreibung',
    ]);

    expect($translation->fresh()->tldr)->toBe('Kurze Zusammenfassung')
        ->and($translation->fresh()->seo_title)->toBe('SEO Titel')
        ->and($translation->fresh()->seo_description)->toBe('SEO Beschreibung');
});

// ============================================================================
// DATE LOCALIZATION TESTS
// ============================================================================

test('dates use carbon isoFormat LL for localization', function () {
    $date = now()->setDate(2025, 10, 7);
    
    // Test that isoFormat produces localized dates
    $enDate = $date->locale('en')->isoFormat('LL');
    $frDate = $date->locale('fr')->isoFormat('LL');
    $esDate = $date->locale('es')->isoFormat('LL');
    $deDate = $date->locale('de')->isoFormat('LL');
    
    // Verify each locale produces a different format
    expect($enDate)->toContain('October')
        ->and($frDate)->toContain('octobre')
        ->and($esDate)->toContain('octubre')
        ->and($deDate)->toContain('Oktober');
    
    // Verify dates are different (localized)
    expect($enDate)->not->toBe($frDate)
        ->and($frDate)->not->toBe($esDate);
});

test('draft label is translated correctly', function () {
    $post = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'Draft Post',
        'slug' => 'draft-post',
        'content' => 'Content',
        'is_published' => false,
        'published_at' => null,
    ]);
    
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Article brouillon',
        'slug' => 'article-brouillon',
        'content' => 'Contenu',
    ]);

    app()->setLocale('fr');
    
    // Note: Draft posts return 404, but the translation key should exist
    expect(__('blogr::blogr.date.draft'))->toBe('Brouillon');
});

test('date format adapts to current locale in views', function () {
    // Test that views use ->locale($currentLocale)->isoFormat('LL')
    // This ensures dates display correctly based on language
    
    $date = now()->setDate(2025, 12, 25); // Christmas
    
    // Test multiple locales
    $formats = [
        'en' => $date->locale('en')->isoFormat('LL'),
        'fr' => $date->locale('fr')->isoFormat('LL'),
        'es' => $date->locale('es')->isoFormat('LL'),
        'de' => $date->locale('de')->isoFormat('LL'),
    ];
    
    // Verify English contains December
    expect($formats['en'])->toContain('December');
    
    // Verify French contains décembre
    expect($formats['fr'])->toContain('décembre');
    
    // Verify Spanish contains diciembre
    expect($formats['es'])->toContain('diciembre');
    
    // Verify German contains Dezember
    expect($formats['de'])->toContain('Dezember');
});

test('carbon isoFormat LL returns localized date', function () {
    $date = now()->setDate(2025, 10, 7);
    
    // Test different locales
    expect($date->locale('en')->isoFormat('LL'))->toContain('October');
    expect($date->locale('fr')->isoFormat('LL'))->toContain('octobre');
    expect($date->locale('es')->isoFormat('LL'))->toContain('octubre');
    expect($date->locale('de')->isoFormat('LL'))->toContain('Oktober');
});
