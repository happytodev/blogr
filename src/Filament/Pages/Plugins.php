<?php

namespace Happytodev\Blogr\Filament\Pages;

use Filament\Notifications\Notification;
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

    public function getExtensionsList(): array
    {
        return app(ExtensionRegistry::class)->getAll();
    }

    /** @return string[] */
    public function getDisabledExtensions(): array
    {
        return app(ExtensionRegistry::class)->getDisabledIds();
    }

    public function toggleExtension(string $id): void
    {
        if ($id === 'blogr-core') {
            Notification::make()
                ->title(__('Core plugin'))
                ->body(__('The core plugin cannot be disabled.'))
                ->warning()
                ->send();

            return;
        }

        $registry = app(ExtensionRegistry::class);

        if ($registry->isEnabled($id)) {
            $registry->disable($id);

            Notification::make()
                ->title(__('Plugin disabled'))
                ->body(__(':name has been disabled.', ['name' => $this->getExtensionName($id)]))
                ->warning()
                ->send();
        } else {
            $registry->enable($id);

            Notification::make()
                ->title(__('Plugin enabled'))
                ->body(__(':name has been enabled.', ['name' => $this->getExtensionName($id)]))
                ->success()
                ->send();
        }
    }

    private function getExtensionName(string $id): string
    {
        $extensions = app(ExtensionRegistry::class)->getAll();

        foreach ($extensions as $ext) {
            if ($ext->getId() === $id) {
                return $ext->getName();
            }
        }

        return $id;
    }
}
