<?php

use Happytodev\Blogr\Filament\Components\IconPicker;
use Happytodev\Blogr\Helpers\IconHelper;

beforeEach(function () {
    IconHelper::flushCache();
});

it('getIconsWithSvg returns non-empty list with name and svg keys', function () {
    $picker = IconPicker::make('icon');

    $icons = $picker->getIconsWithSvg();

    expect($icons)->not->toBeEmpty();
    expect($icons[0])->toHaveKeys(['name', 'svg']);
    expect($icons[0]['svg'])->toContain('<svg');
});

it('getIconSvg returns SVG content for known icons', function () {
    $picker = IconPicker::make('icon');

    $svg = $picker->getIconSvg('academic-cap');

    expect($svg)->not->toBeNull()
        ->and($svg)->toContain('<svg');
});

it('getIconSvg returns null for unknown icon name', function () {
    $picker = IconPicker::make('icon');

    $svg = $picker->getIconSvg('nonexistent-icon-name');

    expect($svg)->toBeNull();
});

it('IconPicker PHP file has valid syntax', function () {
    $output = [];
    $returnCode = 0;
    exec('php -l '.escapeshellarg(__DIR__.'/../../src/Filament/Components/IconPicker.php').' 2>&1', $output, $returnCode);

    expect($returnCode)->toBe(0);
});
