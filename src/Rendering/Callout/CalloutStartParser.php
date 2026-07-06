<?php

namespace Happytodev\Blogr\Rendering\Callout;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

class CalloutStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        $line = $cursor->getLine();

        if (! preg_match('/^:::(tip|info|danger|caution)(?:\[(.+?)\])?\s*$/', $line, $matches)) {
            return null;
        }

        $type = CalloutType::from($matches[1]);
        $title = $matches[2] ?? null;

        $cursor->advanceToEnd();

        return BlockStart::of(new CalloutParser(new CalloutBlock($type, $title)))->at($cursor);
    }
}
