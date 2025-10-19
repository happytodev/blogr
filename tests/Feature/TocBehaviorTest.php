<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->author = User::factory()->create([
        'name' => 'Toc Author',
        'slug' => 'toc-author',
    ]);

    $this->category = Category::factory()->create([
        'name' => 'Misc',
        'slug' => 'misc',
    ]);

    $this->post = BlogPost::create([
        'title' => 'TOC Article',
        'slug' => 'toc-article-' . uniqid(),
        'content' => 'Test content',
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    // attach a translation that contains a frontmatter disable_toc flag
    $this->post->translations()->updateOrCreate([
        'locale' => 'en',
    ], [
        'title' => 'TOC Article',
        'slug' => 'toc-article-' . uniqid(),
        'content' => "---\ndisable_toc: true\n---\n\n# Intro\n\nContent",
    ]);

    // Ensure the model's loaded relations are fresh so frontmatter is picked up
    $this->post->load('translations');
});

it('respects disable_toc frontmatter when strict_mode is false', function () {
    config(['blogr.toc.enabled' => true]);
    config(['blogr.toc.strict_mode' => false]);

    expect($this->post->shouldDisplayToc())->toBe(false);
});

it('respects global setting when strict_mode is true', function () {
    config(['blogr.toc.enabled' => false]);
    config(['blogr.toc.strict_mode' => true]);

    expect($this->post->shouldDisplayToc())->toBe(false);
});

it('allows post override when strict_mode is false and disable_toc absent', function () {
    $post2 = BlogPost::create([
        'title' => 'TOC Article 2',
        'slug' => 'toc-article-2-' . uniqid(),
        'content' => 'Test content',
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $post2->translations()->updateOrCreate([
        'locale' => 'en',
    ], [
        'title' => 'TOC Article 2',
        'slug' => 'toc-article-2-' . uniqid(),
        'content' => "# Intro\n\nContent",
    ]);

    $post2->load('translations');

    config(['blogr.toc.enabled' => true]);
    config(['blogr.toc.strict_mode' => false]);

    expect($post2->shouldDisplayToc())->toBe(true);
});
