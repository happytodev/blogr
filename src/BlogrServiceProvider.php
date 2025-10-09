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
use Happytodev\Blogr\Http\Controllers\BlogController;
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
            $this->app['router']
                ->prefix('{locale}')
                ->where(['locale' => $localePattern])
                ->middleware(array_merge(
                    config('blogr.route.middleware', ['web']),
                    [\Happytodev\Blogr\Http\Middleware\SetLocale::class]
                ))
                ->group(function () use ($prefix, $isHomepage) {
                    if ($prefix === '' || $prefix === '/' || $isHomepage) {
                        // Blog as homepage with locale
                        $this->app['router']->get('/', [BlogController::class, 'index'])->name('blog.index');
                        $this->app['router']->get('/series', [BlogController::class, 'seriesIndex'])->name('blog.series.index');
                        $this->app['router']->get('/series/{seriesSlug}', [BlogController::class, 'series'])->name('blog.series');
                        $this->app['router']->get('/{slug}', [BlogController::class, 'show'])->name('blog.show');
                        $this->app['router']->get('/category/{categorySlug}', [BlogController::class, 'category'])->name('blog.category');
                        $this->app['router']->get('/tag/{tagSlug}', [BlogController::class, 'tag'])->name('blog.tag');
                    } else {
                        // Blog with prefix and locale
                        $this->app['router']
                            ->prefix($prefix)
                            ->group(function () {
                                $this->app['router']->get('/', [BlogController::class, 'index'])->name('blog.index');
                                $this->app['router']->get('/series', [BlogController::class, 'seriesIndex'])->name('blog.series.index');
                                $this->app['router']->get('/series/{seriesSlug}', [BlogController::class, 'series'])->name('blog.series');
                                $this->app['router']->get('/{slug}', [BlogController::class, 'show'])->name('blog.show');
                                $this->app['router']->get('/category/{categorySlug}', [BlogController::class, 'category'])->name('blog.category');
                                $this->app['router']->get('/tag/{tagSlug}', [BlogController::class, 'tag'])->name('blog.tag');
                                $this->app['router']->get('/series/{seriesSlug}', [BlogController::class, 'series'])->name('blog.series');
                            });
                    }
                });
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
                        $this->app['router']->get('/category/{categorySlug}', [BlogController::class, 'category'])->name('blog.category');
                        $this->app['router']->get('/tag/{tagSlug}', [BlogController::class, 'tag'])->name('blog.tag');
                        $this->app['router']->get('/{slug}', [BlogController::class, 'show'])->name('blog.show');
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
                        $this->app['router']->get('/{slug}', [BlogController::class, 'show'])->name('blog.show');
                        $this->app['router']->get('/category/{categorySlug}', [BlogController::class, 'category'])->name('blog.category');
                        $this->app['router']->get('/tag/{tagSlug}', [BlogController::class, 'tag'])->name('blog.tag');
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
        // Register Observers
        BlogPost::observe(\Happytodev\Blogr\Observers\BlogPostObserver::class);
        
        // Publishes the configuration and views
        $this->publishes([
            __DIR__ . '/../config/blogr.php' => config_path('blogr.php'),
            __DIR__ . '/../resources/views' => resource_path('views/vendor/blogr'),
            __DIR__ . '/../database/migrations' => database_path('migrations'),
            __DIR__ . '/../resources/images' => public_path('storage/images'),
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
            ]);
        }

        $prefix = trim(config('blogr.route.prefix', 'blog'), '/');

        if (config()->get('blogr.route.frontend.enabled', true)) {
            $this->registerFrontendRoutes();
        }
    }
}
