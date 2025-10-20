<?php

namespace Happytodev\Blogr\Helpers;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownHelper
{
    protected static ?MarkdownConverter $converter = null;

    /**
     * Get or create the markdown converter instance
     */
    protected static function getConverter(): MarkdownConverter
    {
        if (static::$converter === null) {
            $environment = new Environment([
                'html_input' => 'escape',  // Escape HTML to prevent XSS
                'allow_unsafe_links' => false,
            ]);
            
            $environment->addExtension(new CommonMarkCoreExtension());
            
            static::$converter = new MarkdownConverter($environment);
        }
        
        return static::$converter;
    }

    /**
     * Convert markdown to HTML
     *
     * @param string|null $markdown
     * @return string
     */
    public static function toHtml(?string $markdown): string
    {
        if (empty($markdown)) {
            return '';
        }

        return static::getConverter()->convert($markdown)->getContent();
    }
}
