<?php

namespace Happytodev\Blogr\Rendering\Callout;

use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

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

    public function canContain(AbstractBlockContinueParser $childBlock): bool
    {
        return true;
    }

    public function tryContinue(Cursor $cursor, MarkdownParserStateInterface $parserState): ?int
    {
        $line = $cursor->getLine();

        if (trim($line) === ':::') {
            $this->ended = true;
            $cursor->advanceToEnd();

            return AbstractBlockContinueParser::ABORT;
        }

        if ($cursor->isBlank()) {
            return AbstractBlockContinueParser::CONTINUE;
        }

        return AbstractBlockContinueParser::CONTINUE;
    }
}
