<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
});

it('includes collapsible TOC CSS classes for entire TOC', function () {
    config(['blogr.toc.collapsible' => true]);
    
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'content' => "Introduction.\n\n## Section One\n\nText.\n\n### Subsection A\n\nText.\n\n## Section Two\n\nText.",
        'display_toc' => true,
    ]);

    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    
    // Check for new CSS classes for entire TOC collapsing
    $response->assertSee('.toc-wrapper-collapsible', false);
    $response->assertSee('.toc-toggle-icon', false);
    $response->assertSee('.toc-content-wrapper', false);
});

it('includes collapsible TOC JavaScript function when enabled', function () {
    config(['blogr.toc.collapsible' => true]);
    
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'content' => "Intro.\n\n## First\n\nText.\n\n### Sub\n\nText.",
        'display_toc' => true,
    ]);

    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    
    $response->assertSee('initCollapsibleToc', false);
    $response->assertSee('toc-toggle-icon', false);
    $response->assertSee('toc-wrapper-collapsible', false);
});

it('does not include collapsible JavaScript when disabled in config', function () {
    config(['blogr.toc.collapsible' => false]);
    
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'content' => "Text.\n\n## Alpha\n\nText.\n\n### Beta\n\nText.",
        'display_toc' => true,
    ]);

    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    
    // Should not see the collapsible initialization function
    $html = $response->getContent();
    expect(str_contains($html, 'initCollapsibleToc'))->toBe(false, 'initCollapsibleToc should not be present when collapsible is disabled');
});

it('includes localStorage support for TOC collapsed state', function () {
    config(['blogr.toc.collapsible' => true]);
    
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'content' => "Text.\n\n## Section\n\nContent.",
        'display_toc' => true,
    ]);

    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    
    $response->assertSee('blogr-toc-collapsed-state', false);
    $response->assertSee('localStorage.getItem', false);
    $response->assertSee('localStorage.setItem', false);
});

it('shows TOC title in sidebar when position is left or right', function () {
    config(['blogr.toc.position' => 'left']);
    
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'content' => "Text.\n\n## Section A\n\nText.\n\n## Section B\n\nText.",
        'display_toc' => true,
    ]);

    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    
    // TOC title should be visible
    $response->assertSee('Table of Contents');
    $response->assertSee('toc-sidebar-wrapper', false);
    
    // Test with right position
    config(['blogr.toc.position' => 'right']);
    $response2 = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    $response2->assertSee('Table of Contents');
});

