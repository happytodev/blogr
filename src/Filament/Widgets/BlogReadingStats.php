<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Support\Facades\Cache;

class BlogReadingStats extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = Cache::remember('blogr_reading_stats', 300, function () {
            $posts = BlogPost::where('is_published', true)->get();

            $totalReadingTime = 0;
            $count = $posts->count();
            $shortPosts = 0;
            $mediumPosts = 0;
            $longPosts = 0;

            foreach ($posts as $post) {
                $readingTime = $post->getEstimatedReadingTime();

                if (preg_match('/(\d+)/', $readingTime, $matches)) {
                    $minutes = (int) $matches[1];
                    $totalReadingTime += $minutes;

                    if ($minutes < 1) {
                        $shortPosts++;
                    } elseif ($minutes <= 5) {
                        $mediumPosts++;
                    } else {
                        $longPosts++;
                    }
                }
            }

            return [
                'average' => $count > 0 ? round($totalReadingTime / $count, 1) : 0,
                'short' => $shortPosts,
                'medium' => $mediumPosts,
                'long' => $longPosts,
            ];
        });

        return [
            Stat::make('Avg Reading Time', $stats['average'].' min')
                ->description('Per blog post')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),

            Stat::make('Short (< 1 min)', $stats['short'])
                ->description('Quick reads')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('success'),

            Stat::make('Medium (1-5 min)', $stats['medium'])
                ->description('Standard length')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            Stat::make('Long (> 5 min)', $stats['long'])
                ->description('In-depth content')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),
        ];
    }
}
