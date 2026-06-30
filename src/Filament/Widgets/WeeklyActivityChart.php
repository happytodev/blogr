<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Support\Carbon;

class WeeklyActivityChart extends ChartWidget
{
    protected ?string $heading = 'This Week\'s Activity';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = collect();
        $startOfWeek = Carbon::now()->startOfWeek();

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dayName = $date->format('D');

            $count = BlogPost::whereDate('created_at', $date->toDateString())->count();

            $data->push([
                'day' => $dayName,
                'count' => $count,
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Posts Created',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                    'hoverBackgroundColor' => 'rgba(59, 130, 246, 0.9)',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $data->pluck('day')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                    'grid' => [
                        'display' => true,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }
}
