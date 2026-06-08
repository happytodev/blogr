<?php

use Happytodev\Blogr\Enums\CmsBlockType;
use Happytodev\Blogr\Enums\CmsPageTemplate;

// ─── LANDING ───────────────────────────────────────────────

test('landing template default blocks contain hero block', function () {
    $blocks = CmsPageTemplate::LANDING->defaultBlocks();

    expect($blocks)->toBeArray();
    expect($blocks[0]['type'])->toBe('hero');
    expect($blocks[0]['data']['title'])->not->toBeEmpty();
    expect($blocks[0]['data']['subtitle'])->not->toBeEmpty();
    expect($blocks[0]['data']['cta_text'])->not->toBeEmpty();
});

test('landing template default blocks contain features block with 6 items', function () {
    $blocks = CmsPageTemplate::LANDING->defaultBlocks();

    $features = collect($blocks)->firstWhere('type', 'features');
    expect($features)->not->toBeNull();
    expect($features['data']['items'])->toHaveCount(6);
});

test('landing template default blocks contain stats block', function () {
    $blocks = CmsPageTemplate::LANDING->defaultBlocks();

    $stats = collect($blocks)->firstWhere('type', 'stats');
    expect($stats)->not->toBeNull();
    expect($stats['data']['stats'])->toHaveCount(4);
});

test('landing template default blocks contain blog_posts block', function () {
    $blocks = CmsPageTemplate::LANDING->defaultBlocks();

    expect(collect($blocks)->firstWhere('type', 'blog_posts'))->not->toBeNull();
});

test('landing template default blocks contain timeline block with 4 events', function () {
    $blocks = CmsPageTemplate::LANDING->defaultBlocks();

    $timeline = collect($blocks)->firstWhere('type', 'timeline');
    expect($timeline)->not->toBeNull();
    expect($timeline['data']['events'])->toHaveCount(4);
});

test('landing template default blocks contain cta block', function () {
    $blocks = CmsPageTemplate::LANDING->defaultBlocks();

    expect(collect($blocks)->firstWhere('type', 'cta'))->not->toBeNull();
});

test('landing template default blocks have exactly 6 blocks', function () {
    $blocks = CmsPageTemplate::LANDING->defaultBlocks();

    expect($blocks)->toHaveCount(6);
});

// ─── CONTACT ───────────────────────────────────────────────

test('contact template default blocks contain hero block', function () {
    $blocks = CmsPageTemplate::CONTACT->defaultBlocks();

    expect($blocks[0]['type'])->toBe('hero');
    expect($blocks[0]['data']['title'])->not->toBeEmpty();
});

test('contact template default blocks contain contact_form block', function () {
    $blocks = CmsPageTemplate::CONTACT->defaultBlocks();

    expect(collect($blocks)->firstWhere('type', 'contact_form'))->not->toBeNull();
});

test('contact template default blocks contain map block', function () {
    $blocks = CmsPageTemplate::CONTACT->defaultBlocks();

    expect(collect($blocks)->firstWhere('type', 'map'))->not->toBeNull();
});

test('contact template default blocks contain stats block', function () {
    $blocks = CmsPageTemplate::CONTACT->defaultBlocks();

    expect(collect($blocks)->firstWhere('type', 'stats'))->not->toBeNull();
});

test('contact template default blocks contain features block with contact info', function () {
    $blocks = CmsPageTemplate::CONTACT->defaultBlocks();

    $features = collect($blocks)->firstWhere('type', 'features');
    expect($features)->not->toBeNull();
    expect($features['data']['items'])->toHaveCount(3);
});

test('contact template default blocks contain cta block', function () {
    $blocks = CmsPageTemplate::CONTACT->defaultBlocks();

    expect(collect($blocks)->firstWhere('type', 'cta'))->not->toBeNull();
});

test('contact template default blocks have exactly 6 blocks', function () {
    $blocks = CmsPageTemplate::CONTACT->defaultBlocks();

    expect($blocks)->toHaveCount(6);
});

// ─── ABOUT ─────────────────────────────────────────────────

test('about template default blocks contain hero block', function () {
    $blocks = CmsPageTemplate::ABOUT->defaultBlocks();

    expect($blocks[0]['type'])->toBe('hero');
});

test('about template default blocks contain stats block', function () {
    $blocks = CmsPageTemplate::ABOUT->defaultBlocks();

    expect(collect($blocks)->firstWhere('type', 'stats'))->not->toBeNull();
});

test('about template default blocks contain timeline block', function () {
    $blocks = CmsPageTemplate::ABOUT->defaultBlocks();

    expect(collect($blocks)->firstWhere('type', 'timeline'))->not->toBeNull();
});

test('about template default blocks contain team block with 4 members', function () {
    $blocks = CmsPageTemplate::ABOUT->defaultBlocks();

    $team = collect($blocks)->firstWhere('type', 'team');
    expect($team)->not->toBeNull();
    expect($team['data']['members'])->toHaveCount(4);
});

test('about template default blocks contain cta block', function () {
    $blocks = CmsPageTemplate::ABOUT->defaultBlocks();

    expect(collect($blocks)->firstWhere('type', 'cta'))->not->toBeNull();
});

test('about template default blocks have exactly 5 blocks', function () {
    $blocks = CmsPageTemplate::ABOUT->defaultBlocks();

    expect($blocks)->toHaveCount(5);
});

// ─── PRICING ───────────────────────────────────────────────

