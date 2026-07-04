<?php

use Happytodev\Blogr\Helpers\ColorHelper;
use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

test('feature_color_contrast_black_on_white_passes_aa', function () {
    expect(ColorHelper::passesAA('#000000', '#ffffff'))->toBeTrue();
});

test('feature_color_contrast_white_on_black_passes_aa', function () {
    expect(ColorHelper::passesAA('#ffffff', '#000000'))->toBeTrue();
});

test('feature_color_contrast_light_gray_on_white_fails_aa', function () {
    expect(ColorHelper::passesAA('#cccccc', '#ffffff'))->toBeFalse();
});

test('feature_color_contrast_gray_on_white_passes_large_but_not_aa', function () {
    expect(ColorHelper::passesAALarge('#808080', '#ffffff'))->toBeTrue()
        ->and(ColorHelper::passesAA('#808080', '#ffffff'))->toBeFalse();
});

test('feature_color_contrast_same_color_returns_1', function () {
    expect(ColorHelper::contrastRatio('#ff0000', '#ff0000'))->toBe(1.0);
});

test('feature_color_contrast_ratio_calculation', function () {
    $ratio = ColorHelper::contrastRatio('#000000', '#ffffff');
    expect($ratio)->toBeGreaterThan(20.0);
});

test('feature_relative_luminance_black_is_zero', function () {
    expect(ColorHelper::relativeLuminance('#000000'))->toBe(0.0);
});

test('feature_relative_luminance_white_is_one', function () {
    expect(ColorHelper::relativeLuminance('#ffffff'))->toBe(1.0);
});
