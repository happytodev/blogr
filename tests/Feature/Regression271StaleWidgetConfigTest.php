<?php

use Filament\Panel;
use Happytodev\Blogr\BlogrPlugin;
use Happytodev\Blogr\BlogrWidgets;
use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

it('returns raw config from enabled() including stale widget classes', function () {
    config()->set('blogr.dashboard_widgets', [
        'Happytodev\Blogr\Filament\Widgets\QuickVisitSite',
        'Happytodev\Blogr\Filament\Widgets\BlogStatsOverview',
    ]);

    $enabled = BlogrWidgets::enabled();

    expect($enabled)->toContain('Happytodev\Blogr\Filament\Widgets\QuickVisitSite');
    expect($enabled)->toContain('Happytodev\Blogr\Filament\Widgets\BlogStatsOverview');
});

it('does not crash when registering with stale widget config', function () {
    config()->set('blogr.dashboard_widgets', [
        'Happytodev\Blogr\Filament\Widgets\QuickVisitSite',
    ]);

    $panel = Panel::make();
    $plugin = BlogrPlugin::make();

    $plugin->register($panel);

    $widgets = $panel->getWidgets();

    expect($widgets)->not->toContain('Happytodev\Blogr\Filament\Widgets\QuickVisitSite');
});

it('falls back to core widgets when dashboard_widgets config is empty', function () {
    config()->set('blogr.dashboard_widgets', []);

    $enabled = BlogrWidgets::enabled();

    expect($enabled)->toBe(BlogrWidgets::core());
});
