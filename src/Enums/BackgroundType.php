<?php

namespace Happytodev\Blogr\Enums;

enum BackgroundType: string
{
    case NONE = 'none';
    case COLOR = 'color';
    case GRADIENT = 'gradient';
    case IMAGE = 'image';
    case PATTERN = 'pattern';

    public function getLabel(): string
    {
        return match ($this) {
            self::NONE => __('None'),
            self::COLOR => __('Solid Color'),
            self::GRADIENT => __('Gradient'),
            self::IMAGE => __('Image'),
            self::PATTERN => __('Pattern'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::NONE => 'heroicon-o-minus',
            self::COLOR => 'heroicon-o-paint-brush',
            self::GRADIENT => 'heroicon-o-sparkles',
            self::IMAGE => 'heroicon-o-photo',
            self::PATTERN => 'heroicon-o-squares-2x2',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
