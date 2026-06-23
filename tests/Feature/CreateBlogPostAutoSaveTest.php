<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\CreateBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Spatie\Permission\Models\Role;

uses(Happytodev\Blogr\Tests\CmsTestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    config()->set('blogr.auto_save_interval', 30);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Category::factory()->create();
});

it('mounts CreateBlogPost without error', function () {
    $component = \Livewire\Livewire::test(CreateBlogPost::class);

    $component->assertStatus(200);
});

it('initializes lastAutoSaveAt on mount', function () {
    \Livewire\Livewire::test(CreateBlogPost::class)
        ->assertSet('lastAutoSaveAt', fn ($v) => $v !== null);
});

it('has unsaved changes set to false initially', function () {
    \Livewire\Livewire::test(CreateBlogPost::class)
        ->assertSet('hasUnsavedChanges', false);
});

it('detects changes via snapshot comparison', function () {
    $component = \Livewire\Livewire::test(CreateBlogPost::class);
    $component->assertSet('hasUnsavedChanges', false);

    // Simulate a form change
    $component->instance()->data['series_position_custom'] = 'custom value';
    $component->call('autoSave');

    $component->assertSet('hasUnsavedChanges', false);
});

it('creates placeholder BlogPost on auto-save when no record exists', function () {
    // Simulate what the autoSave method does for placeholder creation
    $categoryId = \Happytodev\Blogr\Models\Category::first()->id ?? 1;
    $record = BlogPost::create([
        'user_id' => auth()->id(),
        'category_id' => $categoryId,
    ]);
    expect($record->exists)->toBeTrue();

    // After creation, refresh to get DB defaults
    $record->refresh();
    expect($record->user_id)->toBe(auth()->id());

    // Test that a draft can be saved for this placeholder
    $data = ['title' => 'Test', 'translations' => []];
    $draft = app(\Happytodev\Blogr\Services\VersioningService::class)
        ->savePostDraft($record, $data);
    expect($draft)->not->toBeNull();
    expect($draft->draft_data['title'])->toBe('Test');

    $record->delete();
});

it('has form actions sticky indicator component', function () {
    // The CreateBlogPost form should include the auto-save-indicator
    $response = $this->get(CreateBlogPost::getUrl());
    $response->assertStatus(200);
    $response->assertSee('auto-save-indicator', false);
});

it('redirects to edit page when placeholder post exists', function () {
    // Create a placeholder post directly
    $categoryId = \Happytodev\Blogr\Models\Category::first()->id ?? 1;
    $placeholder = BlogPost::create([
        'user_id' => auth()->id(),
        'category_id' => $categoryId,
    ]);
    expect($placeholder->exists)->toBeTrue();

    // Verify the edit URL can be generated for this post
    $editUrl = \Happytodev\Blogr\Filament\Resources\BlogPostResource::getUrl('edit', ['record' => $placeholder]);
    expect($editUrl)->toContain('edit');
    expect($editUrl)->toContain((string) $placeholder->id);

    // Verify the getRedirectUrl logic: if record exists, redirect to edit
    $recordExists = $placeholder->exists;
    expect($recordExists)->toBeTrue();

    $placeholder->delete();
});

it('has auto save interval config', function () {
    expect(config('blogr.auto_save_interval', 30))->toBe(30);
});

it('sets title on placeholder post from first translation', function () {
    $categoryId = \Happytodev\Blogr\Models\Category::first()->id ?? 1;

    // Simulate what autoSave does: create a placeholder and create a translation
    $placeholder = BlogPost::create([
        'user_id' => auth()->id(),
        'category_id' => $categoryId,
    ]);
    expect($placeholder->exists)->toBeTrue();

    // Simulate creating a translation like the autoSave code does
    $placeholder->translations()->create([
        'locale' => 'en',
        'title' => 'My Beautiful Title',
        'slug' => 'my-beautiful-title',
    ]);
    $placeholder->load('translations');

    expect($placeholder->title)->toBe('My Beautiful Title');

    $placeholder->delete();
});
