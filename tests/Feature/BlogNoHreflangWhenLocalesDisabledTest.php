<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

it('does not emit hreflang tags when locales are disabled', function () {
    $post = BlogPost::factory()->create([
        'title' => 'Single Language Post',
        'slug' => 'single-language-post',
        'content' => 'Some content',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get("/blog/{$post->slug}");

    $response->assertStatus(200);
    $response->assertDontSee('hreflang', false);
});

it('does not emit hreflang tags on the blog index when locales are disabled', function () {
    $response = $this->get('/blog');

    $response->assertStatus(200);
    $response->assertDontSee('hreflang', false);
});
