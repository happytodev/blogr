<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '1.23.3';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
