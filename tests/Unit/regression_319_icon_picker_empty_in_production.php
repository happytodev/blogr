<?php

use Happytodev\Blogr\Helpers\IconHelper;

beforeEach(function () {
    IconHelper::flushCache();
});

it('resolves the icon directory via base_path first (production path)', function () {
    $directory = IconHelper::resolveSvgDirectory();

    expect($directory)->toStartWith(base_path())
        ->and(is_dir($directory))->toBeTrue();
});

it('finds heroicons when using base_path (production path)', function () {
    $icons = IconHelper::outlineIcons();

    expect($icons)->not->toBeEmpty()
        ->and($icons)->toHaveKey('academic-cap');
});

it('can retrieve SVG content for a known icon', function () {
    $svg = IconHelper::getSvg('academic-cap');

    expect($svg)->not->toBeNull()
        ->and($svg)->toContain('<svg');
});

it('returns null for an unknown icon', function () {
    $svg = IconHelper::getSvg('nonexistent-icon');

    expect($svg)->toBeNull();
});
