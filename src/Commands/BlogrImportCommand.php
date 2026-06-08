<?php

namespace Happytodev\Blogr\Commands;

use Happytodev\Blogr\Services\BlogrImportService;
use Happytodev\Blogr\Services\CmsPageImportExportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BlogrImportCommand extends Command
{
    public $signature = 'blogr:import 
                        {file : Path to the JSON or ZIP export file}
                        {--skip-existing : Skip existing records instead of failing}
                        {--on-conflict=new : Strategy when a page already exists: new, skip, or replace}';

    public $description = 'Import Blogr data from a JSON or ZIP export file';

    public function handle(): int
    {
        $filePath = $this->argument('file');
        $skipExisting = $this->option('skip-existing');
        $onConflict = $this->option('on-conflict');

        if (! File::exists($filePath)) {
            $this->error("❌ File not found: {$filePath}");

            return self::FAILURE;
        }

        $this->info('🚀 Starting Blogr data import...');
        $this->line("📁 Import file: {$filePath}");

        try {
            // Detect if the file is a CMS page export
            $isCmsPage = $this->detectCmsPageFormat($filePath);

            if ($isCmsPage) {
                return $this->handleCmsPageImport($filePath, $onConflict);
            }

            return $this->handleBulkImport($filePath, $skipExisting);

        } catch (\Exception $e) {
            $this->error('❌ Import failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Detect if the JSON file is a CMS page export format.
     */
    protected function detectCmsPageFormat(string $filePath): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension !== 'json') {
            return false;
        }

        $json = File::get($filePath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        // CMS page exports have 'type' => 'cms_page' and 'format_version' => '1.0'
        return isset($data['type']) && $data['type'] === 'cms_page';
    }

    /**
     * Handle CMS page import using CmsPageImportExportService.
     */
    protected function handleCmsPageImport(string $filePath, string $onConflict): int
    {
        $this->info('📄 Detected CMS page export format');

        try {
            $service = new CmsPageImportExportService;
            $page = $service->importFromFile($filePath, $onConflict);

            $this->info('✅ CMS page imported successfully');
            $this->line("   Slug: {$page->slug}");
            $this->line("   Translations: {$page->translations()->count()}");
            $this->line('   Template: '.($page->template instanceof \BackedEnum ? $page->template->label() : $page->template));

            return self::SUCCESS;

        } catch (\RuntimeException $e) {
            $this->error('❌ Import failed: '.$e->getMessage());

            return self::FAILURE;
        } catch (\InvalidArgumentException $e) {
            $this->error('❌ Invalid file: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Handle bulk import using BlogrImportService.
     */
    protected function handleBulkImport(string $filePath, bool $skipExisting): int
    {
        $this->info('📦 Starting bulk data import...');

        $importService = new BlogrImportService;
        $result = $importService->importFromFile($filePath, [
            'skip_existing' => $skipExisting,
        ]);

        if (! $result['success']) {
            $this->error('❌ Import failed:');
            foreach ($result['errors'] as $error) {
                $this->line("  - {$error}");
            }

            return self::FAILURE;
        }

        // Show results
        $this->info('✅ Blogr data imported successfully');
        $this->line("📅 Exported from: {$result['exported_at']}");
        $this->line("🏷️ Version: {$result['version']}");
        $this->newLine();

        foreach ($result['results'] as $type => $stats) {
            $imported = $stats['imported'] ?? 0;
            $updated = $stats['updated'] ?? 0;
            $skipped = $stats['skipped'] ?? 0;
            $this->line("{$type}: {$imported} imported, {$updated} updated, {$skipped} skipped");
        }

        return self::SUCCESS;
    }
}
