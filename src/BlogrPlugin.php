<?php

namespace Happytodev\Blogr;

use Filament\Panel;
use Filament\Contracts\Plugin;
use Filament\Support\Colors\Color;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;
use Happytodev\Blogr\Filament\Resources\Tags\TagResource;
use Happytodev\Blogr\Filament\Resources\Categories\CategoryResource;

class BlogrPlugin implements Plugin
{
    public function getId(): string
    {
        return 'blogr';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            BlogPostResource::class,
            CategoryResource::class,
            TagResource::class,
        ]);

        $panel->colors([
            'primary' => config('blogr.colors.primary', Color::Orange),
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
