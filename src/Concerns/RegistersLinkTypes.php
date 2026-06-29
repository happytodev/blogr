<?php

namespace Happytodev\Blogr\Concerns;

use Happytodev\Blogr\Services\LinkTypeRegistry;

trait RegistersLinkTypes
{
    public function registerLinkTypes(LinkTypeRegistry $registry): void {}
}
