<?php

namespace Happytodev\Blogr\Events;

use Illuminate\Foundation\Events\Dispatchable;

class UserDataExported
{
    use Dispatchable;

    public function __construct(
        public $user,
        public string $format,
    ) {}
}
