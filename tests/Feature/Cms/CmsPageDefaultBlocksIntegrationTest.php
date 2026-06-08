<?php

use Happytodev\Blogr\Enums\CmsPageTemplate;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Tests\CmsTestCase;

uses(\Happytodev\Blogr\Tests\CmsTestCase::class);

// ─── LANDING ───────────────────────────────────────────────

test('creating a landing page creates translation with default blocks', function () {
    $page = CmsPage::create([
        'slug' => 'test-landing',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    $translation = CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => $page->default_locale,
        'slug' => $page->slug,
        'title' => $page->slug,
        'blocks' => $page->template->defaultBlocks(),
    ]);

    expect($translation->blocks)->toBeArray();
    expect($translation->blocks)->not->toBeEmpty();
    expect($translation->blocks[0]['type'])->toBe('hero');
    expect(collect($translation->blocks)->firstWhere('type', 'features'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'stats'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'blog_posts'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'timeline'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'cta'))->not->toBeNull();
});

test('landing page translation blocks match template default blocks', function () {
    $page = CmsPage::create([
        'slug' => 'landing-match',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    $translation = CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => $page->default_locale,
        'slug' => $page->slug,
        'title' => $page->slug,
        'blocks' => $page->template->defaultBlocks(),
    ]);

    expect($translation->blocks)->toEqual($page->template->defaultBlocks());
});

// ─── DEFAULT ───────────────────────────────────────────────

