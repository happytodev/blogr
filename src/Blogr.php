<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '1.23.9';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
