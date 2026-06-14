<?php

namespace Happytodev\Blogr\Filament\Livewire;

use Filament\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Happytodev\Blogr\Services\LocaleService;
use Happytodev\Blogr\Services\Translation\TranslationProviderFactory;
use Happytodev\Blogr\Services\TranslationUsageService;
use Illuminate\Support\Facades\Log;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

class AuthorBio extends MyProfileComponent
{
    protected string $view = 'blogr::filament.livewire.author-bio';

    public ?array $data = [];

    public $user;

    public static $sort = 20;

    public function mount(): void
    {
        $this->user = auth()->user();

        $bio = $this->user->bio ?? [];
        if (is_string($bio)) {
            $bio = json_decode($bio, true) ?? [];
        }

        $this->form->fill(['bio' => $bio]);
    }

    public function form(Schema $schema): Schema
    {
        $locales = app(LocaleService::class)->getAvailable();
        $localeNames = $this->localeNames();
        $flags = $this->localeEmoji();

        $tabs = [];

        foreach ($locales as $locale) {
            $label = $localeNames[$locale] ?? strtoupper($locale);
            $flag = $flags[$locale] ?? '🌐';

            $tabs[] = Tabs\Tab::make($locale)
                ->label("{$flag} {$label}")
                ->schema([
                    MarkdownEditor::make("bio.{$locale}")
                        ->label(__('blogr::blogr.profile.bio_label', ['locale' => $label]))
                        ->maxLength(2000)
                        ->nullable()
                        ->helperText(__('blogr::blogr.profile.bio_help')),
                ]);
        }

        return $schema
            ->components([
                Tabs::make('bio_tabs')
                    ->tabs($tabs)
                    ->persistTabInQueryString('bio_tab'),
            ])
            ->statePath('data');
    }

    public function translateBioAction(): Action
    {
        return Action::make('translateBio')
            ->label(__('blogr::blogr.profile.translate_bio'))
            ->icon('heroicon-o-language')
            ->color('success')
            ->form([
                Select::make('source_locale')
                    ->label(__('blogr::blogr.profile.source_locale'))
                    ->options(fn () => $this->getSourceLocaleOptions())
                    ->required(),
                Select::make('target_locale')
                    ->label(__('blogr::blogr.profile.target_locale'))
                    ->options(fn () => $this->getTargetLocaleOptions())
                    ->required()
                    ->rule('different:source_locale'),
            ])
            ->action(function (array $data) {
                $this->translateBio($data['source_locale'], $data['target_locale']);
            });
    }

    protected function translateBio(string $sourceLocale, string $targetLocale): void
    {
        $provider = app(TranslationProviderFactory::class)->make();

        if (! $provider) {
            Notification::make()
                ->title(__('blogr::blogr.profile.translation_error'))
                ->body('No translation provider configured.')
                ->danger()
                ->send();

            return;
        }

        $formState = $this->form->getState();
        $bio = $formState['bio'] ?? [];
        $sourceText = $bio[$sourceLocale] ?? '';

        if (empty(trim($sourceText))) {
            Notification::make()
                ->title(__('blogr::blogr.profile.no_source_bio', ['locale' => $sourceLocale]))
                ->danger()
                ->send();

            return;
        }

        try {
            $translated = $provider->translate($sourceText, $sourceLocale, $targetLocale);

            $data = $this->form->getRawState();
            $data['bio'][$targetLocale] = $translated;
            $this->form->fill($data);

            $charCount = mb_strlen($sourceText) + mb_strlen($translated);
            app(TranslationUsageService::class)->trackUsage(
                config('blogr.translation.provider', 'none'),
                $charCount
            );

            Notification::make()
                ->title(__('blogr::blogr.profile.bio_translated', ['locale' => $targetLocale]))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('blogr::blogr.profile.translation_error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getSourceLocaleOptions(): array
    {
        $options = $this->localeOptions();
        $formState = $this->form->getState();
        $bio = $formState['bio'] ?? [];

        return collect($options)
            ->filter(fn ($label, $locale) => ! empty(trim($bio[$locale] ?? '')))
            ->toArray();
    }

    public function getTargetLocaleOptions(): array
    {
        return $this->localeOptions();
    }

    public function submit(): void
    {
        try {
            $data = $this->form->getState();
            $bio = $data['bio'] ?? [];

            $this->user->update(['bio' => $bio]);

            Notification::make()
                ->success()
                ->title(__('blogr::blogr.profile.bio_updated'))
                ->send();
        } catch (\Throwable $e) {
            Log::error('AuthorBio submit error', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title('Error saving biography')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function localeNames(): array
    {
        return [
            'en' => 'English', 'fr' => 'Français', 'es' => 'Español', 'de' => 'Deutsch',
            'it' => 'Italiano', 'pt' => 'Português', 'pl' => 'Polski', 'ru' => 'Русский',
            'nl' => 'Nederlands', 'el' => 'Ελληνικά', 'no' => 'Norsk', 'da' => 'Dansk',
            'sv' => 'Svenska', 'fi' => 'Suomi', 'cs' => 'Čeština', 'sk' => 'Slovenčina',
            'hu' => 'Magyar', 'ro' => 'Română', 'bg' => 'Български', 'sr' => 'Српски',
            'hr' => 'Hrvatski', 'sl' => 'Slovenščina', 'et' => 'Eesti', 'lv' => 'Latviešu',
            'lt' => 'Lietuvių', 'uk' => 'Українська', 'ja' => '日本語', 'zh' => '中文',
            'ko' => '한국어', 'ar' => 'العربية', 'hi' => 'हिन्दी', 'tr' => 'Türkçe',
            'th' => 'ไทย', 'vi' => 'Tiếng Việt', 'id' => 'Bahasa Indonesia',
        ];
    }

    private function localeOptions(): array
    {
        $names = $this->localeNames();
        $locales = app(LocaleService::class)->getAvailable();

        return collect($locales)
            ->mapWithKeys(fn ($l) => [$l => ($names[$l] ?? strtoupper($l))." ({$l})"])
            ->toArray();
    }

    private function localeEmoji(): array
    {
        return [
            'en' => '🇬🇧', 'fr' => '🇫🇷', 'es' => '🇪🇸', 'de' => '🇩🇪',
            'it' => '🇮🇹', 'pt' => '🇵🇹', 'pl' => '🇵🇱', 'ru' => '🇷🇺',
            'nl' => '🇳🇱', 'el' => '🇬🇷', 'no' => '🇳🇴', 'da' => '🇩🇰',
            'sv' => '🇸🇪', 'fi' => '🇫🇮', 'cs' => '🇨🇿', 'sk' => '🇸🇰',
            'hu' => '🇭🇺', 'ro' => '🇷🇴', 'bg' => '🇧🇬', 'sr' => '🇷🇸',
            'hr' => '🇭🇷', 'sl' => '🇸🇮', 'et' => '🇪🇪', 'lv' => '🇱🇻',
            'lt' => '🇱🇹', 'uk' => '🇺🇦', 'ja' => '🇯🇵', 'zh' => '🇨🇳',
            'ko' => '🇰🇷', 'ar' => '🇸🇦', 'hi' => '🇮🇳', 'tr' => '🇹🇷',
            'th' => '🇹🇭', 'vi' => '🇻🇳', 'id' => '🇮🇩',
        ];
    }
}
