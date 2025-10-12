<?php

namespace Happytodev\Blogr\Tests;

use Filament\Panel;
use Filament\PanelProvider;
use Happytodev\Blogr\Filament\Pages\Auth\EditProfile;
use Happytodev\Blogr\Filament\Resources\Tags\TagResource;
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
            ->middleware([
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
                \Illuminate\Routing\Middleware\ThrottleRequests::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
            ])
            ->authMiddleware([
                'auth',
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
