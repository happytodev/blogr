<?php

namespace Happytodev\Blogr;

use Filament\Panel;
use Filament\Contracts\Plugin;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;

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
