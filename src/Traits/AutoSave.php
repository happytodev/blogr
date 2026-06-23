<?php

namespace Happytodev\Blogr\Traits;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Services\VersioningService;
use Illuminate\Support\Facades\Log;

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
            : (($record instanceof \Illuminate\Database\Eloquent\Model)
                ? $record->updated_at?->toIso8601String()
                : now()->toIso8601String());
        $this->lastAutoSaveAt = $timestamp;
        $this->lastManualSaveAt = $this->hasActiveDraft ? $timestamp : null;
        try {
            $this->savedSnapshot = md5(serialize($this->data ?? []));
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
            $currentHash = md5(serialize($currentState));
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
                    if ($record->is_published) {
                        $draft = app(VersioningService::class)->savePostDraft($record, $currentState);
                    } else {
                        app(VersioningService::class)->savePostDraft($record, $currentState);
                        app(VersioningService::class)->publishPostDraft($record, $currentState['translations'] ?? []);
                    }
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
                        $record->update($currentState);
                    }
                }
            } elseif (auth()->check()) {
                $placeholder = BlogPost::create([
                    'user_id' => auth()->id(),
                    'category_id' => $currentState['category_id'] ?? \Happytodev\Blogr\Models\Category::first()?->id ?? 1,
                ]);
                if ($placeholder->exists) {
                    // Create a translation from the first translation data so the post
                    // appears with a title in the blog list, not as "Untitled"
                    $translations = $currentState['translations'] ?? [];
                    $firstKey = array_key_first($translations);
                    if ($firstKey !== null && isset($translations[$firstKey]['title'])) {
                        $title = $translations[$firstKey]['title'];
                        $slug = $translations[$firstKey]['slug'] ?? \Illuminate\Support\Str::slug($title);
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

    protected function getRecordForVersioning()
    {
        if (property_exists($this, 'record') && $this->record) {
            return $this->record;
        }

        return null;
    }
}
