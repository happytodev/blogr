<?php

namespace Happytodev\Blogr;

class Blogr
{
    const VERSION = '1.23.13';

    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
