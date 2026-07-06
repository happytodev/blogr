<?php

namespace Happytodev\Blogr\Rendering\Callout;

use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

class CalloutParser extends AbstractBlockContinueParser implements BlockStartParserInterface
{
    private CalloutBlock $block;

    private bool $ended = false;

    public function __construct(CalloutBlock $block)
    {
        $this->block = $block;
    }

    public static function parseStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        $line = $cursor->getLine();

        if (! preg_match('/^:::(tip|info|danger|caution)(?:\[(.+?)\])?\s*$/', $line, $matches)) {
            return null;
        }

        $type = CalloutType::from($matches[1]);
        $title = $matches[2] ?? null;

        $cursor->advanceToEnd();

        return BlockStart::of(new self(new CalloutBlock($type, $title)));
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

    public function closeBlock(): void
    {
        // nothing to close
    }
}
