<?php

namespace Happytodev\Blogr\Tests;

use Filament\Panel;
use Filament\PanelProvider;
use Happytodev\Blogr\BlogrPlugin;
use Happytodev\Blogr\Filament\Pages\Auth\EditProfile;
use Happytodev\Blogr\Filament\Resources\Tags\TagResource;
use Happytodev\Blogr\Tests\Middleware\InitializeSessionErrors;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Workbench\App\Models\User;

class BrowserTestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('test')
            ->path('admin')
            ->login()
            ->profile(EditProfile::class)
            ->resources([
                TagResource::class,
            ])
            // Register the Blogr plugin so package Filament resources (CMS, posts, etc.)
            // are available during tests. The plugin itself will only add CMS
            // resources when the CMS feature is enabled via config.
            ->plugins([
                BlogrPlugin::make(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                InitializeSessionErrors::class,
                ShareErrorsFromSession::class,
                Authenticate::class,
                SubstituteBindings::class,
            ])
            ->authGuard('web')
            ->brandName('Blogr Test')
            ->favicon(null)
            ->darkMode(false)
            ->userMenuItems([
                // Add any user menu items if needed
            ]);
    }
}
