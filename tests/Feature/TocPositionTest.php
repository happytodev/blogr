<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Illuminate\Support\Facades\Config;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
    $this->post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'content' => "Introduction paragraph.\n\n## First Section\n\nContent for the first section with some text.\n\n## Second Section\n\nContent for the second section.\n\n### Subsection\n\nMore detailed information in a subsection.\n\n## Third Section\n\nFinal section content.",
        'display_toc' => true,
    ]);
});

it('displays TOC in center position by default (inline with content)', function () {
    Config::set('blogr.toc.position', 'center');
    
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'content' => '## Heading 1\n\nContent\n\n## Heading 2\n\nMore content',
        'display_toc' => true,
    ]);

    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    
    $response->assertSee('Table of Contents');
    $response->assertSee('blogr-toc-center', false);
    $response->assertDontSee('<aside', false);
});

it('generates correct CSS classes for TOC positioning', function () {
    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $this->post->slug]));
    
    $response->assertSee('class="toc blogr-toc-', false);
    $response->assertSee('First Section');
    $response->assertSee('Second Section');
    $response->assertSee('Subsection');
});

it('TOC contains links to all sections', function () {
    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $this->post->slug]));
    
    $response->assertSee('First Section');
    $response->assertSee('Second Section');
    $response->assertSee('Third Section');
    $response->assertSee('Subsection');
});

it('respects display_toc setting', function () {
    // Disable strict mode to allow per-post TOC control
    Config::set('blogr.toc.strict_mode', false);
    Config::set('blogr.toc.enabled', true);
    
    $postWithoutToc = BlogPost::factory()->create([
        'user_id' => User::factory()->create()->id,
        'category_id' => Category::factory()->create()->id,
        'is_published' => true,
        'published_at' => now(),
        'title' => 'Post Without TOC',
        'slug' => 'post-without-toc-unique-' . time(),
        'content' => "# Main Title\n\nIntroduction text here.\n\n## Section One\n\nContent for section one.\n\n## Section Two\n\nMore content in section two.",
        'display_toc' => false,
    ]);
    
    // Verify the post settings
    $postWithoutToc->refresh();
    expect($postWithoutToc->display_toc)->toBeFalse()
        ->and($postWithoutToc->shouldDisplayToc())->toBeFalse();

    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $postWithoutToc->slug]));
    
    $response->assertStatus(200);
    
    // Check that TOC-related HTML elements don't appear in the body content
    $content = $response->getContent();
    expect($content)->not->toContain('<ul class="toc"')
        ->and($content)->not->toContain('class="toc-mobile-container"')
        ->and($content)->not->toContain('<aside class="toc-sidebar-wrapper">');
});
