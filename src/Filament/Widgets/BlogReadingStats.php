<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Happytodev\Blogr\Models\BlogPost;

class BlogReadingStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Get posts with reading time data
        $postsWithReadingTime = BlogPost::where('is_published', true)->get();

        $totalReadingTime = 0;
        $averageReadingTime = 0;
        $shortPosts = 0; // < 1 minute
        $mediumPosts = 0; // 1-5 minutes
        $longPosts = 0; // > 5 minutes

        if ($postsWithReadingTime->isNotEmpty()) {
            foreach ($postsWithReadingTime as $post) {
                $readingTime = $post->getEstimatedReadingTime();

                // Extract numeric value from reading time string
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

            $averageReadingTime = round($totalReadingTime / $postsWithReadingTime->count(), 1);
        }

        return [
            Stat::make('Average Reading Time', $averageReadingTime . ' min')
                ->description('Per blog post')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),

            Stat::make('Short Posts (< 1 min)', $shortPosts)
                ->description('Quick reads')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('success'),

            Stat::make('Medium Posts (1-5 min)', $mediumPosts)
                ->description('Standard length')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            Stat::make('Long Posts (> 5 min)', $longPosts)
                ->description('In-depth content')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),
        ];
    }
}
