<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '2.3.0';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
