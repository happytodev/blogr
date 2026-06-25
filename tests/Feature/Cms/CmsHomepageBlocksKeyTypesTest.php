<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\CmsWithLocalesTestCase;

use function Pest\Laravel\get;

uses(CmsWithLocalesTestCase::class);
uses()->group('cms', 'blocks');

test('homepage renders with sequential block keys', function () {
    $page = CmsPage::factory()->homepage()->create(['template' => 'landing']);
    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'blocks' => [
            ['type' => 'hero', 'data' => ['title' => 'Welcome']],
            ['type' => 'blog_posts', 'data' => ['heading' => 'Blog']],
        ],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
    $response->assertSee('Welcome');
    $response->assertSee('Blog');
});

test('homepage renders when blocks data has non-numeric string keys', function () {
    $page = CmsPage::factory()->homepage()->create(['template' => 'landing']);
    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
    ]);

    // Simulate blocks stored as an associative array with non-numeric keys
    // This can happen when the JSON in the database is an object with named keys
    $translation->update([
        'blocks' => [
            'main_hero' => ['type' => 'hero', 'data' => ['title' => 'Welcome']],
            'blog_section' => ['type' => 'blog_posts', 'data' => ['heading' => 'Latest']],
        ],
    ]);

    $response = get('/en');

    $response->assertStatus(200);
});
