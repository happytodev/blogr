<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostDraft;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\BlogPostVersion;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Services\VersioningService;
use Spatie\Permission\Models\Role;

uses(Happytodev\Blogr\Tests\CmsTestCase::class);

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
        'title' => 'Original Title',
        'slug' => 'original-slug',
        'content' => 'Original content',
    ]);
    $this->translation = $this->post->translations()->first();
});

// ── Drafts ──

it('creates draft for published post on auto-save', function () {
    app(VersioningService::class)->savePostDraft($this->post, [
        'translations' => [
            ['locale' => 'en', 'title' => 'Draft Title'],
        ],
    ]);

    $draft = BlogPostDraft::where('blog_post_id', $this->post->id)->first();

    expect($draft)->not->toBeNull()
        ->and($draft->draft_data['translations'][0]['title'])->toBe('Draft Title');
});

it('autoSave via Livewire does not modify live translations', function () {
    \Livewire\Livewire::test(\Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost::class, [
        'record' => $this->post->id,
    ])
        ->set('data.translations.0.title', 'Changed via Livewire')
        ->call('autoSave');

    $this->translation->refresh();

    expect($this->translation->title)->toBe('Original Title');
});

it('upserts draft instead of creating duplicate', function () {
    app(VersioningService::class)->savePostDraft($this->post, ['translations' => [['title' => 'Draft 1']]]);
    app(VersioningService::class)->savePostDraft($this->post, ['translations' => [['title' => 'Draft 2']]]);

    $drafts = BlogPostDraft::where('blog_post_id', $this->post->id)->get();

    expect($drafts)->toHaveCount(1)
        ->and($drafts->first()->draft_data['translations'][0]['title'])->toBe('Draft 2');
});

it('loads form from draft if one exists', function () {
    app(VersioningService::class)->savePostDraft($this->post, [
        'translations' => [
            ['locale' => 'en', 'title' => 'Draft Title', 'slug' => 'draft-slug'],
        ],
    ]);

    $draft = BlogPostDraft::where('blog_post_id', $this->post->id)->first();

    expect($draft)->not->toBeNull()
        ->and($draft->draft_data['translations'][0]['title'])->toBe('Draft Title');
});

// ── Save & Publish ──

it('publishes draft to live translation', function () {
    app(VersioningService::class)->savePostDraft($this->post, [
        'translations' => [
            ['locale' => 'en', 'title' => 'Published Title', 'slug' => 'published-slug', 'content' => 'Published content'],
        ],
    ]);

    app(VersioningService::class)->publishPostDraft($this->post, []);

    $this->translation->refresh();

    expect($this->translation->title)->toBe('Published Title')
        ->and($this->translation->slug)->toBe('published-slug')
        ->and($this->translation->content)->toBe('Published content');
});

it('creates version record on publish', function () {
    app(VersioningService::class)->savePostDraft($this->post, [
        'translations' => [
            ['locale' => 'en', 'title' => 'v2 Title'],
        ],
    ]);

    app(VersioningService::class)->publishPostDraft($this->post, []);

    $versions = BlogPostVersion::where('blog_post_translation_id', $this->translation->id)->get();

    expect($versions)->toHaveCount(1)
        ->and($versions->first()->title)->toBe('v2 Title')
        ->and($versions->first()->version_number)->toBe(1);
});

it('increments version number on subsequent publishes', function () {
    app(VersioningService::class)->savePostDraft($this->post, ['translations' => [['locale' => 'en', 'title' => 'v2']]]);
    app(VersioningService::class)->publishPostDraft($this->post, []);

    app(VersioningService::class)->savePostDraft($this->post, ['translations' => [['locale' => 'en', 'title' => 'v3']]]);
    app(VersioningService::class)->publishPostDraft($this->post, []);

    $versions = BlogPostVersion::where('blog_post_translation_id', $this->translation->id)
        ->orderBy('version_number')
        ->get();

    expect($versions)->toHaveCount(2)
        ->and($versions[0]->version_number)->toBe(1)
        ->and($versions[1]->version_number)->toBe(2);
});

