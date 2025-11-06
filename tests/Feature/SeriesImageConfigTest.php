<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogSeries;

it('has series configuration in config file', function () {
    expect(config('blogr.series.enabled'))->toBeTrue();
    expect(config('blogr.series.default_image'))->toBe('/vendor/blogr/images/default-series.svg');
});

it('returns default image url when series has no photo', function () {
    $series = BlogSeries::factory()->create([
        'photo' => null,
    ]);

    expect($series->photo_url)->toContain('default-series.svg');
});

it('returns storage url when series has photo', function () {
    $series = BlogSeries::factory()->create([
        'photo' => 'series-images/test-image.jpg',
    ]);

    expect($series->photo_url)->toContain('test-image.jpg');
});

it('uses custom default image from config', function () {
    config(['blogr.series.default_image' => '/custom/image.jpg']);
    
    $series = BlogSeries::factory()->create([
        'photo' => null,
    ]);

    expect($series->photo_url)->toContain('custom/image.jpg');
});

it('default svg image file exists in package', function () {
    $imagePath = __DIR__ . '/../../resources/images/default-series.svg';
    expect(file_exists($imagePath))->toBeTrue();
    expect(filesize($imagePath))->toBeGreaterThan(0);
});

it('series settings are included in blogr settings form', function () {
    $config = config('blogr');
    
    expect($config)->toHaveKey('series');
    expect($config['series'])->toHaveKey('enabled');
    expect($config['series'])->toHaveKey('default_image');
});

it('series default image path is configurable', function () {
    $defaultImage = config('blogr.series.default_image');
    
    expect($defaultImage)->not->toBeNull();
    expect($defaultImage)->toBeString();
    expect($defaultImage)->toContain('.svg');
});
