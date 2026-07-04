<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '1.24.0';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
