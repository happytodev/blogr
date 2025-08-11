<?php

namespace Happytodev\Blogr\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Happytodev\Blogr\Blogr
 */
class Blogr extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Happytodev\Blogr\Blogr::class;
    }
}
