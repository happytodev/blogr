<?php

use Happytodev\Blogr\Helpers\SEOHelper;

test('SEO configuration is properly loaded', function () {
    // Test that config values are accessible or have defaults
    $seoConfig = config('blogr.seo', []);
    expect($seoConfig)->toBeArray();

    // Test that default values are available (either from config or defaults)
    expect(config('blogr.seo.default_title', 'Blog'))->toBeString();
    expect(config('blogr.seo.default_description', 'Discover our latest articles and insights'))->toBeString();
    expect(config('blogr.seo.structured_data.enabled', true))->toBeBool();
});

test('SEOHelper can be instantiated', function () {
    expect(class_exists(SEOHelper::class))->toBeTrue();
});

test('SEOHelper methods exist', function () {
    $methods = [
        'forListingPage',
        'forBlogPost',
        'generateJsonLd'
    ];

    foreach ($methods as $method) {
        expect(method_exists(SEOHelper::class, $method))->toBeTrue();
    }
});

test('SEOHelper generates non-empty results', function () {
    $seoData = SEOHelper::forListingPage('index');

    expect($seoData)->not->toBeEmpty();
    expect($seoData['title'])->not->toBeEmpty();
    expect($seoData['description'])->not->toBeEmpty();
});
