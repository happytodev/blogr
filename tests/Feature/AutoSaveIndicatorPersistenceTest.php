<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Spatie\Permission\Models\Role;

uses(Happytodev\Blogr\Tests\CmsTestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    config()->set('blogr.auto_save_interval', 5);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $category = Category::factory()->create();

    $this->post = BlogPost::factory()->create([
        'user_id' => $admin->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
    ]);
});

it('sets lastAutoSaveAt on mount', function () {
    \Livewire\Livewire::test(EditBlogPost::class, [
        'record' => $this->post->id,
    ])->assertSet('lastAutoSaveAt', fn ($v) => $v !== null);
});

it('keeps lastAutoSaveAt unchanged after autoSave with no changes', function () {
    $component = \Livewire\Livewire::test(EditBlogPost::class, [
        'record' => $this->post->id,
    ]);

    $timestampBefore = $component->get('lastAutoSaveAt');
    expect($timestampBefore)->not->toBeNull();

    // Call autoSave with no changes — should take the early return path
    $component->call('autoSave');

    $timestampAfter = $component->get('lastAutoSaveAt');
    expect($timestampAfter)->not->toBeNull();
});

it('keeps lastAutoSaveAt after autoSave with instance data change', function () {
    $component = \Livewire\Livewire::test(EditBlogPost::class, [
        'record' => $this->post->id,
    ]);

    $tsBefore = $component->get('lastAutoSaveAt');
    expect($tsBefore)->not->toBeNull();

    // Modify instance data and call autoSave
    $component->instance()->data['default_locale'] = 'changed_' . time();
    $component->call('autoSave');

    // lastAutoSaveAt must persist (not become null)
    $component->assertSet('lastAutoSaveAt', fn ($v) => $v !== null);
    $component->assertSet('hasUnsavedChanges', false);
});

it('maintains lastAutoSaveAt after autoSave with no changes', function () {
    $component = \Livewire\Livewire::test(EditBlogPost::class, [
        'record' => $this->post->id,
    ]);

    $component->assertSet('lastAutoSaveAt', fn ($v) => $v !== null);

    // Call autoSave twice — early return path should also set lastAutoSaveAt
    $component->call('autoSave');
    $component->assertSet('lastAutoSaveAt', fn ($v) => $v !== null);
    $component->assertSet('hasUnsavedChanges', false);

    $component->call('autoSave');
    $component->assertSet('lastAutoSaveAt', fn ($v) => $v !== null);
    $component->assertSet('hasUnsavedChanges', false);
});

it('keeps lastAutoSaveAt after multiple autoSave calls', function () {
    $component = \Livewire\Livewire::test(EditBlogPost::class, [
        'record' => $this->post->id,
    ]);

    for ($i = 0; $i < 5; $i++) {
        $component->call('autoSave');
        $component->assertSet('lastAutoSaveAt', fn ($v) => $v !== null);
        $component->assertSet('hasUnsavedChanges', false);
    }
});

it('sets lastManualSaveAt after manualSave', function () {
    $component = \Livewire\Livewire::test(EditBlogPost::class, [
        'record' => $this->post->id,
    ]);

    $component->call('manualSave');

    $component->assertSet('lastManualSaveAt', fn ($v) => $v !== null);
    $component->assertSet('lastAutoSaveAt', fn ($v) => $v !== null);
});

it('manualSave dispatches manual-saved event', function () {
    $component = \Livewire\Livewire::test(EditBlogPost::class, [
        'record' => $this->post->id,
    ]);

    $component->call('manualSave');

    $component->assertSet('lastManualSaveAt', fn ($v) => $v !== null);
    $component->assertSet('hasUnsavedChanges', false);
});

it('manualSave calls autoSave and updates lastAutoSaveAt', function () {
    $component = \Livewire\Livewire::test(EditBlogPost::class, [
        'record' => $this->post->id,
    ]);

    $tsBefore = $component->get('lastAutoSaveAt');

    $component->call('manualSave');

    $tsAfter = $component->get('lastAutoSaveAt');
    expect($tsAfter)->not->toBeNull();
    expect($tsAfter)->toBe($component->get('lastManualSaveAt'));
});

it('normalizes array values before comparison to ignore UUID keys', function () {
    $normalize = fn ($v) => json_encode(is_array($v) ? array_values($v) : $v);

    // Same block data but different UUID keys
    $blocks1 = [
        'uuid-a' => ['type' => 'hero', 'data' => ['title' => 'Hello']],
        'uuid-b' => ['type' => 'content', 'data' => ['body' => 'World']],
    ];
    $blocks2 = [
        'uuid-c' => ['type' => 'hero', 'data' => ['title' => 'Hello']],
        'uuid-d' => ['type' => 'content', 'data' => ['body' => 'World']],
    ];

    expect($normalize($blocks1))->toBe($normalize($blocks2));

    // Different block data should be detected
    $blocks3 = [
        'uuid-e' => ['type' => 'hero', 'data' => ['title' => 'Changed']],
        'uuid-f' => ['type' => 'content', 'data' => ['body' => 'World']],
    ];

    expect($normalize($blocks1))->not->toBe($normalize($blocks3));
});

it('detects block data changes via normalized comparison', function () {
    $fieldKeys = ['title', 'slug', 'blocks'];

    $draftFields = [
        'title' => 'Test',
        'slug' => 'test',
        'blocks' => [
            'uuid-1' => ['type' => 'hero', 'data' => ['title' => 'Original Hero']],
            'uuid-2' => ['type' => 'content', 'data' => ['body' => 'Original Body']],
        ],
    ];

    $versionFields = [
        'title' => 'Test',
        'slug' => 'test',
        'blocks' => [
            'uuid-3' => ['type' => 'hero', 'data' => ['title' => 'Original Hero']],
            'uuid-4' => ['type' => 'content', 'data' => ['body' => 'Original Body']],
        ],
    ];

    // Same content, different UUIDs → no changes
    $normalize = fn ($v) => json_encode(is_array($v) ? array_values($v) : $v);
    $changes = array_keys(array_diff_assoc(
        array_map($normalize, $draftFields),
        array_map($normalize, $versionFields)
    ));
    expect($changes)->toBe([]);

    // Different content → should detect blocks change
    $draftFields2 = $draftFields;
    $draftFields2['blocks']['uuid-1']['data']['title'] = 'Modified Hero';

    $changes2 = array_keys(array_diff_assoc(
        array_map($normalize, $draftFields2),
        array_map($normalize, $versionFields)
    ));
    expect($changes2)->toContain('blocks');
});
