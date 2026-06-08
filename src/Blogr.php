<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '1.2.0';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
