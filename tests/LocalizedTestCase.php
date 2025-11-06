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

/**
 * TestCase for multilingual/localized tests
 * This TestCase configures locales BEFORE ServiceProvider boot
 */
class LocalizedTestCase extends Orchestra
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
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            SchemasServiceProvider::class,
            PermissionServiceProvider::class,
            BlogrServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Set application encryption key
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        
        // Set auth provider to use test User model
        $app['config']->set('auth.providers.users.model', \Workbench\App\Models\User::class);

        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // IMPORTANT: Configure locales BEFORE ServiceProvider boot
        $app['config']->set('blogr.locales.enabled', true);
        $app['config']->set('blogr.locales.default', 'en');
        $app['config']->set('blogr.locales.available', ['en', 'fr']);
        $app['config']->set('blogr.author_profile.enabled', true);
        $app['config']->set('blogr.display.show_author_pseudo', true);
        $app['config']->set('blogr.display.show_author_avatar', true);

        // Configure Spatie Permission
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
            'cache' => [
                'expiration_time' => \DateInterval::createFromDateString('24 hours'),
                'key' => 'spatie.permission.cache',
                'store' => 'default',
            ],
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        // Load Laravel migrations (sans users car custom)
        $this->loadLaravelMigrations();
        
        // Load test-specific migrations (including users table for testing)
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        
        // Load package migrations (blog + CMS)
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
