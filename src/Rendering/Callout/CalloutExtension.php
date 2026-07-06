<?php

namespace Happytodev\Blogr\Rendering\Callout;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

class CalloutExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(CalloutParser::class, 200);
        $environment->addRenderer(CalloutBlock::class, new CalloutRenderer);
    }
}
