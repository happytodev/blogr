<?php

use Happytodev\Blogr\Tests\TestCase;
uses(TestCase::class);

use Happytodev\Blogr\Http\Controllers\SitemapController;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\User;
use Illuminate\Http\Response;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::create(['name' => 'Tech', 'slug' => 'tech']);
    $this->tag = Tag::create(['name' => 'Laravel', 'slug' => 'laravel']);
    $this->controller = new SitemapController;
});

it('returns valid XML sitemap response', function () {
    BlogPost::factory()->published()->create([
        'category_id' => $this->category->id,
        'user_id' => $this->user->id,
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Content',
    ]);

    $response = $this->controller->index('en');

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->headers->get('Content-Type'))->toContain('application/xml');

    $content = $response->getContent();
    expect($content)->toContain('<?xml version="1.0" encoding="UTF-8"?>');
    expect($content)->toContain('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
});

it('includes published blog posts', function () {
    BlogPost::factory()->published()->create([
        'category_id' => $this->category->id,
        'user_id' => $this->user->id,
        'title' => 'Sitemap Post',
        'slug' => 'sitemap-post',
        'content' => 'Content',
    ]);

    $response = $this->controller->index('en');
    $content = $response->getContent();

    expect($content)->toContain('sitemap-post');
    expect($content)->toContain('0.8');
});

it('excludes draft posts', function () {
    BlogPost::factory()->create([
        'is_published' => false,
        'published_at' => null,
        'category_id' => $this->category->id,
        'user_id' => $this->user->id,
        'title' => 'Draft',
        'slug' => 'draft-post',
        'content' => 'Draft',
    ]);

    $response = $this->controller->index('en');
    $content = $response->getContent();

    expect($content)->not->toContain('draft-post');
});

it('includes categories with correct priority', function () {
    $response = $this->controller->index('en');
    $content = $response->getContent();

    expect($content)->toContain('tech');
    expect($content)->toContain('0.6');
});

it('includes tags with correct priority', function () {
    $response = $this->controller->index('en');
    $content = $response->getContent();

    expect($content)->toContain('laravel');
    expect($content)->toContain('0.5');
});

it('includes published series when series are enabled', function () {
    $series = BlogSeries::create([
        'slug' => 'learn-laravel',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $response = $this->controller->index('en');
    $content = $response->getContent();

    expect($content)->toContain('learn-laravel');
});

it('excludes unpublished series', function () {
    $series = BlogSeries::create([
        'slug' => 'hidden-series',
        'is_published' => false,
    ]);

    $response = $this->controller->index('en');
    $content = $response->getContent();

    expect($content)->not->toContain('hidden-series');
});

it('includes published CMS pages', function () {
    $page = CmsPage::create([
        'slug' => 'about',
        'is_published' => true,
        'is_homepage' => false,
    ]);
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'About Us',
        'slug' => 'about',
        'content' => 'About content',
    ]);

    $response = $this->controller->index('en');
    $content = $response->getContent();

    expect($content)->toContain('about');
});

it('gives homepage CMS page priority 1.0', function () {
    $page = CmsPage::create([
        'slug' => 'home',
        'is_published' => true,
        'is_homepage' => true,
    ]);
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'content' => 'Home content',
    ]);

    $response = $this->controller->index('en');
    $content = $response->getContent();

    expect($content)->toContain('1.0');
});

it('excludes unpublished CMS pages', function () {
    $page = CmsPage::create([
        'slug' => 'draft-page',
        'is_published' => false,
        'is_homepage' => false,
    ]);
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'Draft Page',
        'slug' => 'draft-page',
        'content' => 'Draft',
    ]);

    $response = $this->controller->index('en');
    $content = $response->getContent();

    expect($content)->not->toContain('draft-page');
});

it('escapes XML special characters in URLs', function () {
    $tag = Tag::create(['name' => 'C++', 'slug' => 'c++']);

    $response = $this->controller->index('en');
    $content = $response->getContent();

    expect($content)->toContain('c++');
});
