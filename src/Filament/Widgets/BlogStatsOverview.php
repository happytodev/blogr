<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\Tag;
use Illuminate\Support\Facades\Cache;

class BlogStatsOverview extends BaseWidget
{
    protected array|int|null $columns = 4;

    protected function getStats(): array
    {
        $stats = Cache::remember('blogr_dashboard_stats', 300, function () {
            return [
                'totalPosts' => BlogPost::count(),
                'publishedPosts' => BlogPost::where('is_published', true)->count(),
                'draftPosts' => BlogPost::where('is_published', false)->count(),
                'scheduledPosts' => BlogPost::where('is_published', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '>', now())
                    ->count(),
                'totalCategories' => Category::count(),
                'totalTags' => Tag::count(),
                'totalSeries' => BlogSeries::count(),
            ];
        });

        $statsCards = [
            Stat::make('Total Posts', $stats['totalPosts'])
                ->description('All blog posts')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Published', $stats['publishedPosts'])
                ->description('Live on website')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),

            Stat::make('Drafts', $stats['draftPosts'])
                ->description('Work in progress')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('warning'),

            Stat::make('Scheduled', $stats['scheduledPosts'])
                ->description('Future publications')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Categories', $stats['totalCategories'])
                ->description('Post categories')
                ->descriptionIcon('heroicon-m-folder')
                ->color('gray'),

            Stat::make('Tags', $stats['totalTags'])
                ->description('Post tags')
                ->descriptionIcon('heroicon-m-tag')
                ->color('gray'),

            Stat::make('Series', $stats['totalSeries'])
                ->description('Blog series')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color('primary'),
        ];

        if (config('blogr.cms.enabled', false)) {
            $cmsCount = Cache::remember('blogr_dashboard_cms_count', 300, function () {
                return CmsPage::count();
            });

            $statsCards[] = Stat::make('CMS Pages', $cmsCount)
                ->description('Static pages')
                ->descriptionIcon('heroicon-m-document')
                ->color('info');
        }

        return $statsCards;
    }
}
