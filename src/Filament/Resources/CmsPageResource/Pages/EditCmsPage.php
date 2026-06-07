<?php

namespace Happytodev\Blogr\Filament\Resources\CmsPageResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Happytodev\Blogr\Filament\Resources\CmsPageResource;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Services\LocaleService;
use Illuminate\Support\HtmlString;

class EditCmsPage extends EditRecord
{
    protected static string $resource = CmsPageResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->record?->loadMissing('translations');
    }

    public function form(Schema $schema): Schema
    {
        $schema = static::getResource()::form($schema);

        $schema->components([
            ...$schema->getComponents(),
            Section::make(__('Traductions'))
                ->description(__('Sélectionnez une langue pour éditer son contenu et ses blocs'))
                ->schema(fn () => $this->getTranslationGrid())
                ->columns([
                    'default' => 1,
                    'sm' => 2,
                    'lg' => 3,
                    'xl' => 4,
                ])
                ->columnSpanFull(),
        ]);

        return $schema;
    }

    protected function getTranslationGrid(): array
    {
        $record = $this->getRecord();
        if (! $record) {
            return [
                Text::make(__('Aucune traduction disponible.')),
            ];
        }

        $record->loadMissing('translations');

        $defaultLocale = $record->default_locale;

        $flags = [
            'en' => '🇬🇧', 'fr' => '🇫🇷', 'es' => '🇪🇸', 'de' => '🇩🇪',
            'pt' => '🇵🇹', 'it' => '🇮🇹', 'pl' => '🇵🇱', 'ru' => '🇷🇺',
            'el' => '🇬🇷', 'no' => '🇳🇴', 'nl' => '🇳🇱', 'sv' => '🇸🇪',
            'da' => '🇩🇰', 'fi' => '🇫🇮', 'cs' => '🇨🇿', 'hu' => '🇭🇺',
            'ro' => '🇷🇴', 'uk' => '🇺🇦', 'tr' => '🇹🇷', 'ja' => '🇯🇵',
            'zh' => '🇨🇳', 'ar' => '🇸🇦', 'hi' => '🇮🇳', 'ko' => '🇰🇷',
        ];

        $items = [];

        $sortedTranslations = $record->translations->sortBy('locale');

        foreach ($sortedTranslations as $translation) {
            $locale = $translation->locale;
            $flag = $flags[$locale] ?? '🌐';
            $localeUpper = strtoupper($locale);
            $title = $translation->title ?? '—';
            $isDefault = $locale === $defaultLocale;
            $blockCount = is_array($translation->blocks) ? count($translation->blocks) : 0;
            $blocksLabel = $blockCount > 0
                ? "{$blockCount} bloc" . ($blockCount > 1 ? 's' : '')
                : 'Aucun bloc';

            $editUrl = CmsPageResource::getUrl('edit-translation', [
                'record' => $record,
                'translation' => $translation,
            ]);

            $defaultBadge = $isDefault
                ? '<span class="inline-flex items-center text-xs font-medium text-white bg-indigo-600 rounded-full px-2 py-0.5 ml-2">Défaut</span>'
                : '';

            $statusDot = $blockCount > 0
                ? '<span class="inline-block w-2 h-2 rounded-full bg-green-500 mr-2"></span>'
                : '<span class="inline-block w-2 h-2 rounded-full bg-gray-300 mr-2"></span>';

            $html = <<<HTML
<a href="{$editUrl}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all group">
    <span class="text-xl flex-shrink-0">{$flag}</span>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-1">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">{$localeUpper}</span>
            {$defaultBadge}
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400 truncate">{$title}</div>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
        <span class="text-xs text-gray-400 dark:text-gray-500">{$blocksLabel}</span>
        <span class="text-gray-400 group-hover:text-indigo-500 transition-colors text-lg leading-none">→</span>
    </div>
</a>
HTML;

            $items[] = Html::make(new HtmlString($html))
                ->columnSpan(1);
        }

        return $items;
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        return [
            Actions\Action::make('addTranslation')
                ->label(__('Ajouter une traduction'))
                ->icon('heroicon-o-language')
                ->color('gray')
                ->form([
                    Forms\Components\Select::make('locale')
                        ->label(__('Langue'))
                        ->options(function () {
                            $record = $this->getRecord();
                            $existingLocales = $record->translations->pluck('locale')->toArray();
                            $localeService = app(LocaleService::class);
                            $allLocales = $localeService->getAvailable();
                            $defaultLocale = $record->default_locale;

                            return collect($allLocales)
                                ->reject(fn ($locale) => in_array($locale, $existingLocales))
                                ->mapWithKeys(fn ($locale) => [
                                    $locale => $localeService->localeLabel($locale)
                                        . ($locale === $defaultLocale ? ' (défaut)' : ''),
                                ]);
                        })
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();
                    $translation = CmsPageTranslation::create([
                        'cms_page_id' => $record->id,
                        'locale' => $data['locale'],
                        'slug' => $record->slug,
                        'title' => $record->slug,
                    ]);

                    Notification::make()
                        ->title(__('Traduction ajoutée'))
                        ->success()
                        ->send();

                    $this->redirect(CmsPageResource::getUrl('edit-translation', [
                        'record' => $record,
                        'translation' => $translation,
                    ]));
                }),
            Actions\DeleteAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();
        $breadcrumbs = [
            $resource::getUrl() => $resource::getBreadcrumb(),
        ];

        $record = $this->getRecord();
        if ($record) {
            $breadcrumbs[] = $record->slug;
        }

        return $breadcrumbs;
    }
}
