<?php

namespace Happytodev\Blogr\Services\Translation;

class CodeBlockPreserver
{
    private array $blocks = [];

    public function protect(string $text): string
    {
        $this->blocks = [];

        $text = preg_replace_callback('/```[\s\S]*?```/', function ($match) {
            $placeholder = "___BLOGR_CODE_{$this->hash($match[0])}___";
            $this->blocks[$placeholder] = $match[0];

            return $placeholder;
        }, $text);

        $text = preg_replace_callback('/`[^`\n]+`/', function ($match) {
            $placeholder = "___BLOGR_CODE_{$this->hash($match[0])}___";
            $this->blocks[$placeholder] = $match[0];

            return $placeholder;
        }, $text);

        return $text;
    }

    public function restore(string $text): string
    {
        foreach ($this->blocks as $placeholder => $code) {
            $text = str_replace($placeholder, $code, $text);
        }

        return $text;
    }

    private function hash(string $value): string
    {
        return md5($value);
    }

    public function translateContent(
        TranslationProvider $provider,
        string $content,
        string $sourceLocale,
        string $targetLocale
    ): string {
        if (empty(trim($content))) {
            return $content;
        }

        $protected = $this->protect($content);

        $translated = $provider->translate($protected, $sourceLocale, $targetLocale);

        return $this->restore($translated);
    }
}
