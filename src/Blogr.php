<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '0.18.5';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
