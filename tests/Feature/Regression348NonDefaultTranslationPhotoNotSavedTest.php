<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
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
        'default_locale' => 'fr',
        'title' => 'Article français',
        'slug' => 'article-francais',
        'content' => 'Contenu français',
    ]);

    // The English translation was added later, exactly like in production
    // (post published with FR as the default locale, EN added afterwards).
    $this->enTranslation = BlogPostTranslation::create([
        'blog_post_id' => $this->post->id,
        'locale' => 'en',
        'title' => 'English article',
        'slug' => 'english-article',
        'content' => 'English content',
    ]);
});

it('regression_348_non_default_translation_photo_saved_on_save_and_publish', function () {
    Storage::fake('public');
    Storage::disk('public')->put('blog-photos/en-cover.jpg', UploadedFile::fake()->image('en-cover.jpg', 1200, 630)->get());

    expect($this->enTranslation->photo)->toBeNull();

    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    $translationData = collect($instance->data['translations'])
        ->firstWhere('locale', 'en');
    $index = array_search($translationData, $instance->data['translations']);

    // Filament v5's FileUpload stores files keyed by UUID, e.g.
    // ['93302cc5-...' => 'blog-photos/en-cover.jpg'].
    $instance->data['translations'][$index]['photo'] = [
        '93302cc5-baf3-46f5-9acd-f348dc052d55' => 'blog-photos/en-cover.jpg',
    ];

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    $this->enTranslation->refresh();

    expect($this->enTranslation->photo)->toBe('blog-photos/en-cover.jpg');
});

it('regression_348_non_default_translation_photo_survives_stale_draft_overlay', function () {
    Storage::fake('public');
    Storage::disk('public')->put('blog-photos/en-cover.jpg', UploadedFile::fake()->image('en-cover.jpg', 1200, 630)->get());

    // A stale auto-saved draft whose EN entry has an empty photo (the
    // auto-save ran before the upload was committed). This is the production
    // pattern that reset the freshly uploaded photo before publishing.
    app(VersioningService::class)->savePostDraft($this->post, [
        'translations' => [
            ['locale' => 'fr', 'title' => 'Article français', 'slug' => 'article-francais', 'content' => 'Contenu français'],
            ['locale' => 'en', 'title' => 'English article', 'slug' => 'english-article', 'content' => 'English content', 'photo' => []],
        ],
    ]);

    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    // The form must NOT have dropped the EN photo from the fresh upload just
    // because the stale draft had it empty.
    $translationData = collect($instance->data['translations'])
        ->firstWhere('locale', 'en');
    $index = array_search($translationData, $instance->data['translations']);

    $instance->data['translations'][$index]['photo'] = [
        '93302cc5-baf3-46f5-9acd-f348dc052d55' => 'blog-photos/en-cover.jpg',
    ];

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    $this->enTranslation->refresh();

    expect($this->enTranslation->photo)->toBe('blog-photos/en-cover.jpg');
});

it('regression_348_non_default_translation_photo_displays_after_reload', function () {
    Storage::fake('public');
    Storage::disk('public')->put('blog-photos/en-cover.jpg', UploadedFile::fake()->image('en-cover.jpg', 1200, 630)->get());

    $this->enTranslation->update(['photo' => 'blog-photos/en-cover.jpg']);

    // Re-opening the edit form must show the EN cover in the translations repeater.
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    $translationData = collect($instance->data['translations'])
        ->firstWhere('locale', 'en');

    // Filament v5 keys the file by UUID; only the stored path value matters.
    $photo = $translationData['photo'] ?? null;

    expect($photo)->not->toBeNull()
        ->and(is_string($photo) ? $photo : (reset($photo) ?: null))->toBe('blog-photos/en-cover.jpg');
});

it('regression_348_non_default_translation_photo_persists_via_uploaded_file_state', function () {
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

    // Realistic FileUpload state right after a browser upload: the temp file
    // is referenced under a UUID key.
    $instance->data['translations'][$index]['photo'] = [
        '93302cc5-baf3-46f5-9acd-f348dc052d55' => $tempPath,
    ];

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    $this->enTranslation->refresh();

    expect($this->enTranslation->photo)->not->toBeNull()
        ->and($this->enTranslation->photo)->not->toContain('livewire-tmp')
        ->and(Storage::disk('public')->exists($this->enTranslation->photo))->toBeTrue();
});
