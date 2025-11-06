<?php
uses(Happytodev\Blogr\Tests\TestCase::class);


// Duplicate test file: real Cms tests live at tests/Feature/Cms/CmsPageTest.php
// This file is intentionally short-circuited to avoid running duplicate tests
// under the wrong TestCase. Returning early prevents Pest from evaluating
// the rest of this file.
return;

it('enforces unique locale per page', function () {
    $page = CmsPage::factory()->create();

    $page->translations()->create([
        'locale' => 'en',
        'slug' => 'about-us',
        'title' => 'About Us',
    ]);

    // Try to create duplicate locale for same page
    expect(fn () => $page->translations()->create([
        'locale' => 'en',
        'slug' => 'about-us-2',
        'title' => 'About Us Duplicate',
    ]))->toThrow(Exception::class);
});

it('enforces unique slug per locale', function () {
    $page1 = CmsPage::factory()->create();
    $page2 = CmsPage::factory()->create();

    $page1->translations()->create([
        'locale' => 'en',
        'slug' => 'about',
        'title' => 'About Page 1',
    ]);

    // Try to create same slug in same locale for different page
    expect(fn () => $page2->translations()->create([
        'locale' => 'en',
        'slug' => 'about',
        'title' => 'About Page 2',
    ]))->toThrow(Exception::class);
});

it('allows same slug in different locales', function () {
    $page = CmsPage::factory()->create();

    $page->translations()->create([
        'locale' => 'en',
        'slug' => 'about',
        'title' => 'About',
    ]);

    $page->translations()->create([
        'locale' => 'fr',
        'slug' => 'about',
        'title' => 'À propos',
    ]);

    expect($page->translations()->count())->toBe(2);
});

// ============================================================================
// Translation Retrieval Tests
// ============================================================================

it('can get translation for specific locale', function () {
    $page = CmsPage::factory()->create();

    $page->translations()->create([
        'locale' => 'en',
        'slug' => 'about',
        'title' => 'About Us',
    ]);

    $page->translations()->create([
        'locale' => 'fr',
        'slug' => 'a-propos',
        'title' => 'À propos',
    ]);

    $enTranslation = $page->translation('en');
    $frTranslation = $page->translation('fr');

    expect($enTranslation->title)->toBe('About Us')
        ->and($frTranslation->title)->toBe('À propos');
});

it('returns null when translation not found', function () {
    $page = CmsPage::factory()->create();

    $translation = $page->translation('de');

    expect($translation)->toBeNull();
});

it('gets current translation with fallback', function () {
    $page = CmsPage::factory()->create(['default_locale' => 'en']);

    $page->translations()->create([
        'locale' => 'en',
        'slug' => 'about',
        'title' => 'About Us',
    ]);

    // No French translation, should fallback to default (en)
    $translation = $page->currentTranslation('fr');

    expect($translation)->not->toBeNull()
        ->and($translation->locale)->toBe('en')
        ->and($translation->title)->toBe('About Us');
});

// ============================================================================
// Template Tests
// ============================================================================

it('can create page with different templates', function () {
    $templates = [
        CmsPageTemplate::DEFAULT,
        CmsPageTemplate::LANDING,
        CmsPageTemplate::CONTACT,
        CmsPageTemplate::PRICING,
        CmsPageTemplate::FAQ,
        CmsPageTemplate::CUSTOM,
    ];

    foreach ($templates as $template) {
        $page = CmsPage::factory()->withTemplate($template)->create();
        expect($page->template)->toBe($template);
    }
});

it('can filter pages by template', function () {
    CmsPage::factory()->withTemplate(CmsPageTemplate::DEFAULT)->count(3)->create();
    CmsPage::factory()->withTemplate(CmsPageTemplate::LANDING)->count(2)->create();
    CmsPage::factory()->withTemplate(CmsPageTemplate::CONTACT)->count(1)->create();

    $defaultPages = CmsPage::byTemplate(CmsPageTemplate::DEFAULT)->get();
    $landingPages = CmsPage::byTemplate(CmsPageTemplate::LANDING)->get();

    expect($defaultPages)->toHaveCount(3)
        ->and($landingPages)->toHaveCount(2);
});

// ============================================================================
// Blocks Tests
// ============================================================================

it('can store blocks as JSON', function () {
    $blocks = [
        [
            'type' => 'hero',
            'data' => [
                'title' => 'Welcome',
                'subtitle' => 'To our website',
            ],
        ],
        [
            'type' => 'features',
            'data' => [
                'items' => [
                    ['name' => 'Feature 1', 'description' => 'Description 1'],
                    ['name' => 'Feature 2', 'description' => 'Description 2'],
                ],
            ],
        ],
    ];

    $page = CmsPage::factory()->create(['blocks' => $blocks]);

    expect($page->blocks)->toBeArray()
        ->and($page->blocks)->toHaveCount(2)
        ->and($page->blocks[0]['type'])->toBe('hero')
        ->and($page->blocks[1]['type'])->toBe('features');
});

it('can create landing page with blocks using factory', function () {
    $page = CmsPage::factory()->landing()->create();

    expect($page->template)->toBe(CmsPageTemplate::LANDING)
        ->and($page->blocks)->toBeArray()
        ->and($page->blocks)->toHaveCount(2);
});

// ============================================================================
// Published Scope Tests
// ============================================================================

it('published scope returns only published pages', function () {
    CmsPage::factory()->published()->count(3)->create();
    CmsPage::factory()->unpublished()->count(2)->create();
    CmsPage::factory()->scheduled()->count(1)->create();

    $publishedPages = CmsPage::published()->get();

    expect($publishedPages)->toHaveCount(3);
});

it('published scope excludes scheduled pages', function () {
    CmsPage::factory()->published()->create();
    CmsPage::factory()->scheduled()->create(['published_at' => now()->addWeek()]);

    $publishedPages = CmsPage::published()->get();

    expect($publishedPages)->toHaveCount(1);
});

// ============================================================================
// SEO Methods Tests
// ============================================================================

it('translation has SEO methods', function () {
    $page = CmsPage::factory()->create();

    $translation = $page->translations()->create([
        'locale' => 'en',
        'slug' => 'about',
        'title' => 'About Us',
        'meta_title' => 'About Us - Company Name',
        'meta_description' => 'Learn more about our company',
        'meta_keywords' => 'about, company, team',
    ]);

    expect($translation->seoTitle())->toBe('About Us - Company Name')
        ->and($translation->seoDescription())->toBe('Learn more about our company')
        ->and($translation->seoKeywords())->toBe(['about', 'company', 'team']);
});

it('SEO title fallbacks to title when meta_title is empty', function () {
    $page = CmsPage::factory()->create();

    $translation = $page->translations()->create([
        'locale' => 'en',
        'slug' => 'about',
        'title' => 'About Us',
        'meta_title' => null,
    ]);

    expect($translation->seoTitle())->toBe('About Us');
});
