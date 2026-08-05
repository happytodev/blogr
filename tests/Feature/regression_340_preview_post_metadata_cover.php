<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Services\VersioningService;
use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\URL;

uses(TestCase::class);

test('preview shows the draft post-level cover (Post Metadata) when set', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => false,
        'published_at' => null,
        'default_locale' => 'en',
        'title' => 'Cover Preview',
        'slug' => 'cover-preview',
        'content' => 'Content',
        'photo' => 'blog-photos/old.jpg',
    ]);

    $translation = $post->translations()->first();

    app(VersioningService::class)->savePostDraft($post, [
        'photo' => 'blog-photos/new.jpg',
        'translations' => [
            ['locale' => 'en', 'title' => 'Cover Preview', 'slug' => 'cover-preview', 'content' => 'Content'],
        ],
    ]);

    $url = URL::signedRoute('blog.post.preview', ['translationId' => $translation->id]);
    $response = $this->get($url);

    $response->assertStatus(200);
    $response->assertSee('/storage/blog-photos/new.jpg', false);
    $response->assertDontSee('/storage/blog-photos/old.jpg', false);
});

test('preview keeps translation-specific cover priority over the post-level cover', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => false,
        'published_at' => null,
        'default_locale' => 'en',
        'title' => 'Cover Precedence',
        'slug' => 'cover-precedence',
        'content' => 'Content',
        'photo' => 'blog-photos/old.jpg',
    ]);

    $translation = $post->translations()->first();

    app(VersioningService::class)->savePostDraft($post, [
        'photo' => 'blog-photos/main-draft.jpg',
        'translations' => [
            ['locale' => 'en', 'title' => 'Cover Precedence', 'slug' => 'cover-precedence', 'content' => 'Content', 'photo' => 'blog-photos/trans-draft.jpg'],
        ],
    ]);

    $url = URL::signedRoute('blog.post.preview', ['translationId' => $translation->id]);
    $response = $this->get($url);

    $response->assertStatus(200);
    $response->assertSee('/storage/blog-photos/trans-draft.jpg', false);
    $response->assertDontSee('/storage/blog-photos/main-draft.jpg', false);
});

test('preview clears the post-level cover when the draft sets it to null', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => false,
        'published_at' => null,
        'default_locale' => 'en',
        'title' => 'Cover Cleared',
        'slug' => 'cover-cleared',
        'content' => 'Content',
        'photo' => 'blog-photos/old.jpg',
    ]);

    $translation = $post->translations()->first();

    app(VersioningService::class)->savePostDraft($post, [
        'photo' => null,
        'translations' => [
            ['locale' => 'en', 'title' => 'Cover Cleared', 'slug' => 'cover-cleared', 'content' => 'Content'],
        ],
    ]);

    $url = URL::signedRoute('blog.post.preview', ['translationId' => $translation->id]);
    $response = $this->get($url);

    $response->assertStatus(200);
    $response->assertDontSee('/storage/blog-photos/old.jpg', false);
});