test('pricing template default blocks contain hero block', function () {
    $blocks = CmsPageTemplate::PRICING->defaultBlocks();

    expect($blocks[0]['type'])->toBe('hero');
});

test('pricing template default blocks have columns defaulting to 3', function () {
    $blocks = CmsPageTemplate::PRICING->defaultBlocks();

    $pricing = collect($blocks)->firstWhere('type', 'pricing');
    expect($pricing['data']['columns'])->toBe('3');
});

test('pricing template default blocks have show_yearly_toggle defaulting to false', function () {
    $blocks = CmsPageTemplate::PRICING->defaultBlocks();

    $pricing = collect($blocks)->firstWhere('type', 'pricing');
    expect($pricing['data']['show_yearly_toggle'])->toBeFalse();
});

test('pricing template default blocks contain pricing block with 3 plans', function () {
    $blocks = CmsPageTemplate::PRICING->defaultBlocks();

    $pricing = collect($blocks)->firstWhere('type', 'pricing');
    expect($pricing)->not->toBeNull();
    expect($pricing['data']['plans'])->toHaveCount(3);
    $planNames = collect($pricing['data']['plans'])->pluck('name')->toArray();
    expect($planNames)->toContain('Starter');
    expect($planNames)->toContain('Pro');
    expect($planNames)->toContain('Enterprise');
});

test('pricing template default blocks contain features comparative block', function () {
    $blocks = CmsPageTemplate::PRICING->defaultBlocks();

    expect(collect($blocks)->firstWhere('type', 'features'))->not->toBeNull();
});

test('pricing template default blocks contain faq block with questions', function () {
    $blocks = CmsPageTemplate::PRICING->defaultBlocks();

    $faq = collect($blocks)->firstWhere('type', 'faq');
    expect($faq)->not->toBeNull();
    expect($faq['data']['items'])->toHaveCount(4);
});

test('pricing template default blocks contain cta block', function () {
    $blocks = CmsPageTemplate::PRICING->defaultBlocks();

    expect(collect($blocks)->firstWhere('type', 'cta'))->not->toBeNull();
});

test('pricing template default blocks have exactly 5 blocks', function () {
    $blocks = CmsPageTemplate::PRICING->defaultBlocks();

    expect($blocks)->toHaveCount(5);
});

// ─── FAQ ───────────────────────────────────────────────────

test('faq template default blocks contain hero block', function () {
    $blocks = CmsPageTemplate::FAQ->defaultBlocks();

    expect($blocks[0]['type'])->toBe('hero');
});

test('faq template default blocks contain faq block with 5 questions', function () {
    $blocks = CmsPageTemplate::FAQ->defaultBlocks();

    $faq = collect($blocks)->firstWhere('type', 'faq');
    expect($faq)->not->toBeNull();
    expect($faq['data']['items'])->toHaveCount(5);
});

test('faq template default blocks contain contact_form block', function () {
    $blocks = CmsPageTemplate::FAQ->defaultBlocks();

    expect(collect($blocks)->firstWhere('type', 'contact_form'))->not->toBeNull();
});

test('faq template default blocks have exactly 3 blocks', function () {
    $blocks = CmsPageTemplate::FAQ->defaultBlocks();

    expect($blocks)->toHaveCount(3);
});

// ─── DEFAULT & CUSTOM ──────────────────────────────────────

test('default template default blocks is empty', function () {
    $blocks = CmsPageTemplate::DEFAULT->defaultBlocks();

    expect($blocks)->toBeArray();
    expect($blocks)->toBeEmpty();
});

test('custom template default blocks is empty', function () {
    $blocks = CmsPageTemplate::CUSTOM->defaultBlocks();

    expect($blocks)->toBeArray();
    expect($blocks)->toBeEmpty();
});

// ─── STRUCTURAL VALIDATION ─────────────────────────────────

test('all template default blocks have valid structure', function () {
    $templates = CmsPageTemplate::cases();

    foreach ($templates as $template) {
        $blocks = $template->defaultBlocks();

        expect($blocks)->toBeArray('Template '.$template->value.' must return an array');

        foreach ($blocks as $i => $block) {
            expect($block)->toHaveKey('type');
            expect($block)->toHaveKey('data');
            expect($block['type'])->toBeString();
            expect($block['data'])->toBeArray();
        }
    }
});

test('every default block type references a valid CmsBlockType', function () {
    $validTypes = collect(CmsBlockType::cases())
        ->map(fn ($case) => $case->value)
        ->toArray();

    $templates = CmsPageTemplate::cases();

    foreach ($templates as $template) {
        $blocks = $template->defaultBlocks();

        foreach ($blocks as $block) {
            expect(in_array($block['type'], $validTypes))
                ->toBeTrue("{$block['type']} is not a valid CmsBlockType (used in {$template->value})");
        }
    }
});

test('all default blocks have background colors for both light and dark mode', function () {
    $templates = [CmsPageTemplate::LANDING, CmsPageTemplate::CONTACT, CmsPageTemplate::ABOUT, CmsPageTemplate::PRICING, CmsPageTemplate::FAQ];

    foreach ($templates as $template) {
        $blocks = $template->defaultBlocks();

        foreach ($blocks as $block) {
            if (! isset($block['data']['background_type'])) {
                continue; // skip blocks without background (e.g. blog_posts might not have one)
            }

            expect($block['data'])
                ->toHaveKey('background_type')
                ->toHaveKey('background_type_dark');
        }
    }
});
