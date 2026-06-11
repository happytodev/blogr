<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Services\LocaleService;
use Happytodev\Blogr\Services\Translation\CodeBlockPreserver;
use Happytodev\Blogr\Services\Translation\TranslationProviderFactory;
use Happytodev\Blogr\Services\TranslationUsageService;
use Illuminate\Support\Str;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        $provider = app(TranslationProviderFactory::class)->make();

        if (! $provider) {
            return [];
        }

        $existingLocales = $this->record->translations()
            ->pluck('locale')
            ->toArray();

        $allLocales = app(LocaleService::class)->getAvailable();

        $sourceOptions = collect($allLocales)
            ->filter(fn ($l) => in_array($l, $existingLocales))
            ->mapWithKeys(fn ($l) => [$l => app(LocaleService::class)->localeLabel($l)])
            ->toArray();

        $targetOptions = collect($allLocales)
            ->mapWithKeys(fn ($l) => [$l => app(LocaleService::class)->localeLabel($l)])
            ->toArray();

        return [
            Actions\Action::make('translateWithAI')
                ->label('Translate with AI')
                ->icon('heroicon-o-language')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('source_locale')
                        ->label('Source language')
                        ->options($sourceOptions)
                        ->default($this->record->default_locale)
                        ->required(),
                    \Filament\Forms\Components\Select::make('target_locale')
                        ->label('Target language')
                        ->options($targetOptions)
                        ->required()
                        ->rule('different:source_locale'),
                ])
                ->action(function (array $data) use ($provider) {
                    $this->translateWithAI(
                        $provider,
                        $data['source_locale'],
                        $data['target_locale']
                    );
                }),
        ];
    }

    protected function translateWithAI($provider, string $sourceLocale, string $targetLocale): void
    {
        $sourceTranslation = $this->record->translations()
            ->where('locale', $sourceLocale)
            ->first();

        if (! $sourceTranslation) {
            Notification::make()
                ->title("No source translation found for {$sourceLocale}")
                ->danger()
                ->send();

            return;
        }

        try {
            $fields = [
                'title', 'tldr', 'content', 'seo_title', 'seo_description', 'seo_keywords',
            ];

            $translated = [];
            $charCount = 0;
            $preserver = new CodeBlockPreserver;

            foreach ($fields as $field) {
                $sourceValue = $sourceTranslation->{$field} ?? '';
                if (! empty(trim($sourceValue))) {
                    $translatedValue = $field === 'content'
                        ? $preserver->translateContent($provider, $sourceValue, $sourceLocale, $targetLocale)
                        : $provider->translate($sourceValue, $sourceLocale, $targetLocale);
                    $translated[$field] = $translatedValue;
                    $charCount += mb_strlen($sourceValue) + mb_strlen($translatedValue);
                }
            }

            $translated['slug'] = Str::slug($provider->translate(
                Str::headline($sourceTranslation->slug), $sourceLocale, $targetLocale
            ));

            $targetTranslation = $this->record->translations()
                ->where('locale', $targetLocale)
                ->first();

            if ($targetTranslation) {
                $targetTranslation->update($translated);
            } else {
                $translated['locale'] = $targetLocale;
                $translated['blog_post_id'] = $this->record->id;
                BlogPostTranslation::create($translated);
            }

            $this->record->load('translations');
            $this->refreshFormData(['translations']);

            app(TranslationUsageService::class)->trackUsage(
                config('blogr.translation.provider', 'none'),
                $charCount
            );

            Notification::make()
                ->title("Translation {$targetLocale} completed with AI")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Translation error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Notification is dispatched from BlogPost model's created() hook for new posts
    // For updates, admins don't receive notifications (as per original design)

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['blog_series_id']) && isset($data['series_position'])) {
            if ($data['series_position'] === 'auto-top') {
                BlogPost::where('blog_series_id', $data['blog_series_id'])
                    ->increment('series_position');
                $data['series_position'] = 1;
            } elseif ($data['series_position'] === 'auto-bottom') {
                $data['series_position'] = null;
            } elseif ($data['series_position'] === 'custom') {
                $data['series_position'] = $data['series_position_custom'] ?? null;
            }
        }
        unset($data['series_position_custom']);

        return $data;
    }
}
