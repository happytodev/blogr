<?php

namespace Happytodev\Blogr\Traits;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Services\VersioningService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait AutoSave
{
    public ?string $lastAutoSaveAt = null;

    public ?string $lastManualSaveAt = null;

    public bool $hasUnsavedChanges = false;

    public ?string $savedSnapshot = null;

    public bool $hasActiveDraft = false;

    public function initializeAutoSave(): void
    {
        $this->refreshDraftState();
        $record = $this->getRecordForVersioning();
        $draft = ($record instanceof BlogPost)
            ? app(VersioningService::class)->getPostDraft($record)
            : null;
        $timestamp = $draft
            ? $draft->updated_at->toIso8601String()
            : (($record instanceof Model)
                ? $record->updated_at?->toIso8601String()
                : now()->toIso8601String());
        $this->lastAutoSaveAt = $timestamp;
        $this->lastManualSaveAt = $this->hasActiveDraft ? $timestamp : null;
        try {
            $snapshotData = static::sanitizeForSnapshot($this->data ?? []);
            $this->savedSnapshot = md5(serialize($snapshotData));
        } catch (\Throwable $e) {
            Log::warning('[Blogr AutoSave] initializeAutoSave failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->savedSnapshot = '';
        }
    }

    public function refreshDraftState(): void
    {
        $record = $this->getRecordForVersioning();
        if ($record && $record instanceof BlogPost) {
            $this->hasActiveDraft = app(VersioningService::class)->getPostDraft($record) !== null;
        }
    }

    public function updated(string $name, mixed $value): void
    {
        $this->hasUnsavedChanges = true;
    }

    public function autoSave(): void
    {
        try {
            $currentState = $this->data ?? [];
            $currentHash = md5(serialize(static::sanitizeForSnapshot($currentState)));
            $hasChanges = $currentHash !== $this->savedSnapshot;

            if (! $hasChanges) {
                $this->lastAutoSaveAt = now()->toIso8601String();
                $this->hasUnsavedChanges = false;
                $this->dispatch('auto-saved');

                return;
            }

            $record = $this->getRecordForVersioning();

            if ($record && $record->exists) {
                if ($record instanceof BlogPost) {
                    app(VersioningService::class)->savePostDraft($record, $currentState);
                } elseif ($record instanceof BlogPostTranslation) {
                    if ($record->post?->is_published) {
                        app(VersioningService::class)->saveDraft($record, $currentState);
                    } else {
                        $record->update($currentState);
                    }
                } elseif ($record instanceof CmsPageTranslation) {
                    if ($record->page?->is_published) {
                        app(VersioningService::class)->saveDraft($record, $currentState);
                    } else {
                        $record->update(VersioningService::persistUploadedFiles($currentState));
                    }
                }
            } elseif (auth()->check()) {
                $placeholder = BlogPost::create([
                    'user_id' => auth()->id(),
                    'category_id' => $currentState['category_id'] ?? Category::first()?->id ?? 1,
                ]);
                if ($placeholder->exists) {
                    // Create a translation from the first translation data so the post
                    // appears with a title in the blog list, not as "Untitled"
                    $translations = $currentState['translations'] ?? [];
                    $firstKey = array_key_first($translations);
                    if ($firstKey !== null && isset($translations[$firstKey]['title'])) {
                        $title = $translations[$firstKey]['title'];
                        $slug = $translations[$firstKey]['slug'] ?? Str::slug($title);
                        $locale = $translations[$firstKey]['locale'] ?? $currentState['default_locale'] ?? app()->getLocale();
                        $placeholder->translations()->create([
                            'locale' => $locale,
                            'title' => $title,
                            'slug' => $slug,
                        ]);
                    }
                    $this->record = $placeholder;
                    app(VersioningService::class)->savePostDraft($placeholder, $currentState);
                }
            }
            $this->lastAutoSaveAt = now()->toIso8601String();
            $this->refreshDraftState();
            $this->savedSnapshot = $currentHash;
            $this->hasUnsavedChanges = false;

            $this->dispatch('auto-saved');
        } catch (\Throwable $e) {
            Log::error('[Blogr AutoSave] autoSave failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'record_exists' => isset($record) ? $record->exists : false,
                'record_type' => isset($record) ? get_class($record) : 'none',
            ]);
        }
    }

    public function manualSave(): void
    {
        $this->autoSave();
        $this->lastManualSaveAt = $this->lastAutoSaveAt;
        $this->dispatch('manual-saved');
    }

    public function getAutoSaveInterval(): int
    {
        return max(0, config('blogr.auto_save_interval', 30));
    }

    protected static function sanitizeForSnapshot($value)
    {
        if ($value instanceof TemporaryUploadedFile) {
            return '__FILE__';
        }
        if (is_array($value)) {
            return array_map([static::class, 'sanitizeForSnapshot'], $value);
        }

        return $value;
    }

    protected function getRecordForVersioning()
    {
        if (property_exists($this, 'record') && $this->record) {
            return $this->record;
        }
    }
}
