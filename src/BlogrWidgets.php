<?php

namespace Happytodev\Blogr;

use Happytodev\Blogr\Filament\Widgets\BlogPostsChart;
use Happytodev\Blogr\Filament\Widgets\BlogReadingStats;
use Happytodev\Blogr\Filament\Widgets\BlogStatsOverview;
use Happytodev\Blogr\Filament\Widgets\RecentBlogPosts;
use Happytodev\Blogr\Filament\Widgets\ScheduledPosts;

class BlogrWidgets
{
    /**
     * Get all available blog widgets
     */
    public static function all(): array
    {
        return [
            BlogStatsOverview::class,
            RecentBlogPosts::class,
            ScheduledPosts::class,
            BlogPostsChart::class,
            BlogReadingStats::class,
        ];
    }

    /**
     * Get core blog widgets (recommended for most users)
     */
    public static function core(): array
    {
        return [
            BlogStatsOverview::class,
            RecentBlogPosts::class,
            ScheduledPosts::class,
        ];
    }

    /**
     * Get analytics widgets (charts and statistics)
     */
    public static function analytics(): array
    {
        return [
            BlogPostsChart::class,
            BlogReadingStats::class,
        ];
    }
}
