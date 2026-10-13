<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\CmsWithLocalesTestCase;

use function Pest\Laravel\get;

uses(CmsWithLocalesTestCase::class);
uses()->group('cms', 'blocks');

/**
 * A comparison table: what we do against what the others do.
 *
 * Unlike a pricing table, the rows are not parallel lists of the same shape:
 * each row carries a label and a variable number of cells, one per column.
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
                ['label' => 'Windows support', 'cells' => ['yes', 'no']],
                ['label' => 'Price', 'cells' => ['free', 'free']],
            ],
            'ahead' => ['title' => 'What they do better', 'items' => ['Native macOS integration', 'Longer track record']],
            'closing' => 'We tell you what we lack.',
            'footnote' => 'Checked on every release.',
            'mobile_tabs' => false,
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
    $response->assertSee('Longer track record');
    $response->assertSee('We tell you what we lack.');
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
    // both cells must be present: a view that reads a key as a position would
    // keep only one of them.
    expect($html)->toContain('yes');
    expect($html)->toContain('no');
    expect(strpos($html, 'Windows support'))->toBeLessThan(strpos($html, 'Price'));
});

test('comparison block renders the rows it is given, however many cells they carry', function () {
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
            'rows' => [['label' => 'Three way', 'cells' => ['a', 'b', 'c']]],
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

test('comparison block survives uuid keys inside its rows and columns', function () {
    // The content editor stores lists keyed by UUID once a block is edited.
    // Same family as #350, #351 and #353.
    $uuid = 'b840a197-7fe3-46f2-849b-f17d6e9f3d';
    $columns = [
        $uuid.'01' => ['name' => 'Column one'],
        $uuid.'02' => ['name' => 'Column two'],
    ];
    $rows = [
        $uuid.'11' => ['label' => 'Row one', 'cells' => [$uuid.'21' => 'cell one', $uuid.'22' => 'cell two']],
        $uuid.'12' => ['label' => 'Row two', 'cells' => ['cell three', 'cell four']],
    ];

    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [comparisonBlock(['columns' => $columns, 'rows' => $rows])],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
    $response->assertSee('Column one');
    $response->assertSee('Row two');
    $response->assertSee('cell four');
});
