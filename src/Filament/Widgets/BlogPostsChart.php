<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Support\Carbon;

class BlogPostsChart extends ChartWidget
{
    protected int|string|array $columnSpan = '2/3';

    protected function getData(): array
    {
        $months = collect();
        $createdData = collect();
        $publishedData = collect();

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months->push($date->format('M Y'));

            $createdData->push(
                BlogPost::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count()
            );

            $publishedData->push(
                BlogPost::where('is_published', true)
                    ->whereYear('published_at', $date->year)
                    ->whereMonth('published_at', $date->month)
                    ->count()
            );
        }

        return [
            'datasets' => [
                [
                    'label' => 'Created',
                    'data' => $createdData->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Published',
                    'data' => $publishedData->toArray(),
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
