<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '1.0.2';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
