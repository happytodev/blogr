<?php

namespace Happytodev\Blogr\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class IconHelper
{
    protected static ?array $outlineIcons = null;

    public static function outlineIcons(): array
    {
        if (static::$outlineIcons !== null) {
            return static::$outlineIcons;
        }

        return static::$outlineIcons = Cache::rememberForever('blogr-heroicon-outline-list', function () {
            $svgDir = static::resolveSvgDirectory();

            if (! is_dir($svgDir)) {
                return [];
            }

            $files = File::files($svgDir);
            $icons = [];

            foreach ($files as $file) {
                $name = $file->getFilenameWithoutExtension();
                if (str_starts_with($name, 'o-')) {
                    $icon = substr($name, 2);
                    $icons[$icon] = $icon;
                }
            }

            ksort($icons);

            return $icons;
        });
    }

    public static function resolveSvgDirectory(): string
    {
        $paths = [
            base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg'),
            dirname(__DIR__, 2).'/vendor/blade-ui-kit/blade-heroicons/resources/svg',
        ];

        foreach ($paths as $path) {
            if (is_dir($path)) {
                return $path;
            }
        }

        return $paths[0];
    }

    public static function getSvg(string $icon): ?string
    {
        $path = static::resolveSvgDirectory()."/o-{$icon}.svg";

        if (! file_exists($path)) {
            return null;
        }

        return file_get_contents($path);
    }

    public static function flushCache(): void
    {
        Cache::forget('blogr-heroicon-outline-list');
        static::$outlineIcons = null;
    }

    public static function isValid(string $icon): bool
    {
        return isset(static::outlineIcons()[$icon]);
    }
}
