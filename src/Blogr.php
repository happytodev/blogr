<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '1.33.0';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
