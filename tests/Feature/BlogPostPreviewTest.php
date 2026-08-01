<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Services\VersioningService;
use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\URL;

uses(TestCase::class);

test('preview with valid signed URL shows draft content for published post', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
        'title' => 'Original Title',
        'slug' => 'original-slug',
        'content' => 'Original content',
    ]);

    $translation = $post->translations()->first();

    app(VersioningService::class)->savePostDraft($post, [
        'translations' => [
            ['locale' => 'en', 'title' => 'Draft Title', 'slug' => 'draft-slug', 'content' => 'Draft content'],
        ],
    ]);

    $url = URL::signedRoute('blog.post.preview', ['translationId' => $translation->id]);

    $response = $this->get($url);

    $response->assertStatus(200);
    $response->assertSee('Draft Title');
    $response->assertSee('Draft content');
    $response->assertDontSee('Original Title');
    $response->assertDontSee('Original content');
    $response->assertSee('PREVIEW');
});

test('preview works for unpublished posts', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => false,
        'published_at' => null,
        'default_locale' => 'en',
        'title' => 'Unpublished Post',
        'slug' => 'unpublished-preview',
        'content' => 'Not yet published',
    ]);

    $translation = $post->translations()->first();

    $url = URL::signedRoute('blog.post.preview', ['translationId' => $translation->id]);

    $response = $this->get($url);

    $response->assertStatus(200);
    $response->assertSee('Unpublished Post');
});

test('preview works for scheduled posts', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now()->addDays(1),
        'default_locale' => 'en',
        'title' => 'Scheduled Post',
        'slug' => 'scheduled-preview',
        'content' => 'Scheduled content',
    ]);

    $translation = $post->translations()->first();

    $url = URL::signedRoute('blog.post.preview', ['translationId' => $translation->id]);

    $response = $this->get($url);

    $response->assertStatus(200);
    $response->assertSee('Scheduled Post');
});

test('preview with invalid signature returns 403', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
        'title' => 'Invalid Sig Post',
        'slug' => 'invalid-sig',
        'content' => 'Content',
    ]);

    $translation = $post->translations()->first();

    $response = $this->get('/_preview/post/'.$translation->id.'?signature=invalid');

    $response->assertStatus(403);
});

test('preview for non-existent translation returns 404', function () {
    $url = URL::signedRoute('blog.post.preview', ['translationId' => 99999]);

    $response = $this->get($url);

    $response->assertStatus(404);
});

test('regular blog.show still returns 404 for unpublished posts', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => false,
        'published_at' => null,
        'default_locale' => 'en',
        'title' => 'Unpublished Show Post',
        'slug' => 'unpublished-show',
        'content' => 'Content',
    ]);

    $response = $this->get(route('blog.show', $post->translations()->first()->slug));

    $response->assertStatus(404);
});
