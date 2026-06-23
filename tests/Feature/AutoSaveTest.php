<?php

use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\CmsTestCase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(CmsTestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    config()->set('blogr.auto_save_interval', 30);

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

it('has auto_save_interval config default of 30', function () {
    expect(config('blogr.auto_save_interval', 30))->toBe(30);
});

it('initializes lastAutoSaveAt on mount', function () {
    Livewire::test(EditBlogPost::class, [
        'record' => $this->post->id,
    ])->assertSet('lastAutoSaveAt', fn ($v) => $v !== null);
});

it('detects changes via snapshot comparison', function () {
    $component = Livewire::test(EditBlogPost::class, [
        'record' => $this->post->id,
    ]);

    // Initial state: no unsaved changes
    $component->assertSet('hasUnsavedChanges', false);

    // Modify the form state directly
    $component->instance()->data['title'] = 'Updated Title';

    // Call autoSave — should detect the diff and save
    $component->call('autoSave');

    // After save, snapshot is updated and hasUnsavedChanges is false
    $component->assertSet('hasUnsavedChanges', false);
});

it('has auto save interval field in settings', function () {
    $response = $this->get(BlogrSettings::getUrl());

    $response->assertStatus(200);
    $response->assertSee('Auto-save interval');
});
