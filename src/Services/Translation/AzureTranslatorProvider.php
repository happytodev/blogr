<?php

namespace Happytodev\Blogr\Services\Translation;

use Illuminate\Support\Facades\Http;

class AzureTranslatorProvider implements TranslationProvider
{
    public function isAvailable(): bool
    {
        return ! empty(config('blogr.translation.azure.api_key'))
            && ! empty(config('blogr.translation.azure.region'));
    }

    public function translate(string $text, string $sourceLocale, string $targetLocale): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        $apiKey = config('blogr.translation.azure.api_key');
        $region = config('blogr.translation.azure.region');

        $response = Http::timeout(30)->withHeaders([
            'Ocp-Apim-Subscription-Key' => $apiKey,
            'Ocp-Apim-Subscription-Region' => $region,
            'Content-Type' => 'application/json',
        ])->post("https://api.cognitive.microsofttranslator.com/translate?api-version=3.0&from={$sourceLocale}&to={$targetLocale}", [
            ['Text' => $text],
        ]);

        return $response->json('0.translations.0.text', $text);
    }

    public function translateBlocks(array $blocks, string $sourceLocale, string $targetLocale): array
    {
        return (new BlockTranslator($this))->translateBlocks($blocks, $sourceLocale, $targetLocale);
    }
}
