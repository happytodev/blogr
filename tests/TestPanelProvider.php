<?php

namespace Happytodev\Blogr\Tests;

use Filament\Panel;
use Filament\PanelProvider;
use Happytodev\Blogr\Filament\Resources\Tags\TagResource;

class TestPanelProvider extends PanelProvider
{
    public static function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('test')
            ->path('admin')
            ->resources([
                TagResource::class,
            ]);
    }
}
