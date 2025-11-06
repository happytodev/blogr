<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\TestCase;
use function Pest\Laravel\get;

uses(TestCase::class);

beforeEach(function () {
    // Configure blog as homepage (NOT CMS)
    config(['blogr.homepage.type' => 'blog']);
    config(['blogr.route.homepage' => true]);
    config(['blogr.cms.enabled' => true]);
});

test('CMS homepage route not registered when blog is configured as homepage', function () {
    $page = CmsPage::factory()->homepage()->create();
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'CMS Home',
        'slug' => 'cms-home',
        'content' => 'CMS Homepage',
    ]);
    
    // Root should show blog, not CMS
    $response = get('/');
    
    // Should get blog index (200) or 404 if no posts, but NOT CMS content
    expect($response->status())->toBeIn([200, 404]);
    $response->assertDontSee('CMS Homepage');
});
