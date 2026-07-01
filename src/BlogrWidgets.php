<?php

namespace Happytodev\Blogr;

use Happytodev\Blogr\Filament\Widgets\BlogPostsChart;
use Happytodev\Blogr\Filament\Widgets\BlogReadingStats;
use Happytodev\Blogr\Filament\Widgets\BlogStatsOverview;
use Happytodev\Blogr\Filament\Widgets\CategoryPostsChart;
use Happytodev\Blogr\Filament\Widgets\MissingSeoAlert;
use Happytodev\Blogr\Filament\Widgets\RecentBlogPosts;
use Happytodev\Blogr\Filament\Widgets\ScheduledPosts;
use Happytodev\Blogr\Filament\Widgets\SeriesStatsOverview;
use Happytodev\Blogr\Filament\Widgets\WeeklyActivityChart;

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
            CategoryPostsChart::class,
            SeriesStatsOverview::class,
            WeeklyActivityChart::class,
            MissingSeoAlert::class,
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
            CategoryPostsChart::class,
            WeeklyActivityChart::class,
        ];
    }

    /**
     * Get widget configuration for the widget picker
     */
    public static function widgetOptions(): array
    {
        return [
            BlogStatsOverview::class => 'Blog Stats Overview',
            RecentBlogPosts::class => 'Recent Blog Posts',
            ScheduledPosts::class => 'Scheduled Posts',
            BlogPostsChart::class => 'Posts Chart (12 months)',
            BlogReadingStats::class => 'Reading Time Stats',
            CategoryPostsChart::class => 'Posts per Category',
            SeriesStatsOverview::class => 'Series Overview',
            WeeklyActivityChart::class => 'Weekly Activity',
            MissingSeoAlert::class => 'SEO Checklist',
        ];
    }

    /**
     * Get enabled widgets based on config
     */
    public static function enabled(): array
    {
        $enabled = config('blogr.dashboard_widgets', []);

        if (empty($enabled)) {
            return self::core();
        }

        return array_intersect(self::all(), $enabled);
    }
}
