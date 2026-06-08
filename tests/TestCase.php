<?php

namespace Happytodev\Blogr\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Happytodev\Blogr\BlogrServiceProvider;
use Happytodev\Blogr\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Vite;
use Illuminate\Support\HtmlString;
use Livewire\LivewireServiceProvider;
use Livewire\Mechanisms\DataStore;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Happytodev\\Blogr\\Tests\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        // Add the Vite mock here
        $this->mock(Vite::class, function (MockInterface $mock) {
            $mock->shouldReceive('__invoke')->andReturn(new HtmlString(''));
        });

        // Load translations for tests (after parent::setUp so app is initialized)
        // We need to ensure translations are preloaded and cached for __() to work
        $this->loadTranslationsForTests();

        // Fix Livewire DataStore non-singleton bug in test environment
        // Livewire's DataStore::register() calls app()->instance() but
        // something in Testbench re-binds it (bindings instead of instances),
        // causing app(DataStore::class) to return a NEW instance each call.
        // This breaks store($component)->get('errorBag') which always returns null
        // because the DataStore used by set() is different from the one used by get().
        // ViewErrorBag::put('default', null) then throws TypeError in Laravel 12.61.1+.
        $this->ensureLivewireDataStoreIsSingleton();
    }

    private function ensureLivewireDataStoreIsSingleton(): void
    {
        $ds = app(DataStore::class);
        app()->instance(DataStore::class, $ds);
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
        $app['config']->set('auth.providers.users.model', User::class);

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
                'permission' => Permission::class,
                'role' => Role::class,
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
        // Load test-specific migrations (includes users + permission tables with hasTable() check)
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        // Load package migrations (blog, cms, etc - excludes permission migration)
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    private function loadTranslationsForTests()
    {
        // Load package translations so tests can access __('blogr::key')
        $langPath = __DIR__.'/../resources/lang';
        $this->app['translator']->addNamespace('blogr', $langPath);
    }

    // Routes are now defined by BlogrServiceProvider in boot()
    // protected function defineRoutes($router)
    // {
    //     // Load test routes
    //     require __DIR__ . '/Feature/routes.php';
    // }
}
