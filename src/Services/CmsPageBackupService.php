<?php

namespace Happytodev\Blogr\Services;

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CmsPageBackupService
{
    /**
     * Create a backup of all CMS pages
     */
    public function backup(): string
    {
        try {
            $backupData = [
                'timestamp' => now()->toIso8601String(),
                'pages' => CmsPage::with('translations')->get()->toArray(),
            ];

            $filename = 'cms-pages-backup-' . now()->format('Y-m-d-H-i-s') . '.json';
            $path = storage_path('app/blogr-backups/' . $filename);

            File::ensureDirectoryExists(storage_path('app/blogr-backups'));
            File::put($path, json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Log::info("BlogrBackupService: Created CMS pages backup at {$path}");

            return $path;
        } catch (\Exception $e) {
            Log::error("BlogrBackupService: Backup failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get list of all backups
     */
    public function listBackups(): array
    {
        try {
            $backupDir = storage_path('app/blogr-backups');

            if (!File::exists($backupDir)) {
                return [];
            }

            $files = File::files($backupDir);

            return collect($files)
                ->filter(fn ($file) => str_ends_with($file->getFilename(), '.json'))
                ->map(fn ($file) => [
                    'filename' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'created_at' => \DateTime::createFromFormat('U', $file->getMTime()),
                ])
                ->sortByDesc('created_at')
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error("BlogrBackupService: Failed to list backups: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Restore CMS pages from a backup file
     */
    public function restore(string $backupPath, bool $deleteExisting = false): array
    {
        try {
            if (!File::exists($backupPath)) {
                throw new \Exception("Backup file not found: {$backupPath}");
            }

            $backupData = json_decode(File::get($backupPath), true);

            if (!isset($backupData['pages'])) {
                throw new \Exception("Invalid backup format");
            }

            if ($deleteExisting) {
                CmsPage::truncate();
                CmsPageTranslation::truncate();
            }

            $stats = [
                'pages_restored' => 0,
                'translations_restored' => 0,
                'errors' => [],
            ];

            foreach ($backupData['pages'] as $pageData) {
                try {
                    $translations = $pageData['translations'] ?? [];
                    unset($pageData['translations']);

                    $page = CmsPage::updateOrCreate(
                        ['id' => $pageData['id']],
                        $pageData
                    );

                    $stats['pages_restored']++;

                    foreach ($translations as $translationData) {
                        CmsPageTranslation::updateOrCreate(
                            ['id' => $translationData['id']],
                            $translationData
                        );
                        $stats['translations_restored']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors'][] = "Failed to restore page: " . $e->getMessage();
                }
            }

            Log::info(
                "BlogrBackupService: Restored CMS pages. Pages: {$stats['pages_restored']}, " .
                "Translations: {$stats['translations_restored']}"
            );

            return $stats;
        } catch (\Exception $e) {
            Log::error("BlogrBackupService: Restore failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a backup file
     */
    public function deleteBackup(string $backupPath): bool
    {
        try {
            if (File::exists($backupPath)) {
                File::delete($backupPath);
                Log::info("BlogrBackupService: Deleted backup {$backupPath}");
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error("BlogrBackupService: Failed to delete backup: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean old backups (keep only last N backups)
     */
    public function cleanOldBackups(int $keepCount = 10): int
    {
        try {
            $backups = $this->listBackups();

            if (count($backups) <= $keepCount) {
                return 0;
            }

            $toDelete = array_slice($backups, $keepCount);
            $deletedCount = 0;

            foreach ($toDelete as $backup) {
                if ($this->deleteBackup($backup['path'])) {
                    $deletedCount++;
                }
            }

            Log::info("BlogrBackupService: Cleaned {$deletedCount} old backups");

            return $deletedCount;
        } catch (\Exception $e) {
            Log::error("BlogrBackupService: Cleanup failed: " . $e->getMessage());
            return 0;
        }
    }
}
