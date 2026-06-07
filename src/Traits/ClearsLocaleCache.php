<?php

namespace Happytodev\Blogr\Traits;

use Happytodev\Blogr\Services\LocaleService;
use Illuminate\Support\Facades\App;

trait ClearsLocaleCache
{
    public static function bootClearsLocaleCache(): void
    {
        static::saved(function () {
            App::make(LocaleService::class)->flushCache();
        });

        static::deleted(function () {
            App::make(LocaleService::class)->flushCache();
        });
    }
}
