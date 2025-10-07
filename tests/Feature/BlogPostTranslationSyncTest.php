<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;

beforeEach(function () {
    // Create a default category
    Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_default' => true,
    ]);
});

it('automatically creates a default translation when a post is created', function () {
    config(['blogr.locales.default' => 'fr']);
    
    $post = BlogPost::factory()->create([
        'title' => 'Mon Article',
        'slug' => 'mon-article',
        'content' => 'Contenu de mon article',
        'tldr' => 'Résumé rapide',
        'meta_title' => 'Titre Meta',
        'meta_description' => 'Description Meta',
        'default_locale' => 'fr',
    ]);
    
    // Verify the translation was created
    expect($post->translations()->count())->toBe(1);
    
    $translation = $post->translations()->first();
    
    expect($translation)->toBeInstanceOf(BlogPostTranslation::class)
        ->and($translation->locale)->toBe('fr')
        ->and($translation->title)->toBe('Mon Article')
        ->and($translation->slug)->toBe('mon-article')
        ->and($translation->content)->toBe('Contenu de mon article')
        ->and($translation->excerpt)->toBe('Résumé rapide')
        ->and($translation->seo_title)->toBe('Titre Meta')
        ->and($translation->seo_description)->toBe('Description Meta')
        ->and($translation->reading_time)->toBeGreaterThan(0);
});

it('automatically updates the default translation when main fields are modified', function () {
    config(['blogr.locales.default' => 'en']);
    
    $post = BlogPost::factory()->create([
        'title' => 'Original Title',
        'slug' => 'original-title',
        'content' => 'Original content',
        'default_locale' => 'en',
    ]);
    
    // Verify initial translation
    $translation = $post->translations()->where('locale', 'en')->first();
    expect($translation->title)->toBe('Original Title');
    
    // Update the post
    $post->update([
        'title' => 'Modified Title',
        'content' => 'Modified content with more words to test reading time calculation',
    ]);
    
    // Refresh the translation from database
    $translation = $post->translations()->where('locale', 'en')->first();
    
    expect($translation->title)->toBe('Modified Title')
        ->and($translation->content)->toBe('Modified content with more words to test reading time calculation');
});

it('does not update translation when non-translatable fields are modified', function () {
    config(['blogr.locales.default' => 'en']);
    
    $post = BlogPost::factory()->create([
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
        'default_locale' => 'en',
    ]);
    
    $translation = $post->translations()->where('locale', 'en')->first();
    $originalUpdatedAt = $translation->updated_at;
    
    // Wait a bit to ensure timestamps would be different
    sleep(1);
    
    // Update non-translatable field
    $post->update(['photo' => 'new-photo.jpg']);
    
    $translation->refresh();
    
    // Translation should not have been touched
    expect($translation->updated_at->timestamp)->toBe($originalUpdatedAt->timestamp);
});

it('preserves manual translations when updating main post', function () {
    config(['blogr.locales.default' => 'en']);
    
    $post = BlogPost::factory()->create([
        'title' => 'English Title',
        'slug' => 'english-title',
        'content' => 'English content',
        'default_locale' => 'en',
    ]);
    
    // Manually create a French translation
    $frenchTranslation = BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Titre Français',
        'slug' => 'titre-francais',
        'content' => 'Contenu français',
        'reading_time' => 1,
    ]);
    
    // Update the main post
    $post->update(['title' => 'Updated English Title']);
    
    // English translation should be updated
    $englishTranslation = $post->translations()->where('locale', 'en')->first();
    expect($englishTranslation->title)->toBe('Updated English Title');
    
    // French translation should remain unchanged
    $frenchTranslation->refresh();
    expect($frenchTranslation->title)->toBe('Titre Français')
        ->and($frenchTranslation->content)->toBe('Contenu français');
});

it('deletes all translations when post is deleted', function () {
    config(['blogr.locales.default' => 'en']);
    
    $post = BlogPost::factory()->create([
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
        'default_locale' => 'en',
    ]);
    
    // Create additional translations
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Test FR',
        'slug' => 'test-fr',
        'content' => 'Contenu FR',
        'reading_time' => 1,
    ]);
    
    $postId = $post->id;
    
    // Verify translations exist
    expect(BlogPostTranslation::where('blog_post_id', $postId)->count())->toBe(2);
    
    // Delete the post
    $post->delete();
    
    // Verify all translations were deleted
    expect(BlogPostTranslation::where('blog_post_id', $postId)->count())->toBe(0);
});
