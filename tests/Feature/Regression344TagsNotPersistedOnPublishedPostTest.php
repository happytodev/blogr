<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;
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
        'title' => 'Tagged Post',
        'slug' => 'tagged-post',
        'content' => 'Content',
    ]);

    $this->tagLaravel = Tag::factory()->create(['name' => 'Laravel']);
    $this->tagPhp = Tag::factory()->create(['name' => 'PHP']);
});

it('regression_344_existing_tags_persist_on_save_and_publish', function () {
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    $instance->data['tags'] = [$this->tagLaravel->id, $this->tagPhp->id];

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    $tagIds = $this->post->tags()->pluck('tags.id')->toArray();

    expect($tagIds)->toContain($this->tagLaravel->id)
        ->and($tagIds)->toContain($this->tagPhp->id);
});

it('regression_344_existing_tags_persist_on_save_as_draft', function () {
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    $instance->data['tags'] = [$this->tagLaravel->id, $this->tagPhp->id];

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAsDraft');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    $tagIds = $this->post->tags()->pluck('tags.id')->toArray();

    expect($tagIds)->toContain($this->tagLaravel->id)
        ->and($tagIds)->toContain($this->tagPhp->id);
});

it('regression_344_tags_from_auto_saved_draft_apply_on_publish', function () {
    app(VersioningService::class)->savePostDraft($this->post, [
        'translations' => [
            ['locale' => 'en', 'title' => 'Tagged Post', 'slug' => 'tagged-post', 'content' => 'Content'],
        ],
        'tags' => [$this->tagLaravel->id, $this->tagPhp->id],
    ]);

    app(VersioningService::class)->publishPostDraft($this->post, []);

    $tagIds = $this->post->tags()->pluck('tags.id')->toArray();

    expect($tagIds)->toContain($this->tagLaravel->id)
        ->and($tagIds)->toContain($this->tagPhp->id);
});

it('regression_344_tags_persist_for_unpublished_post_via_standard_save', function () {
    $draftPost = BlogPost::factory()->create([
        'user_id' => $this->post->user_id,
        'category_id' => $this->post->category_id,
        'is_published' => false,
        'title' => 'Unpublished',
        'slug' => 'unpublished-tags',
    ]);

    $draftPost->tags()->attach($this->tagLaravel->id);

    $component = Livewire::test(EditBlogPost::class, ['record' => $draftPost->id]);
    $instance = $component->instance();

    $instance->data['tags'] = [$this->tagLaravel->id, $this->tagPhp->id];

    $reflection = new ReflectionMethod(EditBlogPost::class, 'save');
    $reflection->setAccessible(true);
    $reflection->invoke($instance, true);

    $tagIds = $draftPost->tags()->pluck('tags.id')->toArray();

    expect($tagIds)->toContain($this->tagLaravel->id)
        ->and($tagIds)->toContain($this->tagPhp->id);
});
