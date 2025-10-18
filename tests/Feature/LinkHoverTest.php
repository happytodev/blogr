<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Models\Category;

test('links in post content have proper hover styles', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
        'display_toc' => false,
    ]);

    $post->translations()->create([
        'locale' => 'en',
        'title' => 'Test Post with Links',
        'slug' => 'test-post-with-links',
        'content' => "This is a paragraph with [a link](https://example.com).\n\n## Section\n\nAnother paragraph with [another link](https://example.org).",
        'excerpt' => 'Test post',
    ]);

    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post-with-links']));

    $response->assertStatus(200);
    
    // Check that links are present in the content
    $response->assertSee('href="https://example.com"', false);
    $response->assertSee('href="https://example.org"', false);
    
    // Check that the CSS for link hover is present
    $content = $response->getContent();
    
    // Verify that hover styles exist for links
    expect($content)->toContain('.prose p a:hover')
        ->and($content)->toContain('.prose li a:hover')
        ->and($content)->toContain('text-decoration: underline');
});

test('links in lists have proper hover styles', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
        'display_toc' => false,
    ]);

    $post->translations()->create([
        'locale' => 'en',
        'title' => 'Test Post with List Links',
        'slug' => 'test-post-with-list-links',
        'content' => "Here is a list:\n\n- Item 1 with [link one](https://example.com)\n- Item 2 with [link two](https://example.org)\n- Item 3",
        'excerpt' => 'Test post with lists',
    ]);

    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post-with-list-links']));

    $response->assertStatus(200);
    
    // Check that links are present
    $response->assertSee('href="https://example.com"', false);
    $response->assertSee('href="https://example.org"', false);
    
    // Verify hover styles
    $content = $response->getContent();
    expect($content)->toContain('.prose li a:hover');
});
