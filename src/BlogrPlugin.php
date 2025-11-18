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
use Happytodev\Blogr\Filament\Widgets\QuickVisitSite;
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
            QuickVisitSite::class,
            RecentBlogPosts::class,
            ScheduledPosts::class,
            BlogPostsChart::class,
            BlogReadingStats::class,
        ]);

        // Add a navigation item to quickly view the website
        $panel->navigationItems([
            NavigationItem::make('view-website')
                ->label(fn (): string => __('blogr::ui.view_website'))
                ->url(fn (): string => $this->getWebsiteUrl(), shouldOpenInNewTab: true)
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->sort(1),
        ]);

        $panel->colors([
            'primary' => config('blogr.colors.primary', '#0ea5e9'),
        ]);
    }

    private function getWebsiteUrl(): string
    {
        try {
            $localesEnabled = config('blogr.locales.enabled', false);
            $homepageType = config('blogr.homepage.type', 'blog');

            if ($homepageType === 'blog') {
                // Blog is the homepage
                if ($localesEnabled) {
                    $defaultLocale = config('blogr.locales.default', config('app.locale', 'en'));
                    return route('home', ['locale' => $defaultLocale]);
                }
                return route('home');
            }

            // CMS is the homepage
            if ($localesEnabled) {
                $defaultLocale = config('blogr.locales.default', config('app.locale', 'en'));
                return route('home', ['locale' => $defaultLocale]);
            }
            return route('home');
        } catch (\Exception $e) {
            return config('app.url', '/');
        }
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
