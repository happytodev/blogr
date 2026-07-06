<?php

namespace Happytodev\Blogr\Rendering\Callout;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;

class CalloutParser extends AbstractBlockContinueParser
{
    private CalloutBlock $block;

    private bool $ended = false;

    public function __construct(CalloutBlock $block)
    {
        $this->block = $block;
    }

    public function getBlock(): CalloutBlock
    {
        return $this->block;
    }

    public function isContainer(): bool
    {
        return true;
    }

    public function canContain(AbstractBlock $childBlock): bool
    {
        return true;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        if ($this->ended) {
            return BlockContinue::finished();
        }

        $line = $cursor->getLine();

        if (trim($line) === ':::') {
            $this->ended = true;
            $cursor->advanceToEnd();

            return BlockContinue::at($cursor);
        }

        return BlockContinue::at($cursor);
    }
}
