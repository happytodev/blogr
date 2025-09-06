<?php

use Happytodev\Blogr\Helpers\SEOHelper;

test('SEO configuration is properly loaded', function () {
    // Test that config values are accessible
    expect(config('blogr.seo'))->toBeArray();
    expect(config('blogr.seo.default_title'))->toBeString();
    expect(config('blogr.seo.default_description'))->toBeString();
    expect(config('blogr.seo.structured_data.enabled'))->toBeBool();
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
