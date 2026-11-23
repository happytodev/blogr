<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Services\VersioningService;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
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
        'default_locale' => 'en',
        'title' => 'English Post',
        'slug' => 'english-post',
        'content' => 'English content',
    ]);

    $enTranslation = $this->post->translations()->where('locale', 'en')->first();
    $this->enTranslation = $enTranslation;
});

it('regression_345_en_translation_photo_saved_on_save_and_publish', function () {
    Storage::fake('public');

    expect($this->enTranslation->photo)->toBeNull();

    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    $translationData = collect($instance->data['translations'])
        ->firstWhere('locale', 'en');
    $index = array_search($translationData, $instance->data['translations']);

    $instance->data['translations'][$index]['photo'] = ['blog-photos/newly-added.jpg'];

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    $this->enTranslation->refresh();

    expect($this->enTranslation->photo)->toBe('blog-photos/newly-added.jpg');
});

it('regression_345_en_translation_photo_saved_on_save_as_draft', function () {
    Storage::fake('public');

    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    $translationData = collect($instance->data['translations'])
        ->firstWhere('locale', 'en');
    $index = array_search($translationData, $instance->data['translations']);

    $instance->data['translations'][$index]['photo'] = ['blog-photos/newly-added.jpg'];

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAsDraft');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    $draft = app(VersioningService::class)->getPostDraft($this->post);
    expect($draft)->not->toBeNull();

    $enInDraft = collect($draft->draft_data['translations'] ?? [])
        ->firstWhere('locale', 'en');

    expect($enInDraft['photo'] ?? null)->toBe(['blog-photos/newly-added.jpg']);
});

it('regression_345_en_translation_photo_persists_via_uploaded_file_state', function () {
    Storage::fake('public');

    $tempDisk = FileUploadConfiguration::disk();
    Storage::fake($tempDisk);

    $file = UploadedFile::fake()->image('en-cover.jpg', 1200, 630);
    $tempPath = $file->store(FileUploadConfiguration::directory(), $tempDisk);

    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    $translationData = collect($instance->data['translations'])
        ->firstWhere('locale', 'en');
    $index = array_search($translationData, $instance->data['translations']);

    // Simulates the FileUpload field state right after an upload in the browser
    // (Livewire sends the temporary path; the real request resolves it).
    $instance->data['translations'][$index]['photo'] = [$tempPath];

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    $this->enTranslation->refresh();

    expect($this->enTranslation->photo)->not->toBeNull()
        ->and($this->enTranslation->photo)->not->toContain('livewire-tmp')
        ->and(Storage::disk('public')->exists($this->enTranslation->photo))->toBeTrue();
});
