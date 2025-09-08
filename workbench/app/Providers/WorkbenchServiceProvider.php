<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Workbench\App\Console\Commands\FilamentPublishCommand;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->commands([
            FilamentPublishCommand::class,
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish Filament views and assets for testing
        $this->publishes([
            base_path('vendor/filament/filament/resources/views') => resource_path('views/vendor/filament'),
            base_path('vendor/filament/forms/resources/views') => resource_path('views/vendor/filament-forms'),
        ], 'filament-views');

        // Publish Blogr views
        $this->publishes([
            base_path('resources/views') => resource_path('views/vendor/blogr'),
        ], 'blogr-views');
    }
}