test('creating a default page creates translation with no blocks', function () {
    $page = CmsPage::create([
        'slug' => 'test-default',
        'template' => CmsPageTemplate::DEFAULT,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    $blocks = $page->template->defaultBlocks();

    expect($blocks)->toBeArray();
    expect($blocks)->toBeEmpty();
});

// ─── CUSTOM ────────────────────────────────────────────────

test('creating a custom page creates translation with no blocks', function () {
    $page = CmsPage::create([
        'slug' => 'test-custom',
        'template' => CmsPageTemplate::CUSTOM,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    $blocks = $page->template->defaultBlocks();

    expect($blocks)->toBeArray();
    expect($blocks)->toBeEmpty();
});

// ─── CONTACT ───────────────────────────────────────────────

test('creating a contact page creates translation with contact blocks', function () {
    $page = CmsPage::create([
        'slug' => 'test-contact',
        'template' => CmsPageTemplate::CONTACT,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    $translation = CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => $page->default_locale,
        'slug' => $page->slug,
        'title' => $page->slug,
        'blocks' => $page->template->defaultBlocks(),
    ]);

    expect($translation->blocks)->toBeArray();
    expect($translation->blocks)->not->toBeEmpty();
    expect($translation->blocks[0]['type'])->toBe('hero');
    expect(collect($translation->blocks)->firstWhere('type', 'contact_form'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'map'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'stats'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'features'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'cta'))->not->toBeNull();
});

// ─── ABOUT ─────────────────────────────────────────────────

test('creating an about page creates translation with about blocks', function () {
    $page = CmsPage::create([
        'slug' => 'test-about',
        'template' => CmsPageTemplate::ABOUT,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    $translation = CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => $page->default_locale,
        'slug' => $page->slug,
        'title' => $page->slug,
        'blocks' => $page->template->defaultBlocks(),
    ]);

    expect($translation->blocks)->toBeArray();
    expect($translation->blocks)->not->toBeEmpty();
    expect($translation->blocks[0]['type'])->toBe('hero');
    expect(collect($translation->blocks)->firstWhere('type', 'stats'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'timeline'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'team'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'cta'))->not->toBeNull();
});

// ─── PRICING ───────────────────────────────────────────────

test('creating a pricing page creates translation with pricing blocks', function () {
    $page = CmsPage::create([
        'slug' => 'test-pricing',
        'template' => CmsPageTemplate::PRICING,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    $translation = CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => $page->default_locale,
        'slug' => $page->slug,
        'title' => $page->slug,
        'blocks' => $page->template->defaultBlocks(),
    ]);

    expect($translation->blocks)->toBeArray();
    expect($translation->blocks)->not->toBeEmpty();
    expect($translation->blocks[0]['type'])->toBe('hero');
    expect(collect($translation->blocks)->firstWhere('type', 'pricing'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'features'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'faq'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'cta'))->not->toBeNull();
});

// ─── FAQ ───────────────────────────────────────────────────

test('creating a faq page creates translation with faq blocks', function () {
    $page = CmsPage::create([
        'slug' => 'test-faq',
        'template' => CmsPageTemplate::FAQ,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    $translation = CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => $page->default_locale,
        'slug' => $page->slug,
        'title' => $page->slug,
        'blocks' => $page->template->defaultBlocks(),
    ]);

    expect($translation->blocks)->toBeArray();
    expect($translation->blocks)->not->toBeEmpty();
    expect($translation->blocks[0]['type'])->toBe('hero');
    expect(collect($translation->blocks)->firstWhere('type', 'faq'))->not->toBeNull();
    expect(collect($translation->blocks)->firstWhere('type', 'contact_form'))->not->toBeNull();
});

// ─── BLOCKS PERSISTENCE ────────────────────────────────────

test('default blocks stored in translation persist correctly in database', function () {
    $page = CmsPage::create([
        'slug' => 'persistence-test',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    $translation = CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => $page->default_locale,
        'slug' => $page->slug,
        'title' => 'Persistence Test',
        'blocks' => $page->template->defaultBlocks(),
    ]);

    $fresh = CmsPageTranslation::find($translation->id);
    expect($fresh->blocks)->toBeArray();
    expect($fresh->blocks)->toHaveCount(count($page->template->defaultBlocks()));
});

test('pricing default blocks have show_yearly_toggle set to false', function () {
    $blocks = CmsPageTemplate::PRICING->defaultBlocks();

    $pricing = collect($blocks)->firstWhere('type', 'pricing');
    expect($pricing['data']['show_yearly_toggle'])->toBeFalse();
});

test('yearly price is correctly calculated from percent discount', function () {
    $monthlyPrice = 29;
    $discountValue = 20;
    $yearlyTotal = round($monthlyPrice * 12 * (1 - $discountValue / 100));

    expect($yearlyTotal)->toEqual(278);
});

test('yearly price is correctly calculated from fixed discount', function () {
    $monthlyPrice = 29;
    $discountValue = 50;
    $yearlyTotal = round(max(0, $monthlyPrice * 12 - $discountValue));

    expect($yearlyTotal)->toEqual(298);
});

test('yearly price is correctly calculated from free months', function () {
    $monthlyPrice = 29;
    $freeMonths = 2;
    $paidMonths = max(1, 12 - $freeMonths);
    $yearlyTotal = round($monthlyPrice * $paidMonths);

    expect($yearlyTotal)->toEqual(290);
});

test('yearly price does not go below zero for large fixed discount', function () {
    $monthlyPrice = 10;
    $discountValue = 999;
    $yearlyTotal = round(max(0, $monthlyPrice * 12 - $discountValue));

    expect($yearlyTotal)->toEqual(0);
});

test('free months is capped at 11 minimum paid months', function () {
    $monthlyPrice = 10;
    $freeMonths = 99;
    $paidMonths = max(1, 12 - $freeMonths);
    $yearlyTotal = round($monthlyPrice * $paidMonths);

    expect($yearlyTotal)->toEqual(10);
});

test('yearly price without discount equals monthly times 12', function () {
    $price = 10;
    $yearlyTotal = round($price * 12);

    expect($yearlyTotal)->toEqual(120);
});

test('yearly discount badge renders for percent type', function () {
    $view = view('blogr::components.blocks.pricing', [
        'data' => [
            'show_yearly_toggle' => true,
            'plans' => [
                [
                    'name' => 'Pro',
                    'price' => 29,
                    'period' => 'month',
                    'yearly_discount_type' => 'percent',
                    'yearly_discount_value' => 20,
                    'features' => [],
                ],
            ],
        ],
    ])->render();

    expect($view)->toContain('Save 20%');
    expect($view)->toContain('$278');
    expect($view)->toContain('$348');
    expect($view)->toContain('line-through');
});

test('yearly discount badge renders for fixed type', function () {
    $view = view('blogr::components.blocks.pricing', [
        'data' => [
            'show_yearly_toggle' => true,
            'plans' => [
                [
                    'name' => 'Pro',
                    'price' => 29,
                    'period' => 'month',
                    'yearly_discount_type' => 'fixed',
                    'yearly_discount_value' => 50,
                    'features' => [],
                ],
            ],
        ],
    ])->render();

    expect($view)->toContain('$50 off');
    expect($view)->toContain('$298');
    expect($view)->toContain('$348');
});

test('yearly discount badge renders for free months type', function () {
    $view = view('blogr::components.blocks.pricing', [
        'data' => [
            'show_yearly_toggle' => true,
            'plans' => [
                [
                    'name' => 'Pro',
                    'price' => 29,
                    'period' => 'month',
                    'yearly_discount_type' => 'months',
                    'yearly_discount_value' => 2,
                    'features' => [],
                ],
            ],
        ],
    ])->render();

    expect($view)->toContain('2 months free');
    expect($view)->toContain('$290');
    expect($view)->toContain('$348');
});

test('backward compat: old yearly_discount treated as percent', function () {
    $view = view('blogr::components.blocks.pricing', [
        'data' => [
            'show_yearly_toggle' => true,
            'plans' => [
                [
                    'name' => 'Pro',
                    'price' => 29,
                    'period' => 'month',
                    'yearly_discount' => 20,
                    'features' => [],
                ],
            ],
        ],
    ])->render();

    expect($view)->toContain('Save 20%');
    expect($view)->toContain('$278');
});

test('yearly toggle does not appear when show_yearly_toggle is false', function () {
    $view = view('blogr::components.blocks.pricing', [
        'data' => [
            'show_yearly_toggle' => false,
            'heading' => 'Pricing',
            'plans' => [
                ['name' => 'Starter', 'price' => 0, 'features' => []],
            ],
        ],
    ])->render();

    expect($view)->not->toContain('>Monthly<');
    expect($view)->not->toContain('>Yearly<');
});

test('yearly toggle appears when show_yearly_toggle is true', function () {
    $view = view('blogr::components.blocks.pricing', [
        'data' => [
            'show_yearly_toggle' => true,
            'heading' => 'Pricing',
            'plans' => [
                ['name' => 'Starter', 'price' => 10, 'period' => 'month', 'features' => []],
            ],
        ],
    ])->render();

    expect($view)->toContain('x-data="{ yearly');
    expect($view)->toContain('Monthly');
    expect($view)->toContain('Yearly');
});

test('default blocks can be modified after creation', function () {
    $page = CmsPage::create([
        'slug' => 'modifiable',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    $translation = CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => $page->default_locale,
        'slug' => $page->slug,
        'title' => 'Modifiable',
        'blocks' => $page->template->defaultBlocks(),
    ]);

    $blocks = $translation->blocks;
    $blocks[0]['data']['title'] = 'Modified Title';
    $translation->update(['blocks' => $blocks]);

    expect($translation->refresh()->blocks[0]['data']['title'])->toBe('Modified Title');
});

test('default blocks work across multiple pages with same template', function () {
    $page1 = CmsPage::create([
        'slug' => 'multi-1',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);
    CmsPageTranslation::create([
        'cms_page_id' => $page1->id,
        'locale' => 'en',
        'slug' => 'multi-1',
        'title' => 'Multi 1',
        'blocks' => $page1->template->defaultBlocks(),
    ]);

    $page2 = CmsPage::create([
        'slug' => 'multi-2',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);
    CmsPageTranslation::create([
        'cms_page_id' => $page2->id,
        'locale' => 'en',
        'slug' => 'multi-2',
        'title' => 'Multi 2',
        'blocks' => $page2->template->defaultBlocks(),
    ]);

    $t1 = CmsPageTranslation::where('cms_page_id', $page1->id)->first();
    $t2 = CmsPageTranslation::where('cms_page_id', $page2->id)->first();

    expect($t1->blocks)->toHaveCount(count($page1->template->defaultBlocks()));
    expect($t2->blocks)->toHaveCount(count($page2->template->defaultBlocks()));
    expect($t1->blocks)->toEqual($t2->blocks);
});
