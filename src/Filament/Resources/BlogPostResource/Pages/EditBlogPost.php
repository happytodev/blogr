<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;
use Happytodev\Blogr\Filament\Resources\BlogPosts\BlogPostForm;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\BlogPostVersion;
use Happytodev\Blogr\Services\LocaleService;
use Happytodev\Blogr\Services\Translation\CodeBlockPreserver;
use Happytodev\Blogr\Services\Translation\TranslationProviderFactory;
use Happytodev\Blogr\Services\TranslationUsageService;
use Happytodev\Blogr\Services\VersioningService;
use Happytodev\Blogr\Traits\AutoSave;
use Illuminate\Support\Str;

class EditBlogPost extends EditRecord
{
    use AutoSave;

    protected static string $resource = BlogPostResource::class;

    public function mount(int|string $record = ''): void
    {
        parent::mount($record);
        $this->initializeAutoSave();
    }

    public function form(Schema $schema): Schema
    {
        $schema = BlogPostForm::configure($schema);
        $components = $schema->getComponents();
        $components[] = View::make('blogr::components.auto-save-indicator');

        return $schema->components($components);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Normalize corrupted photo fields (JSON-encoded arrays from previous bugs)
        $data = static::normalizePhotoField($data, 'photo');

        $draft = app(VersioningService::class)->getPostDraft($this->record);
        if ($draft && isset($draft->draft_data['translations'])) {
            $data['translations'] = $draft->draft_data['translations'];
        }

        // Normalize translation photo fields too
        if (isset($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $key => $translation) {
                $data['translations'][$key] = static::normalizePhotoField($translation, 'photo');
            }
        }

        return $data;
    }

