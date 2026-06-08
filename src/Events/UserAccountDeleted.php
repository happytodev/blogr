<?php

namespace Happytodev\Blogr\Events;

use Illuminate\Foundation\Events\Dispatchable;

class UserAccountDeleted
{
    use Dispatchable;

    public function __construct(
        public $user,
        public bool $anonymizePosts,
    ) {}
}