it('deletes draft after publish', function () {
    app(VersioningService::class)->savePostDraft($this->post, ['translations' => [['title' => 'Draft']]]);
    app(VersioningService::class)->publishPostDraft($this->post, []);

    $draft = BlogPostDraft::where('blog_post_id', $this->post->id)->first();

    expect($draft)->toBeNull();
});

it('publishes post via VersioningService', function () {
    app(VersioningService::class)->savePostDraft($this->post, [
        'translations' => [
            ['locale' => 'en', 'title' => 'Published Title', 'slug' => 'published-slug', 'content' => 'Published content'],
        ],
    ]);

    app(VersioningService::class)->publishPostDraft($this->post, []);

    $this->translation->refresh();

    expect($this->translation->title)->toBe('Published Title');
});

// ── Save as Draft ──

it('save as draft keeps live translation unchanged', function () {
    app(VersioningService::class)->savePostDraft($this->post, [
        'translations' => [
            ['locale' => 'en', 'title' => 'Draft Only', 'slug' => 'draft-only'],
        ],
    ]);

    $this->translation->refresh();

    expect($this->translation->title)->toBe('Original Title');

    $draft = BlogPostDraft::where('blog_post_id', $this->post->id)->first();
    expect($draft)->not->toBeNull()
        ->and($draft->draft_data['translations'][0]['title'])->toBe('Draft Only');
});

// ── Unpublished posts ──

it('writes directly to live for unpublished posts', function () {
    $unpublishedPost = BlogPost::factory()->create([
        'user_id' => $this->admin->id,
        'is_published' => false,
        'title' => 'Draft Post',
        'slug' => 'draft-post',
    ]);

    $enTranslation = $unpublishedPost->translations()->where('locale', 'en')->first();
    $enTranslation->update(['title' => 'Updated Draft Post']);

    $enTranslation->refresh();

    expect($enTranslation->title)->toBe('Updated Draft Post');
});

// ── Versions ──

it('lists versions ordered by newest first', function () {
    BlogPostVersion::create([
        'blog_post_translation_id' => $this->translation->id,
        'version_number' => 1,
        'title' => 'v1',
        'created_at' => now()->subDays(2),
    ]);
    BlogPostVersion::create([
        'blog_post_translation_id' => $this->translation->id,
        'version_number' => 2,
        'title' => 'v2',
        'created_at' => now()->subDay(),
    ]);

    $versions = app(VersioningService::class)->listVersions($this->translation);

    expect($versions)->toHaveCount(2)
        ->and($versions->first()->version_number)->toBe(2);
});

it('stores all text fields in version', function () {
    app(VersioningService::class)->savePostDraft($this->post, [
        'translations' => [
            [
                'locale' => 'en',
                'title' => 'Full Version',
                'slug' => 'full-version',
                'content' => 'Full content',
                'tldr' => 'TLDR',
                'seo_title' => 'SEO Title',
                'seo_description' => 'SEO Desc',
                'seo_keywords' => 'kw1, kw2',
            ],
        ],
    ]);
    app(VersioningService::class)->publishPostDraft($this->post, []);

    $version = BlogPostVersion::where('blog_post_translation_id', $this->translation->id)->first();

    expect($version->title)->toBe('Full Version')
        ->and($version->slug)->toBe('full-version')
        ->and($version->content)->toBe('Full content')
        ->and($version->tldr)->toBe('TLDR')
        ->and($version->seo_title)->toBe('SEO Title')
        ->and($version->seo_description)->toBe('SEO Desc')
        ->and($version->seo_keywords)->toBe('kw1, kw2');
});

it('has form actions for Save & Publish, Save as Draft, Cancel', function () {
    \Livewire\Livewire::test(\Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost::class, [
        'record' => $this->post->id,
    ])
        ->assertSee('Save & Publish')
        ->assertSee('Save as Draft');
});

it('has history action in header', function () {
    \Livewire\Livewire::test(\Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost::class, [
        'record' => $this->post->id,
    ])
        ->assertSee('History');
});
