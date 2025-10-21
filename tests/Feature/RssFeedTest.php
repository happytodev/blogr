<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Http\Controllers\RssFeedController;
use Illuminate\Http\Response;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::create(['name' => 'Tech', 'slug' => 'tech']);
    $this->controller = new RssFeedController();
});

it('RSS controller returns valid XML response', function () {
    $post = BlogPost::factory()->published()->create([
        'category_id' => $this->category->id,
        'user_id' => $this->user->id,
        'title' => 'Test RSS Post',
        'slug' => 'test-rss-post',
        'content' => 'This is test content for RSS feed',
    ]);
    
    $response = $this->controller->index('en');
    
    expect($response)->toBeInstanceOf(Response::class);
    expect($response->headers->get('Content-Type'))->toContain('application/rss+xml');
    
    $content = $response->getContent();
    expect($content)->toContain('<?xml version="1.0" encoding="UTF-8"?>');
    expect($content)->toContain('<rss version="2.0"');
    expect($content)->toContain('Test RSS Post');
});

it('RSS feed includes post details', function () {
    $post = BlogPost::factory()->published()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'title' => 'My Awesome Post',
        'slug' => 'my-awesome-post',
        'content' => 'Awesome content here',
    ]);
    
    $response = $this->controller->index('en');
    $content = $response->getContent();
    
    expect($content)->toContain('My Awesome Post');
    expect($content)->toContain($this->user->name);
    expect($content)->toContain($this->category->name);
});

it('RSS feed filters by category', function () {
    $techPost = BlogPost::factory()->published()->create([
        'category_id' => $this->category->id,
        'user_id' => $this->user->id,
        'title' => 'Tech Post',
        'slug' => 'tech-post',
        'content' => 'Tech content',
    ]);
    
    $newsCategory = Category::create(['name' => 'News', 'slug' => 'news']);
    $newsPost = BlogPost::factory()->published()->create([
        'category_id' => $newsCategory->id,
        'user_id' => $this->user->id,
        'title' => 'News Post',
        'slug' => 'news-post',
        'content' => 'News content',
    ]);
    
    $response = $this->controller->category('en', 'tech');
    $content = $response->getContent();
    
    expect($content)->toContain('Tech Post');
    expect($content)->not->toContain('News Post');
    expect($content)->toContain('Tech');
});

it('RSS feed only includes published posts', function () {
    $publishedPost = BlogPost::factory()->published()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'title' => 'Published Post',
        'slug' => 'published-post',
        'content' => 'Published content',
    ]);
    
    $draftPost = BlogPost::factory()->create([
        'is_published' => false,
        'published_at' => null,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'title' => 'Draft Post',
        'slug' => 'draft-post',
        'content' => 'Draft content',
    ]);
    
    $response = $this->controller->index('en');
    $content = $response->getContent();
    
    expect($content)->toContain('Published Post');
    expect($content)->not->toContain('Draft Post');
});

it('RSS feed respects items limit', function () {
    config()->set('blogr.rss.items_limit', 2);
    
    for ($i = 1; $i <= 5; $i++) {
        BlogPost::factory()->published()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'published_at' => now()->subDays($i),
            'title' => "Post $i",
            'slug' => "post-$i",
            'content' => "Content $i",
        ]);
    }
    
    $response = $this->controller->index('en');
    $content = $response->getContent();
    
    $itemCount = substr_count($content, '<item>');
    expect($itemCount)->toBeLessThanOrEqual(2);
});

it('RSS feed escapes XML special characters', function () {
    $post = BlogPost::factory()->published()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'title' => 'Post with <html> & "quotes"',
        'slug' => 'post-with-special-chars',
        'content' => 'Content with & < > characters',
    ]);
    
    $response = $this->controller->index('en');
    $content = $response->getContent();
    
    expect($content)->toContain('&lt;html&gt;');
    expect($content)->toContain('&amp;');
    expect($content)->toContain('&quot;');
});
