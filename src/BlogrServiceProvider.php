<?php

namespace Happytodev\Blogr;

use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Asset;
use Illuminate\Filesystem\Filesystem;
use Spatie\LaravelPackageTools\Package;
use Happytodev\Blogr\Testing\TestsBlogr;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Facades\FilamentAsset;
use Happytodev\Blogr\Commands\BlogrCommand;
use Filament\Support\Assets\AlpineComponent;
use Livewire\Features\SupportTesting\Testable;
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
            ->hasCommands($this->getCommands())
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

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/blogr/{$file->getFilename()}"),
                ], 'blogr-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsBlogr);
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
    protected function getCommands(): array
    {
        return [
            BlogrCommand::class,
        ];
    }

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
    }

    public function boot()
    {
        // Publier la configuration et les vues
        $this->publishes([
            __DIR__ . '/../config/blogr.php' => config_path('blogr.php'),
            __DIR__ . '/../resources/views' => resource_path('views/vendor/blogr'),
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'blogr');

        // Charger les migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Charger les vues
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'blogr');

        // Enregistrer les routes frontend
        $this->app['router']
            ->prefix(config('blogr.route.prefix', 'blog'))
            ->middleware(config('blogr.route.middleware', ['web']))
            ->group(function () {
                $this->app['router']->get('/', [BlogController::class, 'index'])->name('blog.index');
                $this->app['router']->get('/{slug}', [BlogController::class, 'show'])->name('blog.show');
            });
    }
}
