<?php

namespace Happytodev\Blogr\Rendering\Callout;

use League\CommonMark\Node\Block\AbstractBlock;

class CalloutBlock extends AbstractBlock
{
    public function __construct(
        private CalloutType $type,
        private ?string $title = null,
    ) {
        parent::__construct();
    }

    public function getType(): CalloutType
    {
        return $this->type;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
}
