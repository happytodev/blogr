<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '0.24.1';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
