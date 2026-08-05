<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostDraft;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\BlogPostVersion;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Services\VersioningService;
use Happytodev\Blogr\Tests\CmsTestCase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(CmsTestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);

    $category = Category::factory()->create();

    $this->post = BlogPost::factory()->create([
        'user_id' => $this->admin->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'fr',
        'title' => 'Article français',
        'slug' => 'article-francais',
        'content' => 'Contenu français',
    ]);
});

test('regression_342_publish_creates_new_locale_translation', function () {
    expect($this->post->translations()->where('locale', 'fr')->exists())->toBeTrue();

    app(VersioningService::class)->savePostDraft($this->post, [
        'translations' => [
            ['locale' => 'fr', 'title' => 'Article français', 'slug' => 'article-francais', 'content' => 'Contenu français'],
            ['locale' => 'en', 'title' => 'English Article', 'slug' => 'english-article', 'content' => 'English content'],
        ],
    ]);

    app(VersioningService::class)->publishPostDraft($this->post, []);

    $en = BlogPostTranslation::where('blog_post_id', $this->post->id)
        ->where('locale', 'en')
        ->first();

    expect($en)->not->toBeNull()
        ->and($en->title)->toBe('English Article')
        ->and($en->slug)->toBe('english-article')
        ->and($en->content)->toBe('English content');

    $fr = $this->post->translations()->where('locale', 'fr')->first();
    expect($fr->title)->toBe('Article français');

    $version = BlogPostVersion::where('blog_post_translation_id', $en->id)->first();
    expect($version)->not->toBeNull()
        ->and($version->version_number)->toBe(1)
        ->and($version->title)->toBe('English Article');

    expect(BlogPostDraft::where('blog_post_id', $this->post->id)->exists())->toBeFalse();
});

test('regression_342_save_and_publish_persists_new_locale_translation', function () {
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);

    $instance = $component->instance();
    $instance->data['translations'] = [
        ['locale' => 'fr', 'title' => 'Article français', 'slug' => 'article-francais', 'content' => 'Contenu français'],
        ['locale' => 'en', 'title' => 'English Article', 'slug' => 'english-article', 'content' => 'English content'],
    ];

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    $en = BlogPostTranslation::where('blog_post_id', $this->post->id)
        ->where('locale', 'en')
        ->first();

    expect($en)->not->toBeNull()
        ->and($en->title)->toBe('English Article')
        ->and($en->content)->toBe('English content');
});

test('regression_342_save_as_draft_keeps_new_locale_in_draft', function () {
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);

    $instance = $component->instance();
    $instance->data['translations'] = [
        ['locale' => 'fr', 'title' => 'Article français', 'slug' => 'article-francais', 'content' => 'Contenu français'],
        ['locale' => 'en', 'title' => 'English Article', 'slug' => 'english-article', 'content' => 'English content'],
    ];

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAsDraft');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    $draft = app(VersioningService::class)->getPostDraft($this->post);

    expect($draft)->not->toBeNull();

    $enInDraft = collect($draft->draft_data['translations'] ?? [])
        ->firstWhere('locale', 'en');

    expect($enInDraft)->not->toBeNull()
        ->and($enInDraft['title'])->toBe('English Article')
        ->and($enInDraft['content'])->toBe('English content');

    $en = BlogPostTranslation::where('blog_post_id', $this->post->id)
        ->where('locale', 'en')
        ->first();
    expect($en)->toBeNull();
});
