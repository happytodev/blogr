<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Enums\CmsPageTemplate;
use Happytodev\Blogr\Tests\CmsTestCase;

uses(CmsTestCase::class);

test('blocks are stored in translation not in page', function () {
    $page = CmsPage::factory()->create([
        'slug' => 'test-page',
        'template' => CmsPageTemplate::LANDING,
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => 'Test content',
        'blocks' => json_encode([
            ['type' => 'hero', 'data' => ['title' => 'Hero Title']],
        ]),
    ]);

    // Blocks should be in translation
    expect($translation->blocks)->toBeJson();
    
    // Blocks should NOT be in page (or should be null)
    expect($page->blocks ?? null)->toBeNull();
});

test('each translation can have different blocks', function () {
    $page = CmsPage::factory()->create([
        'slug' => 'multilingual-page',
        'template' => CmsPageTemplate::LANDING,
    ]);

    $enTranslation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'English Page',
        'slug' => 'english-page',
        'content' => 'English content',
        'blocks' => json_encode([
            ['type' => 'hero', 'data' => ['title' => 'English Hero']],
        ]),
    ]);

    $frTranslation = $page->translations()->create([
        'locale' => 'fr',
        'title' => 'Page Française',
        'slug' => 'page-francaise',
        'content' => 'Contenu français',
        'blocks' => json_encode([
            ['type' => 'hero', 'data' => ['title' => 'Héros Français']],
            ['type' => 'cta', 'data' => ['heading' => 'Appel à action']],
        ]),
    ]);

    $enBlocks = json_decode($enTranslation->blocks, true);
    $frBlocks = json_decode($frTranslation->blocks, true);

    expect($enBlocks)->toHaveCount(1);
    expect($frBlocks)->toHaveCount(2);
    expect($enBlocks[0]['data']['title'])->toBe('English Hero');
    expect($frBlocks[0]['data']['title'])->toBe('Héros Français');
});

test('translation blocks can be null', function () {
    $page = CmsPage::factory()->create();
    
    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Page Without Blocks',
        'slug' => 'no-blocks',
        'content' => 'Content',
        'blocks' => null,
    ]);

    expect($translation->blocks)->toBeNull();
});

test('translation blocks can be empty array', function () {
    $page = CmsPage::factory()->create();
    
    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Page With Empty Blocks',
        'slug' => 'empty-blocks',
        'content' => 'Content',
        'blocks' => json_encode([]),
    ]);

    $blocks = json_decode($translation->blocks, true);
    expect($blocks)->toBeArray();
    expect($blocks)->toBeEmpty();
});

test('can update translation blocks independently', function () {
    $page = CmsPage::factory()->create();
    
    $enTranslation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'English',
        'slug' => 'english',
        'content' => 'Content',
        'blocks' => json_encode([
            ['type' => 'hero', 'data' => ['title' => 'Old Hero']],
        ]),
    ]);

    $frTranslation = $page->translations()->create([
        'locale' => 'fr',
        'title' => 'Français',
        'slug' => 'francais',
        'content' => 'Contenu',
        'blocks' => json_encode([
            ['type' => 'hero', 'data' => ['title' => 'Ancien Héros']],
        ]),
    ]);

    // Update English blocks
    $enTranslation->update([
        'blocks' => json_encode([
            ['type' => 'hero', 'data' => ['title' => 'New Hero']],
        ]),
    ]);

    $enTranslation->refresh();
    $frTranslation->refresh();

    $enBlocks = json_decode($enTranslation->blocks, true);
    $frBlocks = json_decode($frTranslation->blocks, true);

    expect($enBlocks[0]['data']['title'])->toBe('New Hero');
    expect($frBlocks[0]['data']['title'])->toBe('Ancien Héros'); // Unchanged
});

test('blocks json structure is valid in translation', function () {
    $page = CmsPage::factory()->create();
    
    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Test',
        'slug' => 'test',
        'content' => 'Content',
        'blocks' => json_encode([
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Features',
                    'items' => [
                        ['title' => 'Feature 1', 'description' => 'Desc 1'],
                    ],
                ],
            ],
        ]),
    ]);

    $blocks = json_decode($translation->blocks, true);
    
    expect($blocks)->toBeArray();
    expect($blocks[0])->toHaveKey('type');
    expect($blocks[0])->toHaveKey('data');
    expect($blocks[0]['data'])->toBeArray();
});

test('can delete translation with blocks', function () {
    $page = CmsPage::factory()->create();
    
    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Test',
        'slug' => 'test',
        'content' => 'Content',
        'blocks' => json_encode([
            ['type' => 'hero', 'data' => ['title' => 'Test']],
        ]),
    ]);

    $id = $translation->id;
    $translation->delete();

    expect(CmsPageTranslation::find($id))->toBeNull();
});

test('blocks are preserved when updating other translation fields', function () {
    $page = CmsPage::factory()->create();
    
    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Original Title',
        'slug' => 'original-slug',
        'content' => 'Original content',
        'blocks' => json_encode([
            ['type' => 'hero', 'data' => ['title' => 'Hero Title']],
        ]),
    ]);

    $originalBlocks = $translation->blocks;

    // Update title only
    $translation->update([
        'title' => 'Updated Title',
    ]);

    $translation->refresh();

    expect($translation->title)->toBe('Updated Title');
    expect($translation->blocks)->toBe($originalBlocks);
});

test('can create multiple blocks in translation', function () {
    $page = CmsPage::factory()->create();
    
    $blocks = [
        ['type' => 'hero', 'data' => ['title' => 'Welcome']],
        ['type' => 'features', 'data' => ['title' => 'Features', 'items' => []]],
        ['type' => 'testimonials', 'data' => ['title' => 'Reviews', 'items' => []]],
        ['type' => 'cta', 'data' => ['heading' => 'Get Started']],
        ['type' => 'faq', 'data' => ['title' => 'FAQ', 'items' => []]],
        ['type' => 'content', 'data' => ['content' => '## Rich Content']],
    ];

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Full Page',
        'slug' => 'full-page',
        'content' => 'Content',
        'blocks' => json_encode($blocks),
    ]);

    $decodedBlocks = json_decode($translation->blocks, true);
    
    expect($decodedBlocks)->toHaveCount(6);
    expect($decodedBlocks[0]['type'])->toBe('hero');
    expect($decodedBlocks[5]['type'])->toBe('content');
});
