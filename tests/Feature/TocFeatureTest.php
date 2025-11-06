<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
});

it('displays TOC when enabled on post', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'content' => "Introduction.\n\n## First Section\n\nContent here.\n\n## Second Section\n\nMore content.\n\n### Subsection\n\nDetailed info.",
        'display_toc' => true,
    ]);

    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    
    $response->assertSee('Table of Contents');
    $response->assertSee('First Section');
    $response->assertSee('Second Section');
});

it('does not display TOC when disabled on post', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'content' => "## Section One\n\nContent.\n\n## Section Two\n\nMore content.",
        'display_toc' => false,
    ]);

    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    
    // TOC should not be in the article content
    $html = $response->getContent();
    expect(str_contains($html, '<nav') && str_contains($html, 'class="toc'))->toBe(false, 'TOC nav element should not exist');
});

it('TOC includes proper CSS classes', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'content' => "Intro.\n\n## Heading One\n\nText.\n\n## Heading Two\n\nText.",
        'display_toc' => true,
    ]);

    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    
    // Should have TOC base class
    $response->assertSee('class="toc', false);
    // Should have position class (center is default)
    $response->assertSee('blogr-toc-', false);
});

it('TOC view has required CSS styling', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'content' => "Text.\n\n## One\n\nText.\n\n## Two\n\nText.",
        'display_toc' => true,
    ]);

    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    
    // Check CSS definitions exist
    $response->assertSee('.toc {', false);
    $response->assertSee('.blogr-toc-center', false);
    $response->assertSee('.blogr-toc-sidebar', false);
});

it('TOC contains all section headings', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'content' => "Intro.\n\n## Alpha\n\nText.\n\n## Beta\n\nText.\n\n### Gamma\n\nText.\n\n## Delta\n\nText.",
        'display_toc' => true,
    ]);

    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    
    $response->assertSee('Alpha');
    $response->assertSee('Beta');
    $response->assertSee('Gamma');
    $response->assertSee('Delta');
});

it('shows TOC title in sidebar when position is left or right', function () {
    // Test with left sidebar
    config(['blogr.toc.position' => 'left']);
    
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'content' => "Introduction text.\n\n## First Section\n\nContent here.\n\n## Second Section\n\nMore content.",
        'display_toc' => true,
    ]);

    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    
    // TOC title should be visible in sidebar
    $response->assertSee('Table of Contents');
    $response->assertSee('First Section');
    $response->assertSee('toc-sidebar-wrapper', false);
    
    // Test with right sidebar
    config(['blogr.toc.position' => 'right']);
    $response2 = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    $response2->assertSee('Table of Contents');
    $response2->assertSee('First Section');
});

it('shows TOC title when position is center (inline)', function () {
    // Set TOC position to center (default inline behavior)
    config(['blogr.toc.position' => 'center']);
    
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'content' => "Introduction text.\n\n## Section One\n\nContent here.\n\n## Section Two\n\nMore content.",
        'display_toc' => true,
    ]);

    $response = get(route('blog.show', ['locale' => 'en', 'slug' => $post->slug]));
    
    // When centered, the TOC title should be visible in the content
    $response->assertSee('Table of Contents');
    $response->assertSee('Section One');
    $response->assertSee('Section Two');
});
