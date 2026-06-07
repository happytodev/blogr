<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '0.22.0';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
