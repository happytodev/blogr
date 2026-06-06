<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '0.20.0';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
