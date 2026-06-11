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

    public function mount(int|string $record): void
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
                    'xl' => 3,
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

        // Get the source/default translation block count for comparison
        $sourceBlocksCount = 0;
        $sourceTranslation = $record->translations->firstWhere('locale', $defaultLocale);
        if ($sourceTranslation && isset($sourceTranslation->blocks)) {
            $sourceBlocks = $sourceTranslation->blocks;
            $sourceBlocksCount = is_array($sourceBlocks) ? count($sourceBlocks) : 0;
        }

        foreach ($sortedTranslations as $translation) {
            $locale = $translation->locale;
            $flag = $flags[$locale] ?? '🌐';
            $localeUpper = strtoupper($locale);
            $title = $translation->title ?? '—';
            $isDefault = $locale === $defaultLocale;
            $isComplete = $translation->is_complete ?? false;
            $blockCount = is_array($translation->blocks) ? count($translation->blocks) : 0;

            $hasTitle = ! empty($translation->title);
            $hasAllBlocks = ! $isDefault && $blockCount >= $sourceBlocksCount;
            $hasSomeBlocks = $blockCount > 0;
            $isCopy = ! $isDefault && $sourceTranslation && $sourceTranslation->blocks === $translation->blocks;

            $blocksLabel = $hasSomeBlocks
                ? "{$blockCount} bloc".($blockCount > 1 ? 's' : '')
                : 'Aucun bloc';

            if ($isDefault) {
                $statusIcon = '✅';
                $borderColor = '#22c55e';
                $bgColor = '#f0fdf4';
                $badgeBg = '#dcfce7';
                $badgeColor = '#166534';
                $statusLabel = 'Défaut';
            } elseif ($isComplete) {
                $statusIcon = '✅';
                $borderColor = '#22c55e';
                $bgColor = '#f0fdf4';
                $badgeBg = '#dcfce7';
                $badgeColor = '#166534';
                $statusLabel = 'Traduite';
            } elseif ($isCopy) {
                $statusIcon = '🔄';
                $borderColor = '#818cf8';
                $bgColor = '#eef2ff';
                $badgeBg = '#c7d2fe';
                $badgeColor = '#3730a3';
                $statusLabel = 'Copiée';
            } elseif ($hasAllBlocks) {
                $statusIcon = '🟦';
                $borderColor = '#60a5fa';
                $bgColor = '#eff6ff';
                $badgeBg = '#bfdbfe';
                $badgeColor = '#1e40af';
                $statusLabel = 'En cours';
            } elseif ($hasSomeBlocks) {
                $statusIcon = '🟡';
                $borderColor = '#eab308';
                $bgColor = '#fefce8';
                $badgeBg = '#fef9c3';
                $badgeColor = '#854d0e';
                $statusLabel = 'Partielle';
            } else {
                $statusIcon = '⭕';
                $borderColor = '#fca5a5';
                $bgColor = '#fef2f2';
                $badgeBg = '#fecaca';
                $badgeColor = '#991b1b';
                $statusLabel = 'Vide';
            }

            $editUrl = CmsPageResource::getUrl('edit-translation', [
                'record' => $record,
                'translation' => $translation,
            ]);

            $defaultBadge = $isDefault
                ? '<span style="display:inline-flex;align-items:center;font-size:0.65rem;font-weight:500;color:#fff;background:#4f46e5;border-radius:9999px;padding:0.0625rem 0.375rem;flex-shrink:0;">Défaut</span>'
                : '';

            $html = <<<HTML
<a href="{$editUrl}" style="display:block;padding:1rem;border-radius:0.75rem;border:2px solid {$borderColor};background:{$bgColor};text-decoration:none;transition:box-shadow 0.2s;min-height:120px;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem;margin-bottom:0.75rem;">
        <div style="display:flex;align-items:center;gap:0.5rem;min-width:0;">
            <span style="font-size:1.5rem;flex-shrink:0;">{$flag}</span>
            <div>
                <div style="display:flex;align-items:center;gap:0.25rem;flex-wrap:wrap;">
                    <span style="font-weight:700;color:#111827;font-size:0.875rem;white-space:nowrap;">{$localeUpper}</span>
                    {$defaultBadge}
                </div>
                <div style="font-size:0.875rem;color:#6b7280;margin-top:0.125rem;line-height:1.25;">{$title}</div>
            </div>
        </div>
        <span style="display:inline-flex;align-items:center;gap:0.25rem;font-size:0.75rem;font-weight:500;padding:0.25rem 0.5rem;border-radius:9999px;background:{$badgeBg};color:{$badgeColor};white-space:nowrap;flex-shrink:0;">
            {$statusIcon} {$statusLabel}
        </span>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;font-size:0.75rem;color:#9ca3af;">
        <span>{$blocksLabel}</span>
        <span style="color:#6366f1;">Modifier →</span>
    </div>
</a>
HTML;

            $items[] = Html::make(new HtmlString($html))
                ->columnSpan(1);
        }

        // Legend row
        $legend = <<<'HTML'
<div style="display:flex;align-items:center;justify-content:center;gap:1.5rem;margin-top:0.5rem;padding-top:0.75rem;font-size:0.75rem;color:#6b7280;">
    <span>✅ Traduite</span>
    <span>🔄 Copiée</span>
    <span>🟦 En cours</span>
    <span>🟡 Partielle</span>
    <span>⭕ Vide</span>
</div>
HTML;

        $items[] = Html::make(new HtmlString($legend))
            ->columnSpanFull();

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
                                        .($locale === $defaultLocale ? ' (défaut)' : ''),
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