    public function areFormActionsSticky(): bool
    {
        return true;
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('saveAndPublish')
                ->label('Save & Publish')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    $this->saveAndPublish();
                }),
            Actions\Action::make('saveAsDraft')
                ->label('Save Draft')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('gray')
                ->action(function () {
                    $this->saveAsDraft();
                }),
            Actions\Action::make('unpublish')
                ->label('Unpublish')
                ->icon('heroicon-o-arrow-uturn-down')
                ->color('danger')
                ->visible(fn () => $this->record && $this->record->is_published)
                ->requiresConfirmation()
                ->modalHeading('Unpublish post')
                ->modalDescription('This will unpublish the post immediately. It will no longer be visible on the frontend.')
                ->action(function () {
                    $this->unpublish();
                }),
            $this->getCancelFormAction(),
        ];
    }

    protected function saveAsDraft(): void
    {
        $data = $this->data ?? [];

        $data = $this->mutateFormDataBeforeSave($data);

        /** @var BlogPost $record */
        $record = $this->record;
        $record->update($data);

        app(VersioningService::class)->savePostDraft($this->record, $data);

        $this->lastAutoSaveAt = now()->toIso8601String();
        $this->hasUnsavedChanges = false;
        $this->refreshDraftState();

        $this->record->load('translations');
        $this->fillForm();

        $label = $record->is_published ? 'Draft saved' : 'Draft saved successfully';

        Notification::make()
            ->title($label)
            ->success()
            ->send();
    }

    protected function unpublish(): void
    {
        /** @var BlogPost $record */
        $record = $this->record;
        $record->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        $this->record->load('translations');
        $this->fillForm();

        Notification::make()
            ->title('Post unpublished successfully')
            ->success()
            ->send();
    }

    protected function saveAndPublish(): void
    {
        $data = $this->data ?? [];

        $data = $this->mutateFormDataBeforeSave($data);
        $data['is_published'] = true;

        /** @var BlogPost $record */
        $record = $this->record;
        $record->update($data);

        app(VersioningService::class)->savePostDraft($this->record, $data);
        app(VersioningService::class)->publishPostDraft($this->record, $data['translations'] ?? []);

        $this->record->load('translations');
        $this->refreshDraftState();
        $this->fillForm();

        Notification::make()
            ->title('Post published successfully')
            ->success()
            ->send();
    }

    /**
     * Override parent save() to redirect to draft for published posts.
     * Auto-saves go to drafts. Use "Save & Publish" to publish.
     */
    public function save(?bool $shouldRedirect = null, bool $shouldSendNotification = true): void
    {
        if ($this->record->is_published) {
            $data = $this->data ?? [];

            app(VersioningService::class)->savePostDraft($this->record, $data);

            $this->record->load('translations');

            $this->lastAutoSaveAt = now()->toIso8601String();
            $this->hasUnsavedChanges = false;
            $this->refreshDraftState();

            $this->fillForm();

            Notification::make()
                ->title('Draft saved')
                ->success()
                ->send();
        } else {
            parent::save($shouldRedirect, $shouldSendNotification);
        }
    }

    protected function getHeaderActions(): array
    {
        $provider = app(TranslationProviderFactory::class)->make();
        $actions = [];

        if ($provider) {
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

            $actions[] = Actions\Action::make('translateWithAI')
                ->label('Translate with AI')
                ->icon('heroicon-o-language')
                ->color('success')
                ->form([
                    Select::make('source_locale')
                        ->label('Source language')
                        ->options($sourceOptions)
                        ->default($this->record->default_locale)
                        ->required(),
                    Select::make('target_locale')
                        ->label('Target language')
                        ->options($targetOptions)
                        ->required()
                        ->rule('different:source_locale'),
                ])
                ->action(function (array $data) use ($provider) {
                    $this->translateWithAI($provider, $data['source_locale'], $data['target_locale']);
                });
        }

        $actions[] = Actions\Action::make('history')
            ->label('History')
            ->icon('heroicon-o-clock')
            ->color('gray')
            ->modalContent(function () {
                $draft = app(VersioningService::class)->getPostDraft($this->record);
                $draftEntry = null;
                if ($draft && isset($draft->draft_data['translations'])) {
                    $draftTranslations = $draft->draft_data['translations'];
                    $fieldKeys = ['title', 'slug', 'tldr', 'content', 'seo_title', 'seo_description', 'seo_keywords'];
                    $perLocaleFields = [];
                    $perLocalePrevious = [];
                    $allChanges = [];
                    $firstTitle = null;

                    foreach ($draftTranslations as $key => $transData) {
                        $locale = $transData['locale'] ?? null;
                        if (! $locale) {
                            continue;
                        }
                        if (! $firstTitle && $transData['title']) {
                            $firstTitle = $transData['title'];
                        }
                        $draftFields = array_intersect_key($transData, array_flip($fieldKeys));
                        $perLocaleFields[$locale] = $draftFields;

                        $translation = $this->record->translations()
                            ->where('locale', $locale)
                            ->first();
                        if ($translation) {
                            $lastVersion = app(VersioningService::class)->listVersions($translation)
                                ->sortByDesc('version_number')
                                ->first();
                            if ($lastVersion) {
                                $versionFields = $lastVersion->only($fieldKeys);
                                $perLocalePrevious[$locale] = $versionFields;
                                $normalize = fn ($v) => json_encode(is_array($v) ? array_values($v) : $v);
                                $localeChanges = array_keys(array_diff_assoc(
                                    array_map($normalize, $draftFields),
                                    array_map($normalize, $versionFields)
                                ));
                                $allChanges = array_merge($allChanges, $localeChanges);
                            } else {
                                $allChanges = array_merge($allChanges, $fieldKeys);
                            }
                        }
                    }

                    $draftEntry = [
                        'type' => 'draft',
                        'title' => $firstTitle ?? 'Untitled',
                        'created_at' => $draft->updated_at ?? $draft->created_at,
                        'fields' => $perLocaleFields,
                        'previous_fields' => $perLocalePrevious,
                        'changes' => array_unique($allChanges),
                        'locale_fields' => true,
                    ];
                }

                $versions = collect();
                foreach ($this->record->translations as $translation) {
                    $translationVersions = app(VersioningService::class)->listVersions($translation);
                    $prevVersion = null;
                    foreach ($translationVersions->sortBy('version_number') as $v) {
                        $currentFields = $v->only([
                            'title', 'slug', 'tldr', 'content',
                            'seo_title', 'seo_description', 'seo_keywords',
                        ]);
                        $previousFields = $prevVersion ? $prevVersion->only([
                            'title', 'slug', 'tldr', 'content',
                            'seo_title', 'seo_description', 'seo_keywords',
                        ]) : [];
                        $changes = $prevVersion
                            ? array_keys(array_diff_assoc($currentFields, $previousFields))
                            : ['initial'];
                        $versions->push([
                            'type' => 'version',
                            'title' => $v->title,
                            'version_number' => $v->version_number,
                            'version_id' => $v->id,
                            'translation_id' => $v->blog_post_translation_id,
                            'locale' => $translation->locale,
                            'created_at' => $v->created_at,
                            'fields' => $currentFields,
                            'previous_fields' => $prevVersion ? $previousFields : null,
                            'changes' => $changes,
                        ]);
                        $prevVersion = $v;
                    }
                }

                $history = collect($draftEntry ? [$draftEntry] : [])
                    ->concat($versions)
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

        // Normalize photo fields — FileUpload may return an array
        $data = static::normalizePhotoField($data, 'photo');

        if (isset($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $key => $translation) {
                $data['translations'][$key] = static::normalizePhotoField($translation, 'photo');
            }
        }

        return $data;
    }

    protected static function normalizePhotoField(array $data, string $field): array
    {
        if (! array_key_exists($field, $data)) {
            return $data;
        }

        $value = $data[$field];

        // Decode JSON-encoded array strings from previous bugs (e.g. '[]', '["path.jpg"]')
        if (is_string($value) && str_starts_with($value, '[') && str_ends_with($value, ']')) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }

        // Empty array → remove so existing DB value is preserved
        if (is_array($value) && empty($value)) {
            unset($data[$field]);
        }
        // Array with one element → extract the string path
        elseif (is_array($value) && count($value) === 1 && is_string($value[0])) {
            $data[$field] = $value[0];
        }

        return $data;
    }

    public function restoreVersion(int $versionId): void
    {
        $version = BlogPostVersion::findOrFail($versionId);
        $translation = $version->translation;
        if (! $translation || ! $translation->post) {
            return;
        }

        $post = $translation->post;
        $draft = app(VersioningService::class)->getPostDraft($post);
        $currentData = $draft ? $draft->draft_data : [];

        $versionData = $version->only([
            'title', 'slug', 'content', 'tldr',
            'seo_title', 'seo_description', 'seo_keywords', 'photo',
        ]);

        // Decode JSON strings back to arrays for Filament form components
        foreach ($versionData as $key => &$value) {
            if (is_string($value) && str_starts_with($value, '[') && str_ends_with($value, ']')) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $value = $decoded;
                }
            }
        }
        unset($value);

        $translations = $currentData['translations'] ?? [];
        $locale = $translation->locale;

        $found = false;
        foreach ($translations as $key => $transData) {
            if (is_array($transData) && ($transData['locale'] ?? null) === $locale) {
                $translations[$key] = array_merge($transData, $versionData);
                $found = true;
                break;
            }
        }

        if (! $found) {
            $translations[$locale] = array_merge(
                ['locale' => $locale],
                $versionData
            );
        }

        $currentData['translations'] = $translations;
        app(VersioningService::class)->savePostDraft($post, $currentData);

        $this->lastAutoSaveAt = now()->toIso8601String();
        $this->hasUnsavedChanges = false;
        $this->record->load('translations');
        $this->fillForm();

        Notification::make()
            ->title("Version {$version->version_number} restored to draft")
            ->success()
            ->send();
    }
}
