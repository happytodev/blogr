<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;

class SeriesStatsOverview extends BaseWidget
{
    protected ?string $heading = 'Series Overview';

    protected function getStats(): array
    {
        $totalSeries = BlogSeries::count();
        $featuredSeries = BlogSeries::where('is_featured', true)->count();
        $postsInSeries = BlogPost::whereNotNull('blog_series_id')->count();
        $avgPostsPerSeries = $totalSeries > 0
            ? round($postsInSeries / $totalSeries, 1)
            : 0;

        return [
            Stat::make('Total Series', $totalSeries)
                ->description('All blog series')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color('primary'),

            Stat::make('Featured Series', $featuredSeries)
                ->description('Highlighted on site')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make('Posts in Series', $postsInSeries)
                ->description('Organized content')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),

            Stat::make('Avg Posts / Series', $avgPostsPerSeries)
                ->description('Average per series')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }
}
