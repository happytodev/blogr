<?php

use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);

beforeEach(function () {
    config(['blogr.route.frontend.enabled' => true]);
    config(['blogr.cms.enabled' => true]);
});

test('hero renders button with external URL link type', function () {
    $data = [
        'title' => 'Welcome',
        'subtitle' => 'Get started today',
        'cta_text' => 'Start Now',
        'cta_link_type' => 'external',
        'cta_url' => 'https://example.com',
    ];

    $html = View::make('blogr::components.blocks.hero', ['data' => $data])->render();

    expect($html)
        ->toContain('Start Now')
        ->toContain('href="https://example.com"');
});

test('hero renders button with blog link type', function () {
    $data = [
        'title' => 'Welcome',
        'subtitle' => 'Read our blog',
        'cta_text' => 'View Blog',
        'cta_link_type' => 'blog',
    ];

    $html = view('blogr::components.blocks.hero', ['data' => $data])->render();

    expect($html)
        ->toContain('View Blog')
        ->toMatch('/href="[^"]*"/');
});

test('hero renders button with category link type', function () {
    $category = Category::factory()->create();
    $category->translations()->create([
        'locale' => 'en',
        'name' => 'Tech',
        'slug' => 'tech',
    ]);

    $data = [
        'title' => 'Welcome',
        'subtitle' => 'Browse our tech category',
        'cta_text' => 'View Tech Posts',
        'cta_link_type' => 'category',
        'cta_category_id' => $category->id,
    ];

    $html = View::make('blogr::components.blocks.hero', ['data' => $data])->render();

    expect($html)
        ->toContain('View Tech Posts')
        ->toMatch('/href="[^"]*"/');
});

test('hero renders button with cms_page link type', function () {
    $page = CmsPage::factory()->create();
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'About Us',
        'slug' => 'about',
        'content' => 'About content',
        'blocks' => [],
    ]);

    $data = [
        'title' => 'Welcome',
        'subtitle' => 'Learn more about us',
        'cta_text' => 'About Us',
        'cta_link_type' => 'cms_page',
        'cta_cms_page_id' => $page->id,
    ];

    $html = View::make('blogr::components.blocks.hero', ['data' => $data])->render();

    expect($html)
        ->toContain('About Us')
        ->toMatch('/href="[^"]*"/');
});

test('hero does not render button when cta_text is empty', function () {
    $data = [
        'title' => 'Welcome',
        'subtitle' => 'No button here',
        'cta_link_type' => 'external',
        'cta_url' => 'https://example.com',
    ];

    $html = View::make('blogr::components.blocks.hero', ['data' => $data])->render();

    expect($html)
        ->not->toContain('<a')
        ->not->toContain('href="https://example.com"');
});

test('hero does not render button when link cannot be resolved', function () {
    $data = [
        'title' => 'Welcome',
        'subtitle' => 'Invalid link',
        'cta_text' => 'Click Me',
        'cta_link_type' => 'category',
        'cta_category_id' => 99999, // Non-existent category
    ];

    $html = View::make('blogr::components.blocks.hero', ['data' => $data])->render();

    expect($html)
        ->not->toContain('Click Me')
        ->not->toContain('<a');
});

test('hero renders without button when both cta_text and url are missing', function () {
    $data = [
        'title' => 'Welcome',
        'subtitle' => 'Just text, no button',
    ];

    $html = View::make('blogr::components.blocks.hero', ['data' => $data])->render();

    expect($html)
        ->toContain('Welcome')
        ->toContain('Just text, no button')
        ->not->toContain('<a');
});
