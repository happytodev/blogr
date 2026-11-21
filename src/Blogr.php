<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '2.7.1';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
