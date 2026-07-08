<?php

namespace Happytodev\Blogr\Filament\Components;

use Filament\Forms\Components\Field;
use Happytodev\Blogr\Helpers\IconHelper;

class IconPicker extends Field
{
    protected string $view = 'blogr::components.icon-picker';

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function getIconsWithSvg(): array
    {
        $icons = IconHelper::outlineIcons();
        $result = [];

        foreach ($icons as $name) {
            $svg = IconHelper::getSvg($name);
            if ($svg) {
                $result[] = [
                    'name' => $name,
                    'svg' => $svg,
                ];
            }
        }

        return $result;
    }

    public function getIconSvg(string $icon): ?string
    {
        return IconHelper::getSvg($icon);
    }
}
