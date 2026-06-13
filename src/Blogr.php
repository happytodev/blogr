<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '1.12.1';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
