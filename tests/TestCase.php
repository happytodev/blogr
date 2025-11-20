<?php

namespace Happytodev\Blogr\Tests;

use Filament\Panel;
use Filament\PanelProvider;
use Mockery\MockInterface;
use Illuminate\Foundation\Vite;
use Illuminate\Support\HtmlString;
use Filament\FilamentServiceProvider;
use Livewire\LivewireServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Happytodev\Blogr\BlogrServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Filament\Infolists\InfolistsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => 'Happytodev\\Blogr\\Tests\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        // Add the Vite mock here
        $this->mock(Vite::class, function (MockInterface $mock) {
            $mock->shouldReceive('__invoke')->andReturn(new HtmlString(''));
        });

        // Load translations for tests (after parent::setUp so app is initialized)
        // We need to ensure translations are preloaded and cached for __() to work
        $this->loadTranslationsForTests();

        // Patch Livewire ViewErrorBag null bug in test environment
        // Livewire's SupportValidation::render() calls ViewErrorBag::put('default', $errorBag)
        // where $errorBag can be null, causing: "Argument #2 must be of type MessageBag, null given"
        // This patch ensures we always have a valid MessageBag
        $this->patchLivewireViewErrorBag();
    }

    private function patchLivewireViewErrorBag(): void
    {
        // Patch Livewire's ViewErrorBag bug where put() receives null
        // Instead of trying to prevent null, we make ViewErrorBag::put() handle null gracefully
        
        // Use monkey-patching with reflection or simpler: replace put method behavior
        // Actually, simplest: just initialize the session errors early
        
        // The issue is: Livewire calls getErrorBag() which can return null
        // We can't patch that directly, but we CAN ensure session has errors
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        
        // Also ensure the session driver creates a valid ViewErrorBag
        if (app()->has('view') && app('view')->shared('errors') === null) {
            app('view')->share('errors', new \Illuminate\Support\ViewErrorBag());
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            BlogrServiceProvider::class,
            BrowserTestPanelProvider::class,
            PermissionServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $app['config']->set('app.key', 'base64:/QGZSf6gflmQp4zukiY3ab0DnTFMOqLK1//pgpQhFzw=');
        
        // Configure session to use array driver (default for tests)
        // This ensures session data persists across middleware calls within a single request
        $app['config']->set('session.driver', 'array');

        // Configure the auth user model to use the Blogr User model
        $app['config']->set('auth.providers.users.model', \Happytodev\Blogr\Models\User::class);

        // Configure blogr settings for testing - MUST be set before ServiceProvider loads
        $app['config']->set('blogr.locales.enabled', false);
        $app['config']->set('blogr.route.prefix', 'blog');
        $app['config']->set('blogr.route.homepage', false);

        // Configure database for testing
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Configure Spatie Permission to use 'web' guard by default
        $app['config']->set('permission', [
            'models' => [
                'permission' => \Spatie\Permission\Models\Permission::class,
                'role' => \Spatie\Permission\Models\Role::class,
            ],
            'table_names' => [
                'roles' => 'roles',
                'permissions' => 'permissions',
                'model_has_permissions' => 'model_has_permissions',
                'model_has_roles' => 'model_has_roles',
                'role_has_permissions' => 'role_has_permissions',
            ],
            'column_names' => [
                'role_pivot_key' => null,
                'permission_pivot_key' => null,
                'model_morph_key' => 'model_id',
                'team_foreign_key' => 'team_id',
            ],
            'teams' => false,
            'use_passport_client_credentials' => false,
            'display_permission_in_exception' => false,
            'display_role_in_exception' => false,
            'enable_wildcard_permission' => false,
            'register_permission_check_method' => true,
            'register_octane_reset_listener' => false,
            'cache' => [
                'expiration_time' => \DateInterval::createFromDateString('24 hours'),
                'key' => 'spatie.permission.cache',
                'store' => 'default',
            ],
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        // Load test-specific migrations (includes users and Spatie Permission tables)
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Also load package migrations (blog, cms, etc.) so tests have the package tables
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    private function loadTranslationsForTests()
    {
        // Load package translations so tests can access __('blogr::key')
        $langPath = __DIR__ . '/../resources/lang';
        $this->app['translator']->addNamespace('blogr', $langPath);
    }

    // Routes are now defined by BlogrServiceProvider in boot()
    // protected function defineRoutes($router)
    // {
    //     // Load test routes
    //     require __DIR__ . '/Feature/routes.php';
    // }
}
