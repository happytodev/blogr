<?php

namespace Happytodev\Blogr\Filament\Resources\CmsPageResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Happytodev\Blogr\Filament\Resources\CmsPageResource;
use Happytodev\Blogr\Filament\Resources\CmsPages\CmsBlockBuilder;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Models\CmsPageVersion;
use Happytodev\Blogr\Services\Translation\TranslationProvider;
use Happytodev\Blogr\Services\Translation\TranslationProviderFactory;
use Happytodev\Blogr\Services\TranslationUsageService;
use Happytodev\Blogr\Services\VersioningService;
use Happytodev\Blogr\Traits\AutoSave;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EditCmsPageTranslation extends EditRecord
{
    use AutoSave;

    protected static string $resource = CmsPageResource::class;

    public ?CmsPage $cmsPage = null;

    public function areFormActionsSticky(): bool
    {
        return true;
    }

    public function mount(int|string $record = '', int|string $translation = ''): void
    {
        static::authorizeResourceAccess();

        $this->cmsPage = CmsPage::findOrFail($record);
        $this->record = CmsPageTranslation::findOrFail($translation);

        abort_unless(static::getResource()::canEdit($this->cmsPage), 403);

        $this->fillForm();
        $this->initializeAutoSave();
    }

    public function form(Schema $schema): Schema
    {
        $components = [
            Forms\Components\Placeholder::make('locale_display')
                ->label(__('Locale'))
                ->content(fn () => strtoupper($this->record?->locale ?? '')),

            Forms\Components\TextInput::make('title')
                ->label(__('Titre'))
                ->required()
                ->maxLength(255)
                ->columnSpan(2),

            Forms\Components\TextInput::make('slug')
                ->label(__('Slug'))
                ->required()
                ->maxLength(255)
                ->helperText(__('URL traduite de la page'))
                ->columnSpan(2),

            Forms\Components\Textarea::make('excerpt')
                ->label(__('Extrait'))
                ->maxLength(500)
                ->rows(3)
                ->columnSpan(2),

            Forms\Components\TextInput::make('seo_title')
                ->label(__('Titre SEO'))
                ->maxLength(255)
                ->helperText(__('Titre pour les moteurs de recherche (60 caractères max)'))
                ->columnSpan(2),

            Forms\Components\Textarea::make('seo_description')
                ->label(__('Description SEO'))
                ->maxLength(160)
                ->rows(2)
                ->helperText(__('Description pour les moteurs de recherche (160 caractères max)'))
                ->columnSpan(2),

            Forms\Components\TextInput::make('seo_keywords')
                ->label(__('Mots-clés SEO'))
                ->maxLength(255)
                ->helperText(__('Mots-clés séparés par des virgules'))
                ->columnSpan(2),

            Forms\Components\Toggle::make('is_complete')
                ->label('Traduction terminée')
                ->helperText('Cochez cette case lorsque la traduction est entièrement relue et validée.')
                ->default(false)
                ->columnSpan(2),

            Section::make(__('Content Blocks'))
                ->description(__('Ajoutez des blocs de contenu pour cette langue'))
                ->schema([
                    CmsBlockBuilder::make(),
                ])
                ->collapsible()
                ->collapsed()
                ->columnSpan(2),

            View::make('blogr::components.auto-save-indicator'),
        ];

        return $schema
            ->schema($components)
            ->columns(2);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $draft = app(VersioningService::class)->getDraft($this->record);
        if ($draft && isset($draft->draft_data)) {
            $data = array_merge($data, $draft->draft_data);
        }

        return $data;
    }

    protected function authorizeAccess(): void
    {
        static::authorizeResourceAccess();
        abort_unless(static::getResource()::canEdit($this->cmsPage), 403);
    }

    protected function getHeaderActions(): array
    {
        $targetLocale = $this->record?->locale ?? 'en';
        $targetLabel = strtoupper($targetLocale);
        $sourceLocale = $this->cmsPage->default_locale ?? config('blogr.locales.default', 'en');
        $isDefaultLocale = $targetLocale === $sourceLocale;
        $actions = [];

        if (! $isDefaultLocale) {
            $provider = app(TranslationProviderFactory::class)->make();

            if ($provider->isAvailable()) {
                $actions[] = Actions\Action::make('translateWithAI')
                    ->label("Traduire en {$targetLabel} (IA)")
                    ->icon('heroicon-o-language')
                    ->color('primary')
                    ->action(function () use ($provider, $sourceLocale, $targetLocale) {
                        $this->translateWithAI($provider, $sourceLocale, $targetLocale);
                    });
            } else {
                $actions[] = Actions\Action::make('configureAI')
                    ->label('Configurer la traduction IA')
                    ->icon('heroicon-o-language')
                    ->color('gray')
                    ->url(url('/admin/blogr-settings?tab=ai-translation'))
                    ->openUrlInNewTab(false);
            }

            $actions[] = Actions\Action::make('syncBlocks')
                ->label('Synchroniser les blocs depuis '.strtoupper($sourceLocale))
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () use ($sourceLocale) {
                    $this->syncMissingBlocks($sourceLocale);
                });
        }

        $actions[] = Actions\Action::make('history')
            ->label('History')
            ->icon('heroicon-o-clock')
            ->color('gray')
            ->modalContent(function () {
                // Current draft entry with diff vs last published version
                $draft = app(VersioningService::class)->getDraft($this->record);
                $draftEntry = null;
                if ($draft && isset($draft->draft_data)) {
                    $fieldKeys = ['title', 'slug', 'seo_title', 'seo_description', 'seo_keywords', 'blocks'];
                    $draftFields = array_intersect_key($draft->draft_data, array_flip($fieldKeys));
                    $lastVersion = app(VersioningService::class)->listVersions($this->record)
                        ->sortByDesc('version_number')
                        ->first();
                    if ($lastVersion) {
                        $versionFields = $lastVersion->only($fieldKeys);
                        $normalize = fn ($v) => json_encode(is_array($v) ? array_values($v) : $v);
                        $changes = array_keys(array_diff_assoc(
                            array_map($normalize, $draftFields),
                            array_map($normalize, $versionFields)
                        ));
                        $previousFields = $versionFields;
                    } else {
                        $changes = ['initial'];
                        $previousFields = null;
                    }
                    $draftEntry = [
                        'type' => 'draft',
                        'title' => $draft->draft_data['title'] ?? $this->record->title ?? 'Untitled',
                        'created_at' => $draft->updated_at ?? $draft->created_at,
                        'fields' => $draftFields,
                        'previous_fields' => $previousFields,
                        'changes' => $changes,
                    ];
                }

                // Version entries
                $versions = app(VersioningService::class)->listVersions($this->record);
                $prevVersion = null;
                $history = collect();

                foreach ($versions->sortBy('version_number') as $v) {
                    $currentFields = $v->only([
                        'title', 'slug', 'seo_title', 'seo_description', 'seo_keywords', 'blocks',
                    ]);
                    $previousFields = $prevVersion ? $prevVersion->only([
                        'title', 'slug', 'seo_title', 'seo_description', 'seo_keywords', 'blocks',
                    ]) : [];
                    $normalize = fn ($v) => json_encode(is_array($v) ? array_values($v) : $v);
                    $changes = $prevVersion
                        ? array_keys(array_diff_assoc(
                            array_map($normalize, $currentFields),
                            array_map($normalize, $previousFields)
                        ))
                        : ['initial'];

                    $history->push([
                        'type' => 'version',
                        'title' => $v->title,
                        'version_number' => $v->version_number,
                        'version_id' => $v->id,
                        'created_at' => $v->created_at,
                        'fields' => $currentFields,
                        'previous_fields' => $prevVersion ? $previousFields : null,
                        'changes' => $changes,
                    ]);
                    $prevVersion = $v;
                }

                $history = collect($draftEntry ? [$draftEntry] : [])
                    ->concat($history)
                    ->sortByDesc('created_at')
                    ->take(50);

                return view('blogr::components.version-history', [
                    'history' => $history,
                ]);
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');

        return $actions;
    }

    protected function syncMissingBlocks(string $sourceLocale): void
    {
        $sourceTranslation = $this->cmsPage->translations()
            ->where('locale', $sourceLocale)
            ->first();

        if (! $sourceTranslation) {
            Notification::make()->title('Aucune traduction source')->danger()->send();

            return;
        }

        $sourceBlocks = $sourceTranslation->blocks ?? [];
        $currentBlocks = $this->record->blocks ?? [];

        // Build map of existing block types: ['hero' => true, 'features' => true, ...]
        $existingTypes = [];
        foreach ($currentBlocks as $block) {
            $type = $block['type'] ?? '';
            if ($type) {
                $existingTypes[$type] = true;
            }
        }

        $addedCount = 0;
        $result = [];

        // First pass: keep target's existing blocks in order, check for gaps
        $usedPositions = [];
        foreach ($sourceBlocks as $i => $sourceBlock) {
            $type = $sourceBlock['type'] ?? '';

            if ($type && isset($existingTypes[$type])) {
                // Find this block type in the target (preserve target's content)
                $targetBlock = collect($currentBlocks)->firstWhere('type', $type);
                if ($targetBlock) {
                    $result[$i] = $targetBlock;
                    $usedPositions[$i] = true;
                } else {
                    // Shouldn't happen, but add the source version
                    $result[$i] = $sourceBlock;
                    $usedPositions[$i] = true;
                }
            } else {
                // Type doesn't exist in target — mark for insertion
                $result[$i] = null; // placeholder
                $addedCount++;
            }
        }

        // Second pass: fill placeholders with source blocks (new content)
        $insertIndex = 0;
        foreach ($result as $i => &$slot) {
            if ($slot === null) {
                $slot = $sourceBlocks[$i];
            }
        }
        unset($slot);

        // Third pass: keep any target-only blocks (custom blocks not in source)
        foreach ($currentBlocks as $block) {
            $type = $block['type'] ?? '';
            $isInSource = $type && collect($sourceBlocks)->firstWhere('type', $type);
            if (! $isInSource) {
                $result[] = $block;
            }
        }

        $result = array_values($result);

        if ($addedCount === 0) {
            Notification::make()
                ->title('Aucun bloc manquant — les deux versions sont synchronisées')
                ->info()
                ->send();

            return;
        }

        $this->record->blocks = $result;
        $this->record->save();
        $this->fillForm();

        Notification::make()
            ->title("{$addedCount} bloc(s) ajouté(s) depuis ".strtoupper($sourceLocale))
            ->success()
            ->send();
    }

    protected function translateWithAI(TranslationProvider $provider, string $sourceLocale, string $targetLocale): void
    {
        $sourceTranslation = $this->cmsPage->translations()
            ->where('locale', $sourceLocale)
            ->first();

        if (! $sourceTranslation) {
            Notification::make()
                ->title("Aucune traduction source trouvée pour {$sourceLocale}")
                ->danger()
                ->send();

            return;
        }

        try {
            $this->record->title = $provider->translate(
                $sourceTranslation->title, $sourceLocale, $targetLocale
            );
            $this->record->slug = Str::slug($provider->translate(
                Str::headline($sourceTranslation->slug), $sourceLocale, $targetLocale
            ));
            $this->record->blocks = $provider->translateBlocks(
                $sourceTranslation->blocks ?? [], $sourceLocale, $targetLocale
            );

            $this->record->save();

            $charCount = mb_strlen($sourceTranslation->title)
                + mb_strlen($this->record->title)
                + mb_strlen($sourceTranslation->slug)
                + mb_strlen($this->record->slug)
                + mb_strlen(json_encode($sourceTranslation->blocks ?? []))
                + mb_strlen(json_encode($this->record->blocks ?? []));

            app(TranslationUsageService::class)->trackUsage(
                config('blogr.translation.provider', 'none'),
                $charCount
            );

            $this->fillForm();

            Notification::make()
                ->title("Traduction {$targetLocale} effectuée avec l'IA")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur de traduction')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        $actions = [
            Actions\Action::make('saveAndPublish')
                ->label('Save & Publish')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action('saveAndPublish'),
            Actions\Action::make('saveAsDraft')
                ->label('Save as Draft')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('gray')
                ->action('saveAsDraft'),
        ];

        if ($this->cmsPage) {
            $actions[] = Actions\Action::make('saveAndBack')
                ->label('Save & Back')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->action(function () {
                    $this->saveAndPublish();
                    $this->redirect(CmsPageResource::getUrl('edit', ['record' => $this->cmsPage]));
                });
        }

        $actions[] = $this->getCancelFormAction();

        return $actions;
    }

    public function saveAsDraft(): void
    {
        $data = $this->data ?? [];
        app(VersioningService::class)->saveDraft($this->record, $data);

        $this->lastAutoSaveAt = now()->toIso8601String();
        $this->hasUnsavedChanges = false;
        $this->refreshDraftState();
        $this->record->load('page');

        Notification::make()
            ->title('Draft saved')
            ->success()
            ->send();
    }

    public function saveAndPublish(): void
    {
        $data = $this->data ?? [];

        if (isset($data['blocks'])) {
            Log::debug('[BLOGR DEBUG] saveAndPublish form data blocks', [
                'blocks' => $data['blocks'],
                'blocks_json' => json_encode($data['blocks']),
            ]);
        }

        app(VersioningService::class)->saveDraft($this->record, $data);
        app(VersioningService::class)->publish($this->record);

        $this->lastAutoSaveAt = now()->toIso8601String();
        $this->hasUnsavedChanges = false;
        $this->refreshDraftState();
        $this->record->load('page');
        $this->fillForm();

        Notification::make()
            ->title('Page published')
            ->success()
            ->send();
    }

    public function save(?bool $shouldRedirect = null, bool $shouldSendNotification = true): void
    {
        $data = $this->data ?? [];

        if (isset($data['blocks'])) {
            Log::debug('[BLOGR DEBUG] save() form data blocks', [
                'is_published' => $this->record?->page->is_published ?? false,
                'blocks' => $data['blocks'],
                'blocks_json' => json_encode($data['blocks']),
            ]);
        }

        if ($this->record && $this->record->page?->is_published) {
            app(VersioningService::class)->saveDraft($this->record, $data);

            $this->lastAutoSaveAt = now()->toIso8601String();
            $this->hasUnsavedChanges = false;
            $this->refreshDraftState();
            $this->record->load('page');

            $this->fillForm();

            Notification::make()
                ->title('Draft saved')
                ->success()
                ->send();
        } else {
            parent::save($shouldRedirect ?? true, $shouldSendNotification);
        }
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        $locale = strtoupper($this->record?->locale ?? '');
        $title = $this->record?->title ?? '';

        return "Traduction {$locale} — {$title} enregistrée";
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();
        $breadcrumbs = [
            $resource::getUrl() => $resource::getBreadcrumb(),
            $resource::getUrl('edit', ['record' => $this->cmsPage]) => $this->cmsPage->slug,
        ];

        if ($this->record) {
            $breadcrumbs[] = strtoupper($this->record->locale).' — '.($this->record->title ?? '');
        }

        return $breadcrumbs;
    }

    public function restoreVersion(int $versionId): void
    {
        $version = CmsPageVersion::findOrFail($versionId);

        if ($version->cms_page_translation_id !== $this->record->id) {
            return;
        }

        app(VersioningService::class)->rollback($this->record, $versionId);

        $this->lastAutoSaveAt = now()->toIso8601String();
        $this->hasUnsavedChanges = false;
        $this->record->load('page');
        $this->fillForm();

        Notification::make()
            ->title("Version {$version->version_number} restored to draft")
            ->success()
            ->send();
    }
}
