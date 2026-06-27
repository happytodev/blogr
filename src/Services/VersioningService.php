<?php

namespace Happytodev\Blogr\Services;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostDraft;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\BlogPostVersion;
use Happytodev\Blogr\Models\CmsPageDraft;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Models\CmsPageVersion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class VersioningService
{
    // ── Drafts ──

    public function saveDraft(Model $translation, array $formData, array $extra = []): ?Model
    {
        if (! $this->isPublished($translation)) {
            return null;
        }

        $formData = static::persistUploadedFiles($formData);

        return $this->upsertDraft($translation, $formData, $extra);
    }

    public function savePostDraft(BlogPost $post, array $formData): BlogPostDraft
    {
        return BlogPostDraft::updateOrCreate(
            ['blog_post_id' => $post->id],
            ['draft_data' => $formData],
        );
    }

    public function getDraft(Model $translation): ?Model
    {
        $modelClass = $this->getDraftModel($translation);

        return $modelClass::where($this->getForeignKey($translation), $translation->id)->first();
    }

    public function getPostDraft(BlogPost $post): ?BlogPostDraft
    {
        return BlogPostDraft::where('blog_post_id', $post->id)->first();
    }

    public function draftExists(Model $translation): bool
    {
        return $this->getDraft($translation) !== null;
    }

    // ── Publish ──

    public function publish(Model $translation, array $extra = []): Model
    {
        $draft = $this->getDraft($translation);

        if (! $draft) {
            return $translation;
        }

        $data = $draft->draft_data;

        $data = static::persistUploadedFiles($data);

        $translation->update($data);

        $this->createVersion($translation, $data, $extra);
        $draft->delete();

        return $translation->fresh();
    }

    public function publishPostDraft(BlogPost $post, array $translationsData): void
    {
        $draft = $this->getPostDraft($post);
        if (! $draft) {
            return;
        }

        $data = $draft->draft_data;
        $translations = $data['translations'] ?? [];

        foreach ($translations as $localeData) {
            $locale = $localeData['locale'] ?? null;
            if (! $locale) {
                continue;
            }

            $translation = BlogPostTranslation::where('blog_post_id', $post->id)
                ->where('locale', $locale)
                ->first();

            if ($translation) {
                $versionData = array_intersect_key($localeData, array_flip([
                    'title', 'slug', 'content', 'tldr',
                    'seo_title', 'seo_description', 'seo_keywords', 'photo',
                ]));

                // Convert arrays to strings for storage
                array_walk($versionData, function (&$value) {
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                });

                $translation->update($versionData);

                BlogPostVersion::create([
                    'blog_post_translation_id' => $translation->id,
                    'version_number' => (BlogPostVersion::where('blog_post_translation_id', $translation->id)->max('version_number') ?? 0) + 1,
                    ...$versionData,
                    'categories' => is_array($localeData['categories'] ?? null) ? json_encode($localeData['categories']) : ($localeData['categories'] ?? null),
                    'tags' => is_array($localeData['tags'] ?? null) ? json_encode($localeData['tags']) : ($localeData['tags'] ?? null),
                ]);
            }
        }

        $draft->delete();
    }

    // ── Versions ──

    public function listVersions(Model $translation): Collection
    {
        $modelClass = $this->getVersionModel($translation);

        return $modelClass::where($this->getForeignKey($translation), $translation->id)
            ->orderBy('version_number', 'desc')
            ->get();
    }

    public function rollback(Model $translation, int $versionId): ?Model
    {
        $versionModel = $this->getVersionModel($translation);
        $version = $versionModel::findOrFail($versionId);

        $data = $version->only([
            'title', 'slug', 'content', 'tldr',
            'seo_title', 'seo_description', 'seo_keywords', 'photo',
        ]);

        $data = array_filter($data, fn ($v) => $v !== null);

        $extra = [];
        if ($version->categories) {
            $extra['categories'] = $version->categories;
        }
        if ($version->tags) {
            $extra['tags'] = $version->tags;
        }

        return $this->upsertDraft($translation, $data, $extra);
    }

    // ── File persistence ──

    public static function persistUploadedFiles(array $data): array
    {
        Log::debug('[BLOGR] persistUploadedFiles called', [
            'keys' => array_keys($data),
        ]);
        if (isset($data['blocks'])) {
            Log::debug('[BLOGR] persistUploadedFiles blocks structure', [
                'blocks_keys' => array_keys($data['blocks']),
                'sample' => json_encode($data['blocks'], JSON_PRETTY_PRINT),
            ]);
        }

        return static::walkAndPersist($data);
    }

    protected static function walkAndPersist($value)
    {
        if ($value instanceof TemporaryUploadedFile) {
            try {
                return $value->store('cms-blocks/uploads', ['disk' => 'public']);
            } catch (\Throwable $e) {
                return '';
            }
        }

        if (is_array($value)) {
            Log::debug('[BLOGR] walkAndPersist checking array', [
                'keys' => array_keys($value),
                'has_tmp_key' => collect($value)->contains(fn ($v) => is_string($v) && str_contains($v, '/tmp/')) ? 'yes' : 'no',
            ]);

            // Check if this array represents a Livewire serialized TemporaryUploadedFile
            // The Image field inside a Repeater stores files as:
            //   ['uuid' => ['Livewire\Features\SupportFileUploads\TemporaryUploadedFile' => '/tmp/...']]
            // After dehydrate, the format can be:
            //   ['TemporaryUploadedFile' => '/tmp/...']
            // or simply an array containing a value that looks like a temp file path
            $hasSerializedFile = false;
            foreach ($value as $k => $v) {
                if (is_string($k) && str_contains($k, 'TemporaryUploadedFile') && is_string($v)) {
                    try {
                        $path = Storage::disk('public')
                            ->putFile('cms-blocks/uploads', new File($v));

                        return $path;
                    } catch (\Throwable $e) {
                        return '';
                    }
                }
                if (is_array($v)) {
                    foreach ($v as $innerK => $innerV) {
                        if (is_string($innerK) && str_contains($innerK, 'TemporaryUploadedFile') && is_string($innerV)) {
                            try {
                                $path = Storage::disk('public')
                                    ->putFile('cms-blocks/uploads', new File($innerV));

                                return $path;
                            } catch (\Throwable $e) {
                                return '';
                            }
                        }
                    }
                }
            }

            $result = [];
            foreach ($value as $key => $item) {
                $result[$key] = static::walkAndPersist($item);
            }

            return $result;
        }

        return $value;
    }

    // ── Internal ──

    protected function upsertDraft(Model $translation, array $data, array $extra = []): Model
    {
        $data = static::persistUploadedFiles($data);

        $draftModel = $this->getDraftModel($translation);
        $fk = $this->getForeignKey($translation);

        return $draftModel::updateOrCreate(
            [$fk => $translation->id],
            ['draft_data' => array_merge($data, $extra)],
        );
    }

    protected function createVersion(Model $translation, array $data, array $extra = []): Model
    {
        $versionModel = $this->getVersionModel($translation);
        $fk = $this->getForeignKey($translation);

        $maxVersion = $versionModel::where($fk, $translation->id)->max('version_number') ?? 0;

        return $versionModel::create([
            $fk => $translation->id,
            'version_number' => $maxVersion + 1,
            ...array_merge($data, $extra),
        ]);
    }

    protected function isPublished(Model $translation): bool
    {
        if ($translation instanceof BlogPostTranslation) {
            return $translation->post?->is_published ?? false;
        }

        if ($translation instanceof CmsPageTranslation) {
            return $translation->page?->is_published ?? false;
        }

        return false;
    }

    protected function getDraftModel(Model $translation): string
    {
        return $translation instanceof BlogPostTranslation
            ? BlogPostDraft::class
            : CmsPageDraft::class;
    }

    protected function getVersionModel(Model $translation): string
    {
        return $translation instanceof BlogPostTranslation
            ? BlogPostVersion::class
            : CmsPageVersion::class;
    }

    protected function getForeignKey(Model $translation): string
    {
        return $translation instanceof BlogPostTranslation
            ? 'blog_post_translation_id'
            : 'cms_page_translation_id';
    }
}
