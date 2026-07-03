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
            ['draft_data' => static::persistUploadedFiles($formData, 'blog-photos')],
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

        // Only publish if data has actually changed (after normalizing UUID noise)
        $versionModel = $this->getVersionModel($translation);
        $lastVersion = $versionModel::where($this->getForeignKey($translation), $translation->id)
            ->orderBy('version_number', 'desc')
            ->first();

        if (! $lastVersion || ! static::dataMatches($data, $lastVersion, $versionModel)) {
            $translation->update($data);
            $this->createVersion($translation, $data, $extra);
        } else {
            $translation->update($data);
        }

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
                // photo is a single-file Upload — extract string path from arrays
                if (isset($versionData['photo'])) {
                    if (is_array($versionData['photo'])) {
                        $versionData['photo'] = $versionData['photo'][0] ?? null;
                    }
                    // Non-string photo (TemporaryUploadedFile, etc.) → remove to preserve existing value
                    if (! is_string($versionData['photo'])) {
                        unset($versionData['photo']);
                    }
                }
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

    public static function persistUploadedFiles(array $data, string $directory = 'cms-blocks/uploads'): array
    {
        return static::walkAndPersist($data, $directory);
    }

    protected static function walkAndPersist($value, string $directory = 'cms-blocks/uploads')
    {
        // Convert stdClass objects to arrays (Livewire serializes some data as objects)
        if ($value instanceof \stdClass) {
            $value = (array) $value;
        }

        if ($value instanceof TemporaryUploadedFile) {
            try {
                return $value->store($directory, ['disk' => 'public']);
            } catch (\Throwable $e) {
                return '';
            }
        }

        if (is_array($value)) {
            $hasSerializedFile = false;
            foreach ($value as $k => $v) {
                if (is_string($k) && str_contains($k, 'TemporaryUploadedFile') && is_string($v)) {
                    try {
                        $path = Storage::disk('public')
                            ->putFile($directory, new File($v));

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
                                    ->putFile($directory, new File($innerV));

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
                $result[$key] = static::walkAndPersist($item, $directory);
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

    protected function createVersion(Model $translation, array $data, array $extra = []): ?Model
    {
        $versionModel = $this->getVersionModel($translation);
        $fk = $this->getForeignKey($translation);

        $normalize = function (array $d) use ($versionModel, $fk): string {
            $fillable = (new $versionModel)->getFillable();
            $relevant = array_intersect_key($d, array_flip($fillable));
            unset($relevant[$fk], $relevant['version_number']);

            try {
                $result = static::stripUuidKeys($relevant);
            } catch (\Throwable $e) {
                return json_encode($relevant);
            }

            return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        };

        $newNormalized = $normalize(array_merge($data, $extra));

        $lastVersion = $versionModel::where($fk, $translation->id)
            ->orderBy('version_number', 'desc')
            ->first();

        if ($lastVersion && $normalize($lastVersion->toArray()) === $newNormalized) {
            return $lastVersion;
        }

        $translationId = $translation->getKey();
        $maxVersion = $versionModel::where($fk, $translationId)->max('version_number') ?? 0;

        return $versionModel::create([
            $fk => $translationId,
            'version_number' => $maxVersion + 1,
            ...array_merge($data, $extra),
        ]);
    }

    public static function dataMatches(array $newData, Model $lastVersion, string $versionModel): bool
    {
        $fillable = (new $versionModel)->getFillable();
        $fk = str_contains($versionModel, 'BlogPost') ? 'blog_post_translation_id' : 'cms_page_translation_id';

        // Use only keys present in BOTH the fillable array AND the new data
        $keys = array_intersect(array_keys($newData), $fillable);
        $keys = array_values(array_diff($keys, [$fk, 'version_number']));

        $normalize = function (array $data) use ($keys): string {
            $relevant = [];
            foreach ($keys as $k) {
                if (array_key_exists($k, $data)) {
                    $relevant[$k] = $data[$k];
                }
            }

            return json_encode(static::stripUuidKeys($relevant), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        };

        return $normalize($newData) === $normalize($lastVersion->toArray());
    }

    public static function stripUuidKeys($data)
    {
        if (! is_array($data)) {
            return $data;
        }

        $keys = array_keys($data);
        $isUuidKey = fn ($k) => is_string($k) && preg_match('/^[a-f0-9-]{36}$/', $k);

        if (count($data) === 1 && $isUuidKey($keys[0]) && is_string($data[$keys[0]])) {
            return $data[$keys[0]];
        }

        if (! array_is_list($data)) {
            $allUuid = array_reduce($keys, fn ($c, $k) => $c && $isUuidKey($k), true);
            if ($allUuid) {
                return array_values(array_map([static::class, 'stripUuidKeys'], $data));
            }
        }

        return array_map([static::class, 'stripUuidKeys'], $data);
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
