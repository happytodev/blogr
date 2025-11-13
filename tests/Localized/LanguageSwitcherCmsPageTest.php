<?php

use Happytodev\Blogr\Models\CmsPage;

// NOTE: This test is already using LocalizedTestCase via Pest.php in('Localized')

it('should switch language on cms page using translated slug', function () {
    // Create a CMS page with two translations
    $page = CmsPage::factory()
        ->published()
        ->create(['slug' => 'contact']);

    // English translation with slug "contact-us"
    $page->translations()->create([
        'locale' => 'en',
        'slug' => 'contact-us',
        'title' => 'Contact Us',
        'content' => 'English contact page',
    ]);

    // French translation with slug "nous-contacter"
    $page->translations()->create([
        'locale' => 'fr',
        'slug' => 'nous-contacter',
        'title' => 'Nous Contacter',
        'content' => 'Page de contact française',
    ]);

    // Access French page
    $response = $this->get('/fr/nous-contacter');
    expect($response->status())->toBe(200);

    // The language switcher should generate a link to /en/contact-us (not /en/nous-contacter)
    $content = $response->getContent();
    expect($content)->toContain('/en/contact-us');
    expect($content)->not->toContain('/en/nous-contacter');
});

it('should keep same slug when switching to same language on cms page', function () {
    $page = CmsPage::factory()
        ->published()
        ->create(['slug' => 'contact']);

    $page->translations()->create([
        'locale' => 'en',
        'slug' => 'contact-us',
        'title' => 'Contact Us',
        'content' => 'English contact page',
    ]);

    $page->translations()->create([
        'locale' => 'fr',
        'slug' => 'nous-contacter',
        'title' => 'Nous Contacter',
        'content' => 'Page de contact française',
    ]);

    $response = $this->get('/fr/nous-contacter');
    expect($response->status())->toBe(200);

    $content = $response->getContent();
    
    // Should have a link to /fr/nous-contacter (same page)
    expect($content)->toContain('/fr/nous-contacter');
});

it('should work for cms pages with same slug in all languages', function () {
    $page = CmsPage::factory()
        ->published()
        ->create(['slug' => 'about']);

    $page->translations()->create([
        'locale' => 'en',
        'slug' => 'about',
        'title' => 'About Us',
        'content' => 'About us in English',
    ]);

    $page->translations()->create([
        'locale' => 'fr',
        'slug' => 'about',
        'title' => 'À Propos',
        'content' => 'À propos en français',
    ]);

    $response = $this->get('/en/about');
    expect($response->status())->toBe(200);

    $content = $response->getContent();
    
    // Should have links to both /en/about and /fr/about
    expect($content)->toContain('/en/about');
    expect($content)->toContain('/fr/about');
});
