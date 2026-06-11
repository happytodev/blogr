<?php

namespace Happytodev\Blogr\Services\Translation;

interface TranslationProvider
{
    public function isAvailable(): bool;

    public function translate(string $text, string $sourceLocale, string $targetLocale): string;

    /** @param  array<int, array>  $blocks */
    public function translateBlocks(array $blocks, string $sourceLocale, string $targetLocale): array;
}
