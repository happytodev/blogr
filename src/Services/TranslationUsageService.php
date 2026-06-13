<?php

namespace Happytodev\Blogr\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TranslationUsageService
{
    public const FREE_LIMITS = [
        'azure' => 2_000_000,
        'google' => 500_000,
    ];

    public const PROVIDER_LABELS = [
        'libretranslate' => 'LibreTranslate (self-hosted)',
        'azure' => 'Azure Translator',
        'google' => 'Google Cloud Translation',
        'openai' => 'OpenAI (GPT-4o-mini)',
    ];

    public function trackUsage(string $provider, int $charCount): void
    {
        $now = now();
        $month = $now->month;
        $year = $now->year;

        $existing = DB::table('blogr_translation_usage')
            ->where('provider', $provider)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existing) {
            DB::table('blogr_translation_usage')
                ->where('id', $existing->id)
                ->update(['char_count' => DB::raw("char_count + {$charCount}")]);
        } else {
            DB::table('blogr_translation_usage')->insert([
                'provider' => $provider,
                'month' => $month,
                'year' => $year,
                'char_count' => $charCount,
            ]);
        }

        Cache::forget($this->cacheKey($provider));
    }

    public function getUsageStats(?string $provider): ?array
    {
        if (! $provider || $provider === 'none') {
            return null;
        }

        $now = now();

        return Cache::remember($this->cacheKey($provider), 3600, function () use ($provider, $now) {
            $row = DB::table('blogr_translation_usage')
                ->where('provider', $provider)
                ->where('month', $now->month)
                ->where('year', $now->year)
                ->first();

            $used = $row ? (int) $row->char_count : 0;
            $limit = self::FREE_LIMITS[$provider] ?? null;

            return [
                'provider' => $provider,
                'provider_label' => self::PROVIDER_LABELS[$provider] ?? ucfirst($provider),
                'used' => $used,
                'limit' => $limit,
                'has_limit' => $limit !== null,
                'remaining' => $limit !== null ? max(0, $limit - $used) : null,
                'percentage' => $limit !== null ? round(($used / $limit) * 100, 2) : null,
                'period' => $now->copy()->startOfMonth()->format('j').'–'.$now->format('j F Y'),
            ];
        });
    }

    private function cacheKey(string $provider): string
    {
        $now = now();

        return "blogr_translation_usage_{$provider}_{$now->year}_{$now->month}";
    }
}
