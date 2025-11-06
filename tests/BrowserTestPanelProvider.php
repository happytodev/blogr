<?php

namespace Happytodev\Blogr\Tests;

use Filament\Panel;
use Filament\PanelProvider;
use Happytodev\Blogr\Filament\Pages\Auth\EditProfile;
use Happytodev\Blogr\Filament\Resources\Tags\TagResource;
use Happytodev\Blogr\BlogrPlugin;
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
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Happytodev\Blogr\Tests\Middleware\InitializeSessionErrors::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Auth\Middleware\Authenticate::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
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
