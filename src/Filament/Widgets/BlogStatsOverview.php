<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;

class BlogStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalPosts = BlogPost::count();
        $publishedPosts = BlogPost::where('is_published', true)->count();
        $draftPosts = BlogPost::where('is_published', false)->count();
        $scheduledPosts = BlogPost::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '>', now())
            ->count();

        $totalCategories = Category::count();
        $totalTags = Tag::count();

        return [
            Stat::make('Total Posts', $totalPosts)
                ->description('All blog posts')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Published Posts', $publishedPosts)
                ->description('Live on website')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),

            Stat::make('Draft Posts', $draftPosts)
                ->description('Work in progress')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('warning'),

            Stat::make('Scheduled Posts', $scheduledPosts)
                ->description('Future publications')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Categories', $totalCategories)
                ->description('Post categories')
                ->descriptionIcon('heroicon-m-folder')
                ->color('gray'),

            Stat::make('Tags', $totalTags)
                ->description('Post tags')
                ->descriptionIcon('heroicon-m-tag')
                ->color('gray'),
        ];
    }
}
