<?php

namespace Happytodev\Blogr;

use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Asset;
use Illuminate\Support\Facades\Gate;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Filesystem\Filesystem;
use Spatie\LaravelPackageTools\Package;
use Happytodev\Blogr\Testing\TestsBlogr;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Facades\FilamentAsset;
use Happytodev\Blogr\Commands\BlogrCommand;
use Filament\Support\Assets\AlpineComponent;
use Happytodev\Blogr\Helpers\ConfigHelper;
use Happytodev\Blogr\Policies\BlogPostPolicy;
use Livewire\Features\SupportTesting\Testable;
use Happytodev\Blogr\Commands\BlogrInstallCommand;
use Happytodev\Blogr\Commands\InstallUserManagementCommand;
use Happytodev\Blogr\Commands\MigratePostsToTranslations;
use Happytodev\Blogr\Commands\BlogrExportCommand;
use Happytodev\Blogr\Commands\BlogrImportCommand;
use Happytodev\Blogr\Http\Controllers\BlogController;
use Happytodev\Blogr\Http\Controllers\AuthorController;
use Happytodev\Blogr\Http\Controllers\RssFeedController;
use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Happytodev\Blogr\Observers\BlogSeriesTranslationObserver;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;

class BlogrServiceProvider extends PackageServiceProvider
{
    public static string $name = 'blogr';

