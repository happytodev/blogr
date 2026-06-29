<?php

namespace Happytodev\Blogr\Concerns;

use Happytodev\Blogr\Services\ExtensionRegistry;
use Happytodev\Blogr\Services\LinkTypeRegistry;

trait RegistersLinkTypes
{
    public function getSettingsUrl(): ?string
    {
        return null;
    }

    public function registerExtension(ExtensionRegistry $registry): void {}

    public function registerLinkTypes(LinkTypeRegistry $registry): void {}
}
