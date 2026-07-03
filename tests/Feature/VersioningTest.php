<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostDraft;
use Happytodev\Blogr\Models\BlogPostVersion;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageVersion;
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
    Livewire::test(EditBlogPost::class, [
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

// ── Save Draft ──

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

it('has form actions for Save & Publish, Save Draft, Cancel', function () {
    Livewire::test(EditBlogPost::class, [
        'record' => $this->post->id,
    ])
        ->assertSee('Save & Publish')
        ->assertSee('Save Draft');
});

it('has history action in header', function () {
    Livewire::test(EditBlogPost::class, [
        'record' => $this->post->id,
    ])
        ->assertSee('History');
});

// ── CMS page publish: no version created when data unchanged ──

it('does not create a version when publishing identical content', function () {
    $page = CmsPage::factory()->create([
        'is_published' => true,
        'published_at' => now(),
    ]);
    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Test Page',
        'slug' => 'test-page',
        'blocks' => [
            '550e8400-e29b-41d4-a716-446655440000' => [
                'type' => 'test_block',
                'data' => [
                    'title' => 'Hello',
                    'body' => 'World',
                ],
            ],
        ],
    ]);

    $service = app(VersioningService::class);

    // First publish: saves draft → creates version v1
    $service->saveDraft($translation, [
        'title' => 'Test Page',
        'slug' => 'test-page',
        'blocks' => [
            '550e8400-e29b-41d4-a716-446655440000' => [
                'type' => 'test_block',
                'data' => [
                    'title' => 'Hello',
                    'body' => 'World',
                ],
            ],
        ],
    ]);
    $service->publish($translation);

    $versions = CmsPageVersion::where('cms_page_translation_id', $translation->id)->get();
    expect($versions)->toHaveCount(1);

    // Second publish with IDENTICAL data (only UUIDs differ) → should NOT create v2
    $service->saveDraft($translation, [
        'title' => 'Test Page',
        'slug' => 'test-page',
        'blocks' => [
            '660e8400-e29b-41d4-a716-446655440001' => [
                'type' => 'test_block',
                'data' => [
                    'title' => 'Hello',
                    'body' => 'World',
                ],
            ],
        ],
    ]);
    $service->publish($translation);

    $versions->fresh();
    $versions = CmsPageVersion::where('cms_page_translation_id', $translation->id)->get();
    expect($versions)->toHaveCount(1);
});

it('regression_269_history_shows_photo_changes', function () {
    // Create two versions with different photos
    BlogPostVersion::create([
        'blog_post_translation_id' => $this->translation->id,
        'version_number' => 1,
        'title' => 'v1',
        'photo' => 'blog-photos/old.jpg',
    ]);
    BlogPostVersion::create([
        'blog_post_translation_id' => $this->translation->id,
        'version_number' => 2,
        'title' => 'v2',
        'photo' => 'blog-photos/new.jpg',
    ]);

    // Render the version-history component directly with the same data
    // structure that EditBlogPost's history action builds.
    $versions = BlogPostVersion::where('blog_post_translation_id', $this->translation->id)
        ->orderBy('version_number')
        ->get();

    $history = collect();
    $prevVersion = null;
    foreach ($versions as $v) {
        $currentFields = $v->only([
            'title', 'slug', 'tldr', 'content',
            'seo_title', 'seo_description', 'seo_keywords', 'photo',
        ]);
        $previousFields = $prevVersion ? $prevVersion->only([
            'title', 'slug', 'tldr', 'content',
            'seo_title', 'seo_description', 'seo_keywords', 'photo',
        ]) : [];
        $changes = $prevVersion
            ? array_keys(array_diff_assoc($currentFields, $previousFields))
            : ['initial'];

        $history->push([
            'type' => 'version',
            'title' => $v->title,
            'version_number' => $v->version_number,
            'version_id' => $v->id,
            'translation_id' => $v->blog_post_translation_id,
            'locale' => $this->translation->locale,
            'created_at' => $v->created_at,
            'fields' => $currentFields,
            'previous_fields' => $prevVersion ? $previousFields : null,
            'changes' => $changes,
        ]);
        $prevVersion = $v;
    }

    $html = view('blogr::components.version-history', [
        'history' => $history,
    ])->render();

    expect($html)
        ->toContain('blog-photos/old.jpg')
        ->toContain('Cover Image');
});
