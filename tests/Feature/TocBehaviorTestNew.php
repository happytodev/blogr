<?php<?php



use Happytodev\Blogr\Models\BlogPost;namespace Happytodev\Blogr\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Workbench\App\Models\User;class TocBehaviorTestNew

{

uses(RefreshDatabase::class);}


beforeEach(function () {
    $this->author = User::factory()->create([
        'name' => 'Toc Author',
        'slug' => 'toc-author',
    ]);

    $this->post = BlogPost::create([
        'title' => 'TOC Article',
        'slug' => 'toc-article',
        'content' => 'Test content',
        'user_id' => $this->author->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    // attach a translation that contains a frontmatter disable_toc flag
    $this->post->translations()->create([
        'locale' => 'en',
        'title' => 'TOC Article',
        'slug' => 'toc-article',
        'content' => "---\ndisable_toc: true\n---\n\n# Intro\n\nContent",
    ]);
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
        'slug' => 'toc-article-2',
        'content' => 'Test content',
        'user_id' => $this->author->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $post2->translations()->create([
        'locale' => 'en',
        'title' => 'TOC Article 2',
        'slug' => 'toc-article-2',
        'content' => "# Intro\n\nContent",
    ]);

    config(['blogr.toc.enabled' => true]);
    config(['blogr.toc.strict_mode' => false]);

    expect($post2->shouldDisplayToc())->toBe(true);
});
