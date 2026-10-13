<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\CmsWithLocalesTestCase;

use function Pest\Laravel\get;

uses(CmsWithLocalesTestCase::class);
uses()->group('cms', 'blocks');

/**
 * A block presenting an image next to text, alternating sides down a page.
 *
 * Issue: #353
 */

beforeEach(function () {
    $this->page = CmsPage::factory()->homepage()->create(['template' => 'landing']);
});

function mediaTextBlock(array $data = []): array
{
    return [
        'type' => 'media_text',
        'data' => array_merge([
            'eyebrow' => 'everywhere',
            'title' => 'Runs *where you do*',
            'subtitle' => 'Native on macOS, Linux and Windows.',
            'content' => 'A paragraph with a **bold** bit.',
            'items' => [
                ['title' => 'macOS and Linux, first class', 'text' => 'Native builds.'],
                ['title' => 'Windows without WSL', 'text' => 'A real Windows build.'],
            ],
            'image' => 'cms-blocks/phage/dashboard.png',
            'image_alt' => 'The app at a glance',
            'image_caption' => 'the native app',
            'side' => 'right',
        ], $data),
    ];
}

test('media text block renders its text, its list and its image', function () {
    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [mediaTextBlock()],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
    $response->assertSee('Runs');
    $response->assertSee('where you do');          // the emphasis marker, not the raw asterisks
    $response->assertSee('Native on macOS, Linux and Windows.');
    $response->assertSee('Windows without WSL');
    $response->assertSee('cms-blocks/phage/dashboard.png');
    $response->assertSee('the native app');
});

test('media text block renders rich text as html, not escaped', function () {
    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [mediaTextBlock()],
    ]);

    $html = get('/en')->assertStatus(200)->getContent();

    expect($html)->toContain('<strong>bold</strong>');
    expect($html)->not->toContain('&lt;strong&gt;bold&lt;/strong&gt;');
});

test('media text block honours the side field', function () {
    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [
            mediaTextBlock(['side' => 'right']),
            mediaTextBlock(['side' => 'left', 'title' => 'Second pillar']),
        ],
    ]);

    $html = get('/en')->assertStatus(200)->getContent();

    // Two blocks, one with the image on the right and one on the left. Each must
    // contribute BOTH orders — the text column on one side, the image on the
    // other. If `side` were ignored, we would get four of the same.
    expect(substr_count($html, 'lg:order-1'))->toBeGreaterThanOrEqual(2);
    expect(substr_count($html, 'lg:order-2'))->toBeGreaterThanOrEqual(2);
});

test('media text block survives uuid keys inside its items', function () {
    // The content editor stores lists keyed by UUID once a block is edited. Same
    // family as #350 and #351: a view that reads the key as a position breaks on
    // real data while a freshly seeded test page stays green.
    $items = [];
    foreach (['a', 'b'] as $i => $label) {
        $items['b840a197-7fe3-46f2-849b-f17d6e9f3d1'.$i] = [
            'title' => 'Item '.$label,
            'text' => 'Text '.$label,
        ];
    }

    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [mediaTextBlock(['items' => $items])],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
    $response->assertSee('Item a');
    $response->assertSee('Item b');
});
