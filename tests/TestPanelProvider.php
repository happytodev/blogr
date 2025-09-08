<?php

namespace Happytodev\Blogr\Tests;

use Filament\Panel;
use Filament\PanelProvider;

class TestPanelProvider extends PanelProvider
{
    public static function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('test')
            ->path('admin')
            ->resources([
                \Happytodev\Blogr\Filament\Resources\Tags\TagResource::class,
            ]);
    }
}
