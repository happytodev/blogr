<?php

namespace Happytodev\Blogr\Services;

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class CmsPageImportExportService
{
    public function exportToJson(CmsPage $page): array
    {
        $page->load('translations');

        $translations = $page->translations->map(function (CmsPageTranslation $translation) {
            return $translation->toArray();
        })->toArray();

        $media = $this->collectMediaFromBlocks($translations);

        return [
            'type' => 'cms_page',
            'format_version' => '1.0',
            'name' => $translations[0]['title'] ?? $page->slug,
            'exported_at' => now()->toIso8601String(),
            'data' => [
                'slug' => $page->slug,
                'template' => $page->template instanceof \BackedEnum ? $page->template->value : $page->template,
                'is_published' => $page->is_published ?? false,
                'is_homepage' => $page->is_homepage ?? false,
                'published_at' => $page->published_at?->toIso8601String(),
                'default_locale' => $page->default_locale ?? config('blogr.locales.default', 'en'),
                'translations' => $translations,
            ],
            'media' => $media,
        ];
    }

    public function exportToFile(CmsPage $page, ?string $path = null): string
    {
        $data = $this->exportToJson($page);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $dir = storage_path('app/blogr-exports');
        File::ensureDirectoryExists($dir);

        if (!$path) {
            $slug = $page->slug;
            $path = $dir . "/{$slug}-export-" . now()->format('Ymd_His') . '.json';
        }

        File::put($path, $json);

        return $path;
    }

    public function importFromFile(string $filePath, string $onConflict = 'new'): CmsPage
    {
        if (!File::exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'zip') {
            return $this->importFromZip($filePath, $onConflict);
        }

        if ($extension !== 'json') {
            throw new \InvalidArgumentException('Unsupported file format. Use .json or .zip files.');
        }

        $json = File::get($filePath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }

        return $this->importFromArray($data, $onConflict);
    }

    public function importFromArray(array $data, string $onConflict = 'new'): CmsPage
    {
        $pageData = $data['data'] ?? $data;
        $media = $data['media'] ?? [];

        if (!isset($pageData['slug'])) {
            throw new \InvalidArgumentException('Missing required field: slug');
        }

        $translations = $pageData['translations'] ?? [];
        unset($pageData['translations']);

        $originalSlug = $pageData['slug'];
        $existing = CmsPage::where('slug', $originalSlug)->first();

        if ($existing) {
            if ($onConflict === 'skip') {
                throw new \RuntimeException("A page with slug '{$originalSlug}' already exists. Import skipped.");
            }

            if ($onConflict === 'new') {
                $pageData['slug'] = $this->generateUniqueSlug($originalSlug);

                foreach ($translations as &$trans) {
                    $trans['slug'] = $this->generateUniqueTranslationSlug($trans['slug'], $trans['locale']);
                }
                unset($trans);
            }
        }

        $pageData['is_homepage'] = $pageData['is_homepage'] ?? false;

        if (!empty($media)) {
            $this->downloadMediaFiles($media);
            $translations = $this->replaceMediaUrlsInBlocks($translations, $media);
        }

        $timestamps = [
            'created_at' => $pageData['created_at'] ?? now(),
            'updated_at' => now(),
        ];

        if (isset($pageData['published_at'])) {
            $pageData['published_at'] = is_string($pageData['published_at'])
                ? \Illuminate\Support\Carbon::parse($pageData['published_at'])
                : $pageData['published_at'];
        }

        if ($onConflict === 'replace' && $existing) {
            $existing->update(array_merge($pageData, $timestamps));
            $page = $existing;
            $page->translations()->delete();
        } else {
            $pageData = array_merge($pageData, $timestamps);
            $page = CmsPage::create($pageData);
        }

        foreach ($translations as $translationData) {
            $translationData['cms_page_id'] = $page->id;
            $translationData['created_at'] = $translationData['created_at'] ?? now();
            $translationData['updated_at'] = now();

            if (isset($translationData['blocks']) && is_array($translationData['blocks'])) {
                $translationData['blocks'] = $translationData['blocks'];
            }

            CmsPageTranslation::create($translationData);
        }

        $page->load('translations');

        Log::info("CmsPageImportExportService: Imported page '{$pageData['slug']}' with " . count($translations) . " translations");

        return $page;
    }

    protected function generateUniqueSlug(string $slug): string
    {
        $base = $slug;
        $counter = 1;

        while (CmsPage::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function generateUniqueTranslationSlug(string $slug, string $locale): string
    {
        $base = $slug;
        $counter = 1;

        while (CmsPageTranslation::where('locale', $locale)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function collectMediaFromBlocks(array $translations): array
    {
        $media = [];

        foreach ($translations as $translation) {
            $blocks = $translation['blocks'] ?? [];

            foreach ($blocks as $blockIndex => $block) {
                $data = $block['data'] ?? [];

                $found = $this->extractImagePaths($data);

                foreach ($found as $path) {
                    if ($this->isLocalStoragePath($path)) {
                        $url = $this->getPublicUrl($path);

                        $media[] = [
                            'path' => $path,
                            'url' => $url,
                            'block_index' => $blockIndex,
                            'field' => $this->findFieldForKey($data, $path),
                        ];
                    }
                }
            }
        }

        return $this->deduplicateMedia($media);
    }

    protected function extractImagePaths(array $data, string $prefix = ''): array
    {
        $paths = [];

        foreach ($data as $key => $value) {
            $fieldPath = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_string($value) && $this->looksLikeImagePath($value)) {
                $paths[] = $value;
            } elseif (is_array($value)) {
                $paths = array_merge($paths, $this->extractImagePaths($value, $fieldPath));
            }
        }

        return $paths;
    }

    protected function looksLikeImagePath(string $value): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif'];

        $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));

        if (in_array($extension, $imageExtensions)) {
            return true;
        }

        if (Str::startsWith($value, 'cms-') || Str::contains($value, '/cms-')) {
            return true;
        }

        return false;
    }

    protected function isLocalStoragePath(string $path): bool
    {
        return !Str::startsWith($path, 'http://') && !Str::startsWith($path, 'https://') && !Str::startsWith($path, 'data:');
    }

    protected function getPublicUrl(string $path): string
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return url('storage/' . ltrim($path, '/'));
    }

    protected function findFieldForKey(array $data, string $searchValue): string
    {
        foreach ($data as $key => $value) {
            if (is_string($value) && $value === $searchValue) {
                return $key;
            }
            if (is_array($value)) {
                $result = $this->findFieldForKey($value, $searchValue);
                if ($result) {
                    return "{$key}.{$result}";
                }
            }
        }

        return '';
    }

    protected function deduplicateMedia(array $media): array
    {
        $seen = [];

        return array_values(array_filter($media, function ($item) use (&$seen) {
            $key = $item['path'];
            if (isset($seen[$key])) {
                return false;
            }
            $seen[$key] = true;
            return true;
        }));
    }

    protected function downloadMediaFiles(array $media): void
    {
        foreach ($media as $item) {
            if (empty($item['url']) || empty($item['path'])) {
                continue;
            }

            if ($this->isLocalStoragePath($item['path'])) {
                if (Storage::disk('public')->exists($item['path'])) {
                    continue;
                }
            }

            try {
                $response = Http::timeout(30)
                    ->get($item['url']);

                if ($response->successful()) {
                    $directory = dirname($item['path']);
                    if ($directory !== '.') {
                        File::ensureDirectoryExists(Storage::disk('public')->path($directory));
                    }

                    Storage::disk('public')->put($item['path'], $response->body());

                    Log::info("CmsPageImportExportService: Downloaded media '{$item['path']}' from {$item['url']}");
                } else {
                    Log::warning("CmsPageImportExportService: Failed to download '{$item['url']}' - HTTP {$response->status()}");
                }
            } catch (\Exception $e) {
                Log::warning("CmsPageImportExportService: Failed to download '{$item['url']}': " . $e->getMessage());
            }
        }
    }

    protected function replaceMediaUrlsInBlocks(array $translations, array $media): array
    {
        $urlToPath = [];

        foreach ($media as $item) {
            if (!empty($item['url']) && !empty($item['path'])) {
                $urlToPath[$item['url']] = $item['path'];
            }
        }

        if (empty($urlToPath)) {
            return $translations;
        }

        foreach ($translations as &$translation) {
            if (isset($translation['blocks']) && is_array($translation['blocks'])) {
                foreach ($translation['blocks'] as &$block) {
                    if (isset($block['data']) && is_array($block['data'])) {
                        $block['data'] = $this->replaceUrlsInData($block['data'], $urlToPath);
                    }
                }
            }
        }

        return $translations;
    }

    protected function replaceUrlsInData(array $data, array $urlToPath): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                foreach ($urlToPath as $url => $path) {
                    if ($value === $url) {
                        $data[$key] = $path;
                        break;
                    }
                }
            } elseif (is_array($value)) {
                $data[$key] = $this->replaceUrlsInData($value, $urlToPath);
            }
        }

        return $data;
    }

    protected function importFromZip(string $zipPath, string $onConflict): CmsPage
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Cannot open ZIP file');
        }

        $jsonContent = $zip->getFromName('data.json');
        if (!$jsonContent) {
            $zip->close();
            throw new \RuntimeException('data.json not found in ZIP file');
        }

        $tempDir = storage_path('app/temp/cms-import-' . now()->timestamp);
        File::ensureDirectoryExists($tempDir);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (Str::startsWith($filename, 'media/')) {
                $content = $zip->getFromIndex($i);
                $localPath = $tempDir . '/' . basename($filename);
                File::put($localPath, $content);
            }
        }

        $zip->close();

        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            File::deleteDirectory($tempDir);
            throw new \InvalidArgumentException('Invalid JSON in ZIP: ' . json_last_error_msg());
        }

        if (isset($data['media'])) {
            foreach ($data['media'] as &$mediaItem) {
                $localFile = $tempDir . '/' . basename($mediaItem['path']);
                if (File::exists($localFile)) {
                    $mediaItem['_local_file'] = $localFile;
                }
            }
        }

        try {
            $page = $this->importFromArray($data, $onConflict);
            File::deleteDirectory($tempDir);
            return $page;
        } catch (\Exception $e) {
            File::deleteDirectory($tempDir);
            throw $e;
        }
    }
}
