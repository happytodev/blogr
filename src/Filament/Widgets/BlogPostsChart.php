<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Support\Carbon;

class BlogPostsChart extends ChartWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = collect();

        // Get data for the last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');

            $count = BlogPost::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $data->push([
                'month' => $monthName,
                'count' => $count,
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Blog Posts Created',
                    'data' => $data->pluck('count')->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->pluck('month')->toArray(),
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
                    'display' => true,
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
