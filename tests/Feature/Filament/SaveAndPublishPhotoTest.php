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
    $this->category = $category;

    $this->post = BlogPost::factory()->create([
        'user_id' => $admin->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
        'title' => 'Photo Save Test',
        'slug' => 'photo-save-test',
        'content' => 'Content',
    ]);
});

test('regression_266_general_photo_preserved_after_save_and_publish', function () {
    // Set a general photo on the post
    $this->post->update(['photo' => 'blog-photos/general-image.jpg']);

    // Open edit, call saveAndPublish without changing anything
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($component->instance());

    $this->post->refresh();
    expect($this->post->photo)->toBe('blog-photos/general-image.jpg');
});

test('regression_266_translation_photo_preserved_after_save_and_publish', function () {
    // Set a photo on the EN translation
    $translation = $this->post->translations()->first();
    $translation->update(['photo' => 'blog-photos/translation-image.jpg']);

    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($component->instance());

    $translation->refresh();
    expect($translation->photo)->toBe('blog-photos/translation-image.jpg');
});

test('regression_266_new_photo_survives_save_and_publish', function () {
    // Simulate a newly uploaded photo by setting a new path via Livewire
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);

    // Simulate a FileUpload state change: set a new photo path
    $component->set('data.photo', 'blog-photos/newly-uploaded.jpg');

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($component->instance());

    $this->post->refresh();
    expect($this->post->photo)->toBe('blog-photos/newly-uploaded.jpg');
});

test('regression_266_all_three_photos_preserved_after_save_and_publish', function () {
    // Add FR translation and set photos on all 3
    $this->post->update(['photo' => 'blog-photos/general.jpg']);

    $enTranslation = $this->post->translations()->first();
    $enTranslation->update(['photo' => 'blog-photos/en.jpg']);

    $frTranslation = $this->post->translations()->create([
        'locale' => 'fr',
        'title' => 'Titre FR',
        'slug' => 'titre-fr',
        'content' => 'Contenu FR',
        'photo' => 'blog-photos/fr.jpg',
    ]);

    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($component->instance());

    $this->post->refresh();
    expect($this->post->photo)->toBe('blog-photos/general.jpg');

    $enTranslation->refresh();
    expect($enTranslation->photo)->toBe('blog-photos/en.jpg');

    $frTranslation->refresh();
    expect($frTranslation->photo)->toBe('blog-photos/fr.jpg');
});

test('regression_267_photo_preserved_when_draft_missing_photo_key', function () {
    // Post has a translation with a photo
    $translation = $this->post->translations()->first();
    $translation->update(['photo' => 'blog-photos/model-photo.jpg']);

    // Save a draft that has translations data BUT without the photo key
    // (simulates auto-save saving raw Livewire state that omits null fields)
    app(VersioningService::class)->savePostDraft($this->post, [
        'translations' => [
            [
                'locale' => 'en',
                'title' => 'Edited title',
                'slug' => 'edited-slug',
                'content' => 'Edited content',
                // NO photo key here!
            ],
        ],
    ]);

    // Load the edit page — the draft overrides model data
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $component->assertStatus(200);

    // The form should still show the model's photo because
    // mutateFormDataBeforeFill should merge draft with model data
    $instance = $component->instance();
    $translationData = collect($instance->data['translations'] ?? [])
        ->firstWhere('locale', 'en');

    expect($translationData)->not->toBeNull();
    // FileUpload expects array format ['path.jpg']
    $photo = $translationData['photo'] ?? null;
    expect($photo)->toBe(['blog-photos/model-photo.jpg']);
});
