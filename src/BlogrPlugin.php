<?php

namespace Happytodev\Blogr;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;
use Happytodev\Blogr\Filament\Resources\BlogSeriesResource;
use Happytodev\Blogr\Filament\Resources\Categories\CategoryResource;
use Happytodev\Blogr\Filament\Resources\Tags\TagResource;
use Happytodev\Blogr\Filament\Widgets\BlogPostsChart;
use Happytodev\Blogr\Filament\Widgets\BlogReadingStats;
use Happytodev\Blogr\Filament\Widgets\BlogStatsOverview;
use Happytodev\Blogr\Filament\Widgets\RecentBlogPosts;
use Happytodev\Blogr\Filament\Widgets\ScheduledPosts;

class BlogrPlugin implements Plugin
{
    public function getId(): string
    {
        return 'blogr';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            BlogPostResource::class,
            BlogSeriesResource::class,
            CategoryResource::class,
            TagResource::class,
        ]);

        $panel->pages([
            BlogrSettings::class,
        ]);

        $panel->widgets([
            BlogStatsOverview::class,
            RecentBlogPosts::class,
            ScheduledPosts::class,
            BlogPostsChart::class,
            BlogReadingStats::class,
        ]);

        $panel->colors([
            'primary' => config('blogr.colors.primary', '#0ea5e9'),
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
