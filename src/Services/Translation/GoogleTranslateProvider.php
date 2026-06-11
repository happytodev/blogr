<?php

namespace Happytodev\Blogr\Services\Translation;

use Illuminate\Support\Facades\Http;

class GoogleTranslateProvider implements TranslationProvider
{
    public function isAvailable(): bool
    {
        return ! empty(config('blogr.translation.google.api_key'));
    }

    public function translate(string $text, string $sourceLocale, string $targetLocale): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        $apiKey = config('blogr.translation.google.api_key');

        $response = Http::timeout(30)->get('https://translation.googleapis.com/language/translate/v2', [
            'key' => $apiKey,
            'q' => $text,
            'source' => $sourceLocale,
            'target' => $targetLocale,
        ]);

        return $response->json('data.translations.0.translatedText', $text);
    }

    public function translateBlocks(array $blocks, string $sourceLocale, string $targetLocale): array
    {
        return (new BlockTranslator($this))->translateBlocks($blocks, $sourceLocale, $targetLocale);
    }
}
