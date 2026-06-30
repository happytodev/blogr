<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Happytodev\Blogr\Models\CmsPage;

class CmsStatsOverview extends BaseWidget
{
    protected ?string $heading = 'CMS Overview';

    protected function getStats(): array
    {
        $totalPages = CmsPage::count();
        $publishedPages = CmsPage::where('is_published', true)->count();
        $draftPages = CmsPage::where('is_published', false)->count();
        $homepage = CmsPage::where('is_homepage', true)->exists();

        return [
            Stat::make('Total Pages', $totalPages)
                ->description('All CMS pages')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Published Pages', $publishedPages)
                ->description('Live on website')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),

            Stat::make('Draft Pages', $draftPages)
                ->description('Work in progress')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('warning'),

            Stat::make('Homepage Set', $homepage ? 'Yes' : 'No')
                ->description($homepage ? 'Homepage is configured' : 'No page is set as homepage')
                ->descriptionIcon($homepage ? 'heroicon-m-home' : 'heroicon-m-home-modern')
                ->color($homepage ? 'success' : 'danger'),
        ];
    }

    public static function canView(): bool
    {
        return config('blogr.cms.enabled', false);
    }
}
