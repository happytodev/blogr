<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '1.23.12';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
