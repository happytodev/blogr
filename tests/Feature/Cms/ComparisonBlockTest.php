<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\CmsWithLocalesTestCase;

use function Pest\Laravel\get;

uses(CmsWithLocalesTestCase::class);
uses()->group('cms', 'blocks');

/**
 * A comparison table: what we do against what the others do.
 *
 * The fixtures below use the shapes the block actually carries in production,
 * taken from a live page:
 *
 *   rows[i].cells[j] = { text: '…', sub?: '…' }
 *   ahead.items[i]   = { name: '…', text: '…' }
 *   mobile_tabs      = ['Phage vs Herd', …]     (labels, not a boolean)
 *
 * They were first written with bare strings, which is how a view and a form
 * ended up agreeing with each other and disagreeing with the real data: the
 * test passed, the page broke.
 */
beforeEach(function () {
    $this->page = CmsPage::factory()->homepage()->create(['template' => 'landing']);
});

function comparisonBlock(array $data = []): array
{
    return [
        'type' => 'comparison',
        'data' => array_merge([
            'eyebrow' => 'the honest part',
            'heading' => 'Where the others are ahead',
            'intro' => 'Read this before you install anything.',
            'verdict_label' => 'Verdict',
            'columns' => [
                ['name' => 'Phage', 'highlight' => true, 'badge' => 'you are here', 'tagline' => 'One binary'],
                ['name' => 'Herd', 'highlight' => false, 'badge' => null, 'tagline' => 'macOS only'],
            ],
            'rows' => [
                [
                    'label' => 'Windows support',
                    'cells' => [
                        ['text' => 'yes'],
                        ['text' => 'nope', 'sub' => 'not yet'],
                    ],
                ],
                [
                    'label' => 'Price',
                    'cells' => [['text' => 'free'], ['text' => 'free']],
                ],
            ],
            'ahead' => [
                'title' => 'What they do better',
                'items' => [
                    ['name' => 'Herd', 'text' => 'Native macOS integration'],
                    ['name' => 'Laragon', 'text' => 'Longer track record'],
                ],
            ],
            'closing' => 'We tell you what we lack.',
            'footnote' => 'Checked on every release.',
            'mobile_tabs' => ['Phage vs Herd', 'Phage vs Laragon'],
            'ctas' => [['text' => 'Download', 'url' => 'https://example.test/download', 'style' => 'primary']],
        ], $data),
    ];
}

test('comparison block renders its heading, its columns and its rows', function () {
    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [comparisonBlock()],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
    $response->assertSee('Where the others are ahead');
    $response->assertSee('Read this before you install anything.');
    $response->assertSee('Phage');
    $response->assertSee('Herd');
    $response->assertSee('Windows support');
    $response->assertSee('We tell you what we lack.');
});

test('comparison block renders each cell text and its optional sub note', function () {
    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [comparisonBlock()],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
    $response->assertSee('yes');
    $response->assertSee('nope');
    // `sub` is the smaller line under a cell, and comes from the object, not
    // from the cell's text.
    $response->assertSee('not yet');
});

test('comparison block renders the name and the text of each "ahead" item', function () {
    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [comparisonBlock()],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
    $response->assertSee('What they do better');
    $response->assertSee('Herd');
    $response->assertSee('Native macOS integration');
    $response->assertSee('Laragon');
    $response->assertSee('Longer track record');
});

test('comparison block renders the mobile tab labels', function () {
    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [comparisonBlock()],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
    // mobile_tabs is a list of labels. Reading it as a flag would print nothing.
    $response->assertSee('Phage vs Herd');
    $response->assertSee('Phage vs Laragon');
});

test('comparison block keeps each row aligned with its columns', function () {
    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [comparisonBlock()],
    ]);

    $html = get('/en')->assertStatus(200)->getContent();

    // The first row's cells must appear in the order they were declared, and
    // both must be present: a view that reads a key as a position would keep
    // only one of them.
    expect(strpos($html, 'Windows support'))->toBeLessThan(strpos($html, 'Price'));
    expect(strpos($html, 'yes'))->toBeLessThan(strpos($html, 'nope'));
});

test('comparison block renders a row whatever number of cells it carries', function () {
    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [comparisonBlock([
            'columns' => [
                ['name' => 'Phage', 'highlight' => true],
                ['name' => 'Herd', 'highlight' => false],
                ['name' => 'Laragon', 'highlight' => false],
            ],
            'rows' => [['label' => 'Three way', 'cells' => [
                ['text' => 'a'],
                ['text' => 'b'],
                ['text' => 'c'],
            ]]],
        ])],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
    $response->assertSee('Laragon');
    $response->assertSee('Three way');
    foreach (['a', 'b', 'c'] as $cell) {
        $response->assertSee($cell);
    }
});

test('comparison block still renders a bare string where an object is expected', function () {
    // Older pages, or a hand-written block, can carry a plain string as a cell.
    // It has to render rather than break the page.
    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [comparisonBlock([
            'rows' => [['label' => 'Legacy row', 'cells' => ['plain one', 'plain two']]],
            'ahead' => ['title' => 'Legacy ahead', 'items' => ['a plain item']],
        ])],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
    $response->assertSee('plain one');
    $response->assertSee('plain two');
    $response->assertSee('a plain item');
});

test('comparison block survives uuid keys inside its rows, cells and columns', function () {
    // The content editor stores lists keyed by UUID once a block is edited.
    // Same family as #350, #351 and #353.
    $uuid = 'b840a197-7fe3-46f2-849b-f17d6e9f3d';
    $columns = [
        $uuid.'01' => ['name' => 'Column one'],
        $uuid.'02' => ['name' => 'Column two'],
    ];
    $rows = [
        $uuid.'11' => ['label' => 'Row one', 'cells' => [
            $uuid.'21' => ['text' => 'cell one'],
            $uuid.'22' => ['text' => 'cell two', 'sub' => 'sub two'],
        ]],
        $uuid.'12' => ['label' => 'Row two', 'cells' => [['text' => 'cell three'], ['text' => 'cell four']]],
    ];
    $aheadItems = [
        $uuid.'31' => ['name' => 'Tool one', 'text' => 'ahead one'],
    ];

    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [comparisonBlock([
            'columns' => $columns,
            'rows' => $rows,
            'ahead' => ['title' => 'Ahead', 'items' => $aheadItems],
        ])],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
    $response->assertSee('Column one');
    $response->assertSee('Row two');
    $response->assertSee('cell four');
    $response->assertSee('sub two');
    $response->assertSee('Tool one');
});
