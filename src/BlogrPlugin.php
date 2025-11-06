<?php

namespace Happytodev\Blogr;

use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;
use Happytodev\Blogr\Filament\Resources\BlogSeriesResource;
use Happytodev\Blogr\Filament\Resources\Categories\CategoryResource;
use Happytodev\Blogr\Filament\Resources\CmsPageResource;
use Happytodev\Blogr\Filament\Resources\Tags\TagResource;
use Happytodev\Blogr\Filament\Widgets\BlogPostsChart;
use Happytodev\Blogr\Filament\Widgets\BlogReadingStats;
use Happytodev\Blogr\Filament\Widgets\BlogStatsOverview;
use Happytodev\Blogr\Filament\Widgets\RecentBlogPosts;
use Happytodev\Blogr\Filament\Widgets\ScheduledPosts;

class BlogrPlugin implements Plugin
{
    public function getId(): string
    {
        return 'blogr';
    }

    public function register(Panel $panel): void
    {
        $resources = [
            BlogPostResource::class,
            BlogSeriesResource::class,
            CategoryResource::class,
            TagResource::class,
        ];

        // Ajouter la ressource CMS si activée
        if (config('blogr.cms.enabled', false)) {
            $resources[] = CmsPageResource::class;
        }

        $panel->resources($resources);

        $panel->pages([
            BlogrSettings::class,
        ]);

        $panel->widgets([
            BlogStatsOverview::class,
            RecentBlogPosts::class,
            ScheduledPosts::class,
            BlogPostsChart::class,
            BlogReadingStats::class,
        ]);

        // Add a navigation item to quickly view the website (translation key used)
        $panel->navigationItems([
            NavigationItem::make('view-website')
                ->label(__('blogr::navigation.view_website'))
                ->url(fn (): string => config('app.url', '/'), shouldOpenInNewTab: true)
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->sort(1),
        ]);

        $panel->colors([
            'primary' => config('blogr.colors.primary', '#0ea5e9'),
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
