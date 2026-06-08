<?php

namespace Happytodev\Blogr\Filament\Pages;

use Filament\Pages\Page;
use Happytodev\Blogr\Services\ExtensionRegistry;

class Plugins extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationLabel = 'Plugins';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected string $view = 'blogr::filament.pages.plugins';

    public function getTitle(): string
    {
        return __('Plugins');
    }

    public function getBreadcrumbs(): array
    {
        return [
            __('Plugins'),
        ];
    }

    /**
     * Provide view data for the plugins page.
     * This is called by Livewire's rendering pipeline.
     */
    public function getExtensionsList(): array
    {
        return app(ExtensionRegistry::class)->getAll();
    }
}
