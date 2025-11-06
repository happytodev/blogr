<?php

use Happytodev\Blogr\Models\BlogPost;

// NOTE: This test is already using LocalizedTestCase via Pest.php in('Localized')

it('can display a blog post with locales enabled', function () {
    $post = BlogPost::factory()->create([
        'title' => 'English Title',
        'slug' => 'english-slug',
        'content' => 'English content here',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    
    // Verify the Observer created 'en' translation
    $enTranslation = $post->translations()->where('locale', 'en')->first();
    expect($enTranslation)->not->toBeNull();
    expect($enTranslation->slug)->toBe('english-slug');
    
    $response = $this->get('/en/blog/english-slug');
    
    $response->assertStatus(200);
    $response->assertSee('English Title');
    $response->assertViewHas('currentLocale', 'en');
});

it('can display translated post in French', function () {
    $post = BlogPost::factory()->create([
        'title' => 'English Title',
        'slug' => 'english-slug',
        'content' => 'English content',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    
    // Add French translation
    $post->translations()->create([
        'locale' => 'fr',
        'title' => 'Titre Français',
        'slug' => 'titre-francais',
        'content' => 'Contenu français',
        'excerpt' => 'Extrait français',
    ]);
    
    $response = $this->get('/fr/blog/titre-francais');
    
    $response->assertStatus(200);
    $response->assertSee('Titre Français');
    $response->assertViewHas('currentLocale', 'fr');
});

it('provides translations for language switcher', function () {
    $post = BlogPost::factory()->create([
        'title' => 'English Title',
        'slug' => 'english-slug',
        'content' => 'English content',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    
    // Add French translation
    $post->translations()->create([
        'locale' => 'fr',
        'title' => 'Titre Français',
        'slug' => 'titre-francais',
        'content' => 'Contenu français',
        'excerpt' => 'Extrait français',
    ]);
    
    $response = $this->get('/en/blog/english-slug');
    
    $response->assertStatus(200);
    
    // Check that availableTranslations view data includes both locales
    $translations = $response->viewData('availableTranslations');
    expect($translations)->toHaveCount(2); // en + fr
});
