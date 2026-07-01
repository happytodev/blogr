<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Happytodev\Blogr\Models\Category;

class CategoryPostsChart extends ChartWidget
{
    protected ?string $heading = 'Posts per Category';

    protected int|string|array $columnSpan = '1/2';

    protected function getData(): array
    {
        $categories = Category::withCount('posts')->get();

        $colors = [
            'rgb(59, 130, 246)',
            'rgb(16, 185, 129)',
            'rgb(245, 158, 11)',
            'rgb(239, 68, 68)',
            'rgb(139, 92, 246)',
            'rgb(236, 72, 153)',
            'rgb(14, 165, 233)',
            'rgb(168, 85, 247)',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Posts',
                    'data' => $categories->pluck('posts_count')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, max(1, $categories->count())),
                    'borderColor' => 'rgb(255, 255, 255)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $categories->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 16,
                        'usePointStyle' => true,
                    ],
                ],
            ],
            'cutout' => '60%',
        ];
    }
}
