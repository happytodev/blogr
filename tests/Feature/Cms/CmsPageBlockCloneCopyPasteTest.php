<?php

use Happytodev\Blogr\Filament\Resources\CmsPageResource\Pages\EditCmsPageTranslation;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\CmsTestCase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(CmsTestCase::class)->group('cms');

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole($adminRole);
    $this->actingAs($this->admin);

    $this->page = CmsPage::factory()->create([
        'slug' => 'clone-test',
        'default_locale' => 'en',
    ]);

    $this->translation = CmsPageTranslation::create([
        'cms_page_id' => $this->page->id,
        'locale' => 'en',
        'title' => 'Clone Test',
        'slug' => 'clone-test',
        'blocks' => [
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Hero Title',
                    'cta_link_type' => 'blog',
                    'alignment' => 'center',
                    'text_shadow' => false,
                ],
            ],
        ],
    ]);
});

test('the block builder form has cloneable enabled', function () {
    Livewire::test(EditCmsPageTranslation::class, [
        'record' => $this->page->id,
        'translation' => $this->translation->id,
    ])
        ->assertFormComponentActionExists('blocks', 'clone');
});

test('the block builder has the copyBlock extra item action', function () {
    Livewire::test(EditCmsPageTranslation::class, [
        'record' => $this->page->id,
        'translation' => $this->translation->id,
    ])
        ->assertFormComponentActionExists('blocks', 'copyBlock');
});

test('the copy block action stores block data in session', function () {
    $test = Livewire::test(EditCmsPageTranslation::class, [
        'record' => $this->page->id,
        'translation' => $this->translation->id,
    ]);

    // Get the first block UUID key from component state
    $blocks = $test->get('data.blocks');
    $firstKey = array_keys($blocks)[0];

    $test->callFormComponentAction('blocks', 'copyBlock', arguments: ['item' => $firstKey]);

    expect(session()->has('blogr_cms_block_clipboard'))->toBeTrue();

    $clipboard = session()->get('blogr_cms_block_clipboard');
    expect($clipboard)->toBeArray();
    expect($clipboard)->toHaveKey('type');
    expect($clipboard)->toHaveKey('data');
    expect($clipboard['type'])->toBe('hero');
    expect($clipboard['data'])->toHaveKey('title');
    expect($clipboard['data']['title'])->toBe('Hero Title');
});

test('the paste block header action is visible when clipboard has data', function () {
    session()->put('blogr_cms_block_clipboard', [
        'type' => 'hero',
        'data' => ['title' => 'Copied Hero'],
    ]);

    Livewire::test(EditCmsPageTranslation::class, [
        'record' => $this->page->id,
        'translation' => $this->translation->id,
    ])
        ->assertActionExists('pasteBlock');
});

test('the paste block header action adds a block from the clipboard', function () {
    // Use complete block data (as would be produced by copyBlock) to avoid render errors
    session()->put('blogr_cms_block_clipboard', [
        'type' => 'hero',
        'data' => [
            'title' => 'Pasted Hero',
            'cta_link_type' => 'blog',
            'alignment' => 'center',
            'text_shadow' => false,
        ],
    ]);

    $test = Livewire::test(EditCmsPageTranslation::class, [
        'record' => $this->page->id,
        'translation' => $this->translation->id,
    ]);

    $test->assertSet('data.blocks', fn ($blocks) => is_array($blocks) && count($blocks) === 1);

    $test->callAction('pasteBlock')
        ->assertHasNoErrors();

    $blocks = $test->get('data.blocks');
    expect(count($blocks))->toBe(2);
    $values = array_values($blocks);
    expect($values[1]['type'])->toBe('hero');
    expect($values[1]['data']['title'])->toBe('Pasted Hero');
});

test('the pasted block gets a UUID key, not a numeric key', function () {
    session()->put('blogr_cms_block_clipboard', [
        'type' => 'hero',
        'data' => [
            'title' => 'UUID Key Test',
            'cta_link_type' => 'blog',
            'alignment' => 'center',
            'text_shadow' => false,
        ],
    ]);

    $test = Livewire::test(EditCmsPageTranslation::class, [
        'record' => $this->page->id,
        'translation' => $this->translation->id,
    ]);

    $test->callAction('pasteBlock');

    $blocks = $test->get('data.blocks');
    expect(count($blocks))->toBe(2);

    // All keys must be non-numeric strings (UUIDs)
    foreach (array_keys($blocks) as $key) {
        expect(is_numeric($key))->toBeFalse("%s: a block has a numeric key ($key) which would crash blocks-renderer");
    }
});

test('the paste block header action is hidden when clipboard is empty', function () {
    Livewire::test(EditCmsPageTranslation::class, [
        'record' => $this->page->id,
        'translation' => $this->translation->id,
    ])
        ->assertActionHidden('pasteBlock');
});

test('the paste block header action is hidden after clipboard is cleared on successful paste', function () {
    session()->put('blogr_cms_block_clipboard', [
        'type' => 'hero',
        'data' => [
            'title' => 'Copied',
            'cta_link_type' => 'blog',
            'alignment' => 'center',
            'text_shadow' => false,
        ],
    ]);

    Livewire::test(EditCmsPageTranslation::class, [
        'record' => $this->page->id,
        'translation' => $this->translation->id,
    ])
        ->assertActionExists('pasteBlock')
        ->callAction('pasteBlock')
        ->assertActionHidden('pasteBlock');

    expect(session()->has('blogr_cms_block_clipboard'))->toBeFalse();
});

test('blocks-renderer does not crash with mixed UUID and numeric keys', function () {
    $blocks = [
        'abc-123' => ['type' => 'hero', 'data' => ['title' => 'First']],
        'def-456' => ['type' => 'features', 'data' => ['title' => 'Second', 'items' => []]],
        0 => ['type' => 'cta', 'data' => ['heading' => 'Third']],
    ];

    $html = view('blogr::components.blocks-renderer', ['blocks' => $blocks])->render();

    expect($html)->toContain('First');
    expect($html)->toContain('Second');
    expect($html)->toContain('Third');
});
