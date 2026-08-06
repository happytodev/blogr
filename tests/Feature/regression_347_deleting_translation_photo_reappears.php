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
        'default_locale' => 'fr',
        'title' => 'Article français',
        'slug' => 'article-francais',
        'content' => 'Contenu français',
    ]);

    $this->frTranslation = $this->post->translations()->where('locale', 'fr')->first();
    $this->frTranslation->update(['photo' => 'blog-photos/fr.jpg']);
});

it('regression_347_clearing_translation_photo_persists_on_save_and_publish', function () {
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    $translationData = collect($instance->data['translations'])
        ->firstWhere('locale', 'fr');
    $index = array_search($translationData, $instance->data['translations']);

    // FileUpload sends an empty array when the X button is clicked.
    $instance->data['translations'][$index]['photo'] = [];

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    $this->frTranslation->refresh();

    expect($this->frTranslation->photo)->toBeNull();
});

it('regression_347_clearing_translation_photo_is_recorded_in_save_as_draft', function () {
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    $translationData = collect($instance->data['translations'])
        ->firstWhere('locale', 'fr');
    $index = array_search($translationData, $instance->data['translations']);

    $instance->data['translations'][$index]['photo'] = [];

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAsDraft');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    // Save Draft does not touch live translations; the deletion is recorded
    // in the draft and applied on the next publish.
    $this->frTranslation->refresh();
    expect($this->frTranslation->photo)->toBe('blog-photos/fr.jpg');

    $draft = app(VersioningService::class)->getPostDraft($this->post);
    expect($draft)->not->toBeNull();

    $frInDraft = collect($draft->draft_data['translations'] ?? [])
        ->firstWhere('locale', 'fr');

    expect($frInDraft['photo'] ?? null)->toBe([]);
});

it('regression_347_clearing_translation_photo_persists_on_publish_from_draft', function () {
    app(VersioningService::class)->savePostDraft($this->post, [
        'translations' => [
            ['locale' => 'fr', 'title' => 'Article français', 'slug' => 'article-francais', 'content' => 'Contenu français', 'photo' => []],
        ],
    ]);

    app(VersioningService::class)->publishPostDraft($this->post, []);

    $this->frTranslation->refresh();

    expect($this->frTranslation->photo)->toBeNull();
});
