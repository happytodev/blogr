<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\CmsWithLocalesTestCase;

use function Pest\Laravel\get;

uses(CmsWithLocalesTestCase::class);
uses()->group('cms', 'blocks');

/**
 * An installation section: numbered steps, a command per platform, and a
 * download call to action.
 */
beforeEach(function () {
    $this->page = CmsPage::factory()->homepage()->create(['template' => 'landing']);
});

function installBlock(array $data = []): array
{
    return [
        'type' => 'install',
        'data' => array_merge([
            'eyebrow' => 'two commands',
            'heading' => 'Install it once',
            'steps' => [
                ['cmd' => 'curl -fsSL https://example.test/install.sh | sh', 'note' => 'Puts the binary in your PATH.'],
                ['cmd' => 'phage scan', 'note' => 'Finds the projects on your machine.'],
            ],
            'rows' => [
                ['os' => 'macOS', 'cmd' => 'brew install phage', 'hint' => 'Apple Silicon and Intel'],
                ['os' => 'Windows', 'cmd' => 'winget install phage', 'hint' => 'PowerShell'],
            ],
            'copy' => 'Copy',
            'copied' => 'Copied',
            'download_label' => 'Download',
            'download_text' => 'No account, no email.',
            'download_url' => 'https://example.test/phage',
            'after' => 'Then run phage serve.',
        ], $data),
    ];
}

test('install block renders its steps, its platforms and its download', function () {
    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [installBlock()],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
    $response->assertSee('Install it once');
    $response->assertSee('Puts the binary in your PATH.');
    $response->assertSee('brew install phage');
    $response->assertSee('Apple Silicon and Intel');
    $response->assertSee('No account, no email.');
    $response->assertSee('https://example.test/phage');
    $response->assertSee('Then run phage serve.');
});

test('install block gives its steps a copy action', function () {
    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [installBlock()],
    ]);

    $html = get('/en')->assertStatus(200)->getContent();

    // The command has to sit in the copyable element, and the button labels come
    // from the block itself rather than being hardcoded in the view.
    expect($html)->toContain('curl -fsSL https://example.test/install.sh | sh');
    expect($html)->toContain('Copied');
    expect($html)->toContain('clipboard');
});

test('install block survives uuid keys inside its steps and rows', function () {
    // Same family as #350, #351 and #353: lists keyed by UUID once a block has
    // been edited in the admin.
    $uuid = 'b840a197-7fe3-46f2-849b-f17d6e9f3d';
    $steps = [
        $uuid.'01' => ['cmd' => 'first command', 'note' => 'first note'],
        $uuid.'02' => ['cmd' => 'second command', 'note' => 'second note'],
    ];
    $rows = [
        $uuid.'11' => ['os' => 'Linux', 'cmd' => 'apt install phage', 'hint' => 'Debian and Ubuntu'],
    ];

    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [installBlock(['steps' => $steps, 'rows' => $rows])],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
    $response->assertSee('second command');
    $response->assertSee('second note');
    $response->assertSee('apt install phage');
});

test('install block keeps working without an image or a download link', function () {
    // Both are optional: a page may only want to show the commands.
    $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [installBlock(['download_url' => null, 'download_text' => null, 'download_label' => null])],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
    $response->assertSee('Install it once');
    $response->assertSee('phage scan');
});
