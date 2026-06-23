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
use Illuminate\Support\Collection;

class VersioningService
{
    // ── Drafts ──

    public function saveDraft(Model $translation, array $formData, array $extra = []): ?Model
    {
        if (! $this->isPublished($translation)) {
            return null;
        }

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

    // ── Internal ──

    protected function upsertDraft(Model $translation, array $data, array $extra = []): Model
    {
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
