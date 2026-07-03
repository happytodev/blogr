<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Services\VersioningService;
use Happytodev\Blogr\Tests\CmsTestCase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(CmsTestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $category = Category::factory()->create();

    $this->post = BlogPost::factory()->create([
        'user_id' => $admin->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
        'title' => 'Corrupted Photo Test',
        'slug' => 'corrupted-photo-test',
        'content' => 'Content',
    ]);
});

test('regression_265_corrupted_main_photo_does_not_crash', function () {
    $this->post->update(['photo' => '[]']);

    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $component->assertStatus(200);
    // No TypeError should occur — page loads successfully
});

test('regression_265_corrupted_translation_photo_does_not_crash', function () {
    $translation = $this->post->translations()->first();
    $translation->update(['photo' => '["blog-photos/old-image.jpg"]']);

    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $component->assertStatus(200);
    // No TypeError should occur — page loads successfully
});

test('regression_265_corrupted_photo_in_draft_does_not_crash', function () {
    // Simulate a draft with corrupted photo data (PHP array in draft JSON)
    app(VersioningService::class)->savePostDraft($this->post, [
        'translations' => [
            [
                'locale' => 'en',
                'title' => 'Draft with bad photo',
                'slug' => 'draft-bad-photo',
                'content' => 'Test',
                'photo' => [],
            ],
        ],
    ]);

    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $component->assertStatus(200);
});

test('regression_265_upload_new_photo_after_corruption', function () {
    $this->post->update(['photo' => '[]']);

    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $component->assertStatus(200);

    $component->set('data.photo', 'blog-photos/new-image.jpg');

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($component->instance());

    $this->post->refresh();
    expect($this->post->photo)->toBe('blog-photos/new-image.jpg');
});
