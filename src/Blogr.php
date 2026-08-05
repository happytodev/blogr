<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '2.2.3';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