    public static string $viewNamespace = 'blogr';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('happytodev/blogr');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Register Policies
        Gate::policy(BlogPost::class, BlogPostPolicy::class);

        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Widget Registration
        $this->registerWidgets();

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/blogr/{$file->getFilename()}"),
                ], 'blogr-stubs');
            }
            
            // Publish translations
            $this->publishes([
                __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/blogr'),
            ], 'blogr-translations');
            
            // Publish default series image
            $this->publishes([
                __DIR__ . '/../resources/images' => public_path('vendor/blogr/images'),
            ], 'blogr-assets');
        }

        // Testing
        Testable::mixin(new TestsBlogr);
    }

    protected function registerFrontendRoutes(): void
    {
        $prefix = trim(config('blogr.route.prefix', 'blog'), '/');
        $isHomepage = config('blogr.route.homepage', false);
        $localesEnabled = config('blogr.locales.enabled', false);
        $availableLocales = config('blogr.locales.available', ['en']);
        $localePattern = implode('|', $availableLocales);

        // If homepage is explicitly set, override prefix
        if ($isHomepage) {
            $prefix = '';
        }

        if ($localesEnabled) {
            // Add fallback redirect from non-localized URL to default locale
            $defaultLocale = config('blogr.locales.default', 'en');

            if ($prefix === '' || $prefix === '/' || $isHomepage) {
                // When blog is homepage, override the root route to redirect to default locale
                // This ensures it takes precedence over any other root route
                $this->app['router']
                    ->middleware(config('blogr.route.middleware', ['web']))
                    ->get('/', function () use ($defaultLocale) {
                        return redirect("/{$defaultLocale}", 302);
                    })->name('homepage.redirect');
            } else {
                // Redirect /blog to /en/blog (or default locale)
                $this->app['router']
                    ->middleware(config('blogr.route.middleware', ['web']))
                    ->get("/{$prefix}", function () use ($prefix, $defaultLocale) {
                        return redirect("/{$defaultLocale}/{$prefix}");
                    });
            }            // Register localized routes with locale prefix
            // Blog routes with locale - REFACTORED: No nested prefix groups to avoid Laravel parameter binding bug
            if ($prefix === '' || $prefix === '/' || $isHomepage) {
                // Blog as homepage with locale: /{locale}/
                $this->app['router']->get('{locale}', [BlogController::class, 'index'])
                    ->where('locale', $localePattern)
                    ->middleware(array_merge(
                        config('blogr.route.middleware', ['web']),
                        [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                    ))
                    ->name('blog.index');
                    
                $this->app['router']->get('{locale}/series', [BlogController::class, 'seriesIndex'])
                    ->where('locale', $localePattern)
                    ->middleware(array_merge(
                        config('blogr.route.middleware', ['web']),
                        [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                    ))
                    ->name('blog.series.index');
                    
                $this->app['router']->get('{locale}/series/{seriesSlug}', [BlogController::class, 'series'])
                    ->where(['locale' => $localePattern, 'seriesSlug' => '.*'])
                    ->middleware(array_merge(
                        config('blogr.route.middleware', ['web']),
                        [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                    ))
                    ->name('blog.series');
                    
                $this->app['router']->get('{locale}/author/{userSlug}', [AuthorController::class, 'show'])
                    ->where(['locale' => $localePattern, 'userSlug' => '.*'])
                    ->middleware(array_merge(
                        config('blogr.route.middleware', ['web']),
                        [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                    ))
                    ->name('blog.author');
                    
                $this->app['router']->get('{locale}/category/{categorySlug}', [BlogController::class, 'category'])
                    ->where(['locale' => $localePattern, 'categorySlug' => '.*'])
                    ->middleware(array_merge(
                        config('blogr.route.middleware', ['web']),
                        [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                    ))
                    ->name('blog.category');
                    
                $this->app['router']->get('{locale}/tag/{tagSlug}', [BlogController::class, 'tag'])
                    ->where(['locale' => $localePattern, 'tagSlug' => '.*'])
                    ->middleware(array_merge(
                        config('blogr.route.middleware', ['web']),
                        [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                    ))
                    ->name('blog.tag');
                    
                // RSS Feed routes with locale (homepage mode - MUST be before {slug} catch-all)
                if (config('blogr.rss.enabled', true)) {
                    $this->app['router']->get('{locale}/feed', [RssFeedController::class, 'index'])
                        ->where('locale', $localePattern)
                        ->middleware(config('blogr.route.middleware', ['web']))
                        ->name('blog.feed');
                    
                    $this->app['router']->get('{locale}/feed/category/{categorySlug}', [RssFeedController::class, 'category'])
                        ->where(['locale' => $localePattern, 'categorySlug' => '.*'])
                        ->middleware(config('blogr.route.middleware', ['web']))
                        ->name('blog.feed.category');
                    
                    $this->app['router']->get('{locale}/feed/tag/{tagSlug}', [RssFeedController::class, 'tag'])
                        ->where(['locale' => $localePattern, 'tagSlug' => '.*'])
                        ->middleware(config('blogr.route.middleware', ['web']))
                        ->name('blog.feed.tag');
                }
                    
                $this->app['router']->get('{locale}/{slug}', [BlogController::class, 'show'])
                    ->where(['locale' => $localePattern, 'slug' => '.*']) // Allow any slug since specific routes are already defined
                    ->middleware(array_merge(
                        config('blogr.route.middleware', ['web']),
                        [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                    ))
                    ->name('blog.show');
            } else {
                // Blog with prefix and locale: /{locale}/{prefix}/
                $fullPrefix = '{locale}/' . $prefix;
                
                $this->app['router']->get($fullPrefix, [BlogController::class, 'index'])
                    ->where('locale', $localePattern)
                    ->middleware(array_merge(
                        config('blogr.route.middleware', ['web']),
                        [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                    ))
                    ->name('blog.index');
                    
                $this->app['router']->get($fullPrefix . '/series', [BlogController::class, 'seriesIndex'])
                    ->where('locale', $localePattern)
                    ->middleware(array_merge(
                        config('blogr.route.middleware', ['web']),
                        [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                    ))
                    ->name('blog.series.index');
                    
                $this->app['router']->get($fullPrefix . '/series/{seriesSlug}', [BlogController::class, 'series'])
                    ->where(['locale' => $localePattern, 'seriesSlug' => '.*'])
                    ->middleware(array_merge(
                        config('blogr.route.middleware', ['web']),
                        [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                    ))
                    ->name('blog.series');
                    
                $this->app['router']->get($fullPrefix . '/author/{userSlug}', [AuthorController::class, 'show'])
                    ->where(['locale' => $localePattern, 'userSlug' => '.*'])
                    ->middleware(array_merge(
                        config('blogr.route.middleware', ['web']),
                        [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                    ))
                    ->name('blog.author');
                    
                $this->app['router']->get($fullPrefix . '/category/{categorySlug}', [BlogController::class, 'category'])
                    ->where(['locale' => $localePattern, 'categorySlug' => '.*'])
                    ->middleware(array_merge(
                        config('blogr.route.middleware', ['web']),
                        [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                    ))
                    ->name('blog.category');
                    
                $this->app['router']->get($fullPrefix . '/tag/{tagSlug}', [BlogController::class, 'tag'])
                    ->where(['locale' => $localePattern, 'tagSlug' => '.*'])
                    ->middleware(array_merge(
                        config('blogr.route.middleware', ['web']),
                        [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                    ))
                    ->name('blog.tag');
                    
                // RSS Feed routes with locale (MUST be before {slug} catch-all)
                if (config('blogr.rss.enabled', true)) {
                    $this->app['router']->get($fullPrefix . '/feed', [RssFeedController::class, 'index'])
                        ->where('locale', $localePattern)
                        ->middleware(config('blogr.route.middleware', ['web']))
                        ->name('blog.feed');
                    
                    $this->app['router']->get($fullPrefix . '/feed/category/{categorySlug}', [RssFeedController::class, 'category'])
                        ->where(['locale' => $localePattern, 'categorySlug' => '.*'])
                        ->middleware(config('blogr.route.middleware', ['web']))
                        ->name('blog.feed.category');
                    
                    $this->app['router']->get($fullPrefix . '/feed/tag/{tagSlug}', [RssFeedController::class, 'tag'])
                        ->where(['locale' => $localePattern, 'tagSlug' => '.*'])
                        ->middleware(config('blogr.route.middleware', ['web']))
                        ->name('blog.feed.tag');
                }
                    
                $this->app['router']->get($fullPrefix . '/{slug}', [BlogController::class, 'show'])
                    ->where(['locale' => $localePattern, 'slug' => '.*']) // Allow any slug since specific routes are already defined
                    ->middleware(array_merge(
                        config('blogr.route.middleware', ['web']),
                        [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                    ))
                    ->name('blog.show');
            }
        } else {
            // Original non-localized routes
            if ($prefix === '' || $prefix === '/' || $isHomepage) {
                // Blog route as homepage
                $this->app['router']
                    ->middleware(config('blogr.route.middleware', ['web']))
                    ->group(function () {
                        $this->app['router']->get('/', [BlogController::class, 'index'])->name('blog.index');
                        $this->app['router']->get('/series', [BlogController::class, 'seriesIndex'])->name('blog.series.index');
                        $this->app['router']->get('/series/{seriesSlug}', [BlogController::class, 'series'])->name('blog.series');
                        $this->app['router']->get('/author/{userSlug}', [AuthorController::class, 'show'])->name('blog.author');
                        $this->app['router']->get('/category/{categorySlug}', [BlogController::class, 'category'])->name('blog.category');
                        $this->app['router']->get('/tag/{tagSlug}', [BlogController::class, 'tag'])->name('blog.tag');
                        $this->app['router']->get('/{slug}', [BlogController::class, 'show'])
                            ->where('slug', '.*') // Allow any slug since specific routes are already defined
                            ->name('blog.show');
                        
                        // RSS Feed routes (no locale)
                        if (config('blogr.rss.enabled', true)) {
                            $this->app['router']->get('/feed', [RssFeedController::class, 'index'])->name('blog.feed');
                            $this->app['router']->get('/feed/category/{categorySlug}', [RssFeedController::class, 'category'])->name('blog.feed.category');
                            $this->app['router']->get('/feed/tag/{tagSlug}', [RssFeedController::class, 'tag'])->name('blog.feed.tag');
                        }
                    });
            } else {
                // Blog route with prefix
                $this->app['router']
                    ->prefix($prefix)
                    ->middleware(config('blogr.route.middleware', ['web']))
                    ->group(function () {
                        $this->app['router']->get('/', [BlogController::class, 'index'])->name('blog.index');
                        $this->app['router']->get('/series', [BlogController::class, 'seriesIndex'])->name('blog.series.index');
                        $this->app['router']->get('/series/{seriesSlug}', [BlogController::class, 'series'])->name('blog.series');
                        $this->app['router']->get('/author/{userSlug}', [AuthorController::class, 'show'])->name('blog.author');
                        $this->app['router']->get('/category/{categorySlug}', [BlogController::class, 'category'])->name('blog.category');
                        $this->app['router']->get('/tag/{tagSlug}', [BlogController::class, 'tag'])->name('blog.tag');
                        $this->app['router']->get('/{slug}', [BlogController::class, 'show'])
                            ->where('slug', '.*') // Allow any slug since specific routes are already defined
                            ->name('blog.show');
                        
                        // RSS Feed routes (no locale)
                        if (config('blogr.rss.enabled', true)) {
                            $this->app['router']->get('/feed', [RssFeedController::class, 'index'])->name('blog.feed');
                            $this->app['router']->get('/feed/category/{categorySlug}', [RssFeedController::class, 'category'])->name('blog.feed.category');
                            $this->app['router']->get('/feed/tag/{tagSlug}', [RssFeedController::class, 'tag'])->name('blog.feed.tag');
                        }
                    });
            }
        }
    }

    /**
     * Register the blog widgets
     */
    protected function registerWidgets(): void
    {
        // Only register widgets if we're in a Filament context
        if (!class_exists('\Filament\PanelProvider')) {
            return;
        }

        $widgets = [
            \Happytodev\Blogr\Filament\Widgets\BlogStatsOverview::class,
            \Happytodev\Blogr\Filament\Widgets\RecentBlogPosts::class,
            \Happytodev\Blogr\Filament\Widgets\ScheduledPosts::class,
            \Happytodev\Blogr\Filament\Widgets\BlogPostsChart::class,
            \Happytodev\Blogr\Filament\Widgets\BlogReadingStats::class,
        ];

        // Register widgets with Filament
        foreach ($widgets as $widget) {
            if (class_exists($widget)) {
                // Widgets are automatically discovered by Filament
                // No additional registration needed
            }
        }
    }

    protected function getAssetPackageName(): ?string
    {
        return 'happytodev/blogr';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('blogr', __DIR__ . '/../resources/dist/components/blogr.js'),

            // The 2 folllowing are commented out as they are not used in the current context.
            // Css::make('blogr-styles', __DIR__ . '/../resources/dist/blogr.css'),
            // Js::make('blogr-scripts', __DIR__ . '/../resources/dist/blogr.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_blogr_table',
        ];
    }



    // to check
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/blogr.php', 'blogr');
        
        // Register config helper
        $this->app->singleton('blogr.config', function ($app) {
            return new ConfigHelper();
        });
    }

    public function boot()
    {
        // Register model observers
        BlogSeriesTranslation::observe(BlogSeriesTranslationObserver::class);
        
        // Publishes the configuration and views
        $this->publishes([
            __DIR__ . '/../config/blogr.php' => config_path('blogr.php'),
            __DIR__ . '/../resources/views' => resource_path('views/vendor/blogr'),
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'blogr');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'blogr');
        
        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'blogr');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                BlogrCommand::class,
                BlogrInstallCommand::class,
                InstallUserManagementCommand::class,
                MigratePostsToTranslations::class,
                BlogrExportCommand::class,
                BlogrImportCommand::class,
            ]);
        }

        $prefix = trim(config('blogr.route.prefix', 'blog'), '/');

        if (config()->get('blogr.route.frontend.enabled', true)) {
            $this->registerFrontendRoutes();
        }
    }
}
