<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



it('has default series image configured', function () {
    $defaultImage = config('blogr.series.default_image');
    
    expect($defaultImage)->not->toBeNull()
        ->and($defaultImage)->toBeString();
});

it('can set custom default series image', function () {
    config(['blogr.series.default_image' => '/storage/blogr/series/my-default.jpg']);
    
    expect(config('blogr.series.default_image'))->toBe('/storage/blogr/series/my-default.jpg');
});

it('falls back to vendor image when no custom image set', function () {
    config(['blogr.series.default_image' => '/vendor/blogr/images/default-series.svg']);
    
    $image = config('blogr.series.default_image');
    
    expect($image)->toContain('vendor/blogr');
});
