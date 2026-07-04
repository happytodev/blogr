<?php

namespace Happytodev\Blogr\Services;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/** @phpstan-type BackupMeta array{format_version: string, version: string, exported_at: string} */
class BlogrRecoveryService
{
    /** @return array<int, array{path: string, name: string, size: int, modified_at: string}> */
    public function listBackups(): array
    {
        $dir = storage_path('app/blogr-exports');
        if (! File::exists($dir)) {
            return [];
        }
        $result = [];
        foreach (File::files($dir) as $file) {
            if (! in_array($file->getExtension(), ['json', 'zip'])) {
                continue;
            }
            $result[] = [
                'path' => $file->getPathname(),
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
            ];
        }
        usort($result, fn ($a, $b) => strcmp($b['modified_at'], $a['modified_at']));

        return $result;
    }

    /**
     * @return array{valid: bool, errors: array<int, string>, warnings: array<int, string>, meta?: BackupMeta}
     */
    public function validateBackup(string $path): array
    {
        $errors = [];
        $warnings = [];
        if (! File::exists($path)) {
            return ['valid' => false, 'errors' => ['File does not exist'], 'warnings' => []];
        }
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (! in_array($extension, ['json', 'zip'])) {
            return ['valid' => false, 'errors' => ['Unsupported file format. Use .json or .zip'], 'warnings' => []];
        }
        if ($extension === 'zip') {
            $zip = new \ZipArchive;
            if ($zip->open($path) !== true) {
                return ['valid' => false, 'errors' => ['Cannot open ZIP file'], 'warnings' => []];
            }
            $jsonContent = $zip->getFromName('data.json');
            $zip->close();
            if (! $jsonContent) {
                return ['valid' => false, 'errors' => ['data.json not found in ZIP file'], 'warnings' => []];
            }
            $data = json_decode($jsonContent, true);
        } else {
            $jsonContent = File::get($path);
            $data = json_decode($jsonContent, true);
        }
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['valid' => false, 'errors' => ['Invalid JSON: '.json_last_error_msg()], 'warnings' => []];
        }
        if (! is_array($data)) {
            return ['valid' => false, 'errors' => ['Invalid JSON structure: expected an object'], 'warnings' => []];
        }
        if (! isset($data['posts']) || ! isset($data['series']) || ! isset($data['categories']) || ! isset($data['tags'])) {
            $errors[] = 'Missing required sections (posts, series, categories, tags)';
        }
        if (isset($data['extensions']) && is_array($data['extensions'])) {
            $registry = app(ExtensionRegistry::class);
            foreach ($data['extensions'] as $extKey => $extData) {
                if (is_string($extKey) && is_array($extData)) {
                    $extVersion = is_string($extData['version'] ?? null) ? $extData['version'] : 'unknown';
                    if (! $registry->has($extKey)) {
                        $warnings[] = "Extension '{$extKey}' (v{$extVersion}) from backup is not installed in the current system";
                    }
                }
            }
        }
        $meta = [
            'format_version' => is_string($data['format_version'] ?? null) ? $data['format_version'] : '1.0',
            'version' => is_string($data['version'] ?? null) ? $data['version'] : 'unknown',
            'exported_at' => is_string($data['exported_at'] ?? null) ? $data['exported_at'] : 'unknown',
        ];

        return ['valid' => empty($errors), 'errors' => $errors, 'warnings' => $warnings, 'meta' => $meta];
    }

    public function detectCorruption(): bool
    {
        try {
            $hasData = Category::count() > 0 || Tag::count() > 0 || BlogPost::count() > 0;

            return ! $hasData;
        } catch (\Exception) {
            return true;
        }
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{success: bool, errors?: array<int, string>, results?: array<string, array{imported: int, skipped: int, updated?: int}>, version?: string, exported_at?: string}
     */
    public function restore(string $path, array $options = []): array
    {
        $validation = $this->validateBackup($path);
        if (! $validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }
        try {
            $importOptions = array_merge($options, ['overwrite' => true]);
            $importService = app(BlogrImportService::class);
            /** @var array{success: bool, errors?: array<int, string>, results?: array<string, array{imported: int, skipped: int}>, version?: string, exported_at?: string} $result */
            $result = $importService->importFromFile($path, $importOptions);

            return $result;
        } catch (\Exception $e) {
            Log::error('BlogrRecoveryService: Restore failed: '.$e->getMessage());

            return ['success' => false, 'errors' => [$e->getMessage()]];
        }
    }
}
