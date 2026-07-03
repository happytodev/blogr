<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\CreateBlogPost;
use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Services\VersioningService;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\ViewErrorBag;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(CmsTestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $this->session(['errors' => new ViewErrorBag]);

    $this->category = Category::factory()->create();
});

test('regression_264_photo_survives_save_and_publish', function () {
    $post = BlogPost::factory()->create([
        'user_id' => auth()->id(),
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'photo' => 'blog-photos/test-image.jpg',
        'title' => 'Photo Test Post',
        'slug' => 'photo-test-post',
        'content' => 'Content with photo',
    ]);

    $translation = $post->translations()->first();
    $translation->update(['photo' => 'blog-photos/translation-image.jpg']);

    $component = Livewire::test(EditBlogPost::class, ['record' => $post->id]);

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($component->instance());

    $post->refresh();
    expect($post->photo)->toBe('blog-photos/test-image.jpg');

    $translation->refresh();
    expect($translation->photo)->toBe('blog-photos/translation-image.jpg');
});

test('regression_264_photo_survives_save_draft', function () {
    $post = BlogPost::factory()->create([
        'user_id' => auth()->id(),
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'photo' => 'blog-photos/test-image.jpg',
        'title' => 'Draft Photo Test',
        'slug' => 'draft-photo-test',
        'content' => 'Content',
    ]);

    $translation = $post->translations()->first();
    $translation->update(['photo' => 'blog-photos/translation-image.jpg']);

    $component = Livewire::test(EditBlogPost::class, ['record' => $post->id]);

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAsDraft');
    $reflection->setAccessible(true);
    $reflection->invoke($component->instance());

    $post->refresh();
    expect($post->photo)->toBe('blog-photos/test-image.jpg');

    $translation->refresh();
    expect($translation->photo)->toBe('blog-photos/translation-image.jpg');

    $draft = app(VersioningService::class)->getPostDraft($post);
    expect($draft)->not->toBeNull();

    $translations = $draft->draft_data['translations'] ?? [];
    $enTranslation = collect($translations)->firstWhere('locale', 'en');
    expect($enTranslation)->not->toBeNull();
    // The draft should have the photo as a string, or not present (empty unchanged)
    $photo = $enTranslation['photo'] ?? null;
    if ($photo !== null) {
        expect($photo)->toBeString();
    }

    // The model's photo must always be preserved
    $post->refresh();
    expect($post->photo)->toBe('blog-photos/test-image.jpg');

    $translation->refresh();
    expect($translation->photo)->toBe('blog-photos/translation-image.jpg');
});

test('regression_264_all_fields_survive_publish', function () {
    $post = BlogPost::factory()->create([
        'user_id' => auth()->id(),
        'category_id' => $this->category->id,
        'is_published' => false,
        'title' => 'All Fields Draft',
        'slug' => 'all-fields-draft',
        'content' => 'Original content',
        'default_locale' => 'en',
        'photo' => 'blog-photos/main-image.jpg',
    ]);

    $translation = $post->translations()->first();
    $translation->update([
        'photo' => 'blog-photos/translation-image.jpg',
        'tldr' => 'Original TLDR',
        'seo_title' => 'Original SEO Title',
        'seo_description' => 'Original SEO Description',
        'seo_keywords' => 'original, keywords',
    ]);

    $component = Livewire::test(EditBlogPost::class, ['record' => $post->id]);

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($component->instance());

    $post->refresh();
    $post->load('translations');

    expect($post->photo)->toBe('blog-photos/main-image.jpg');
    expect($post->is_published)->toBeTrue();
    expect($post->default_locale)->toBe('en');

    $en = $post->translate('en');
    expect($en->title)->toBe('All Fields Draft');
    expect($en->slug)->toBe('all-fields-draft');
    expect($en->content)->toBe('Original content');
    expect($en->tldr)->toBe('Original TLDR');
    expect($en->photo)->toBe('blog-photos/translation-image.jpg');
    expect($en->seo_title)->toBe('Original SEO Title');
    expect($en->seo_description)->toBe('Original SEO Description');
    expect($en->seo_keywords)->toBe('original, keywords');
});
