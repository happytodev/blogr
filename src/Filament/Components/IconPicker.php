<?php

namespace Happytodev\Blogr\Filament\Components;

use Filament\Forms\Components\Field;
use Happytodev\Blogr\Helpers\IconHelper;

class IconPicker extends Field
{
    protected string $view = 'blogr::components.icon-picker';

    protected string $iconsPrefix = 'heroicon-o-';

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (IconPicker $component, $state): void {
            $component->state($state);
        });
    }

    public function getIcons(): array
    {
        return IconHelper::outlineIcons();
    }

    public function getIconSvg(string $icon): ?string
    {
        return IconHelper::getSvg($icon);
    }

    public function iconsPrefix(string $prefix): static
    {
        $this->iconsPrefix = $prefix;

        return $this;
    }

    public function getIconsPrefix(): string
    {
        return $this->iconsPrefix;
    }
}
