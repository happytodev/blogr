<?php

namespace Happytodev\Blogr\Commands;

use Happytodev\Blogr\Services\BlogrImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BlogrImportCommand extends Command
{
    public $signature = 'blogr:import 
                        {file : Path to the JSON or ZIP export file}
                        {--skip-existing : Skip existing records instead of failing}';

    public $description = 'Import Blogr data from a JSON or ZIP export file';

    public function handle(): int
    {
        $filePath = $this->argument('file');
        $skipExisting = $this->option('skip-existing');
        
        if (!File::exists($filePath)) {
            $this->error("❌ File not found: {$filePath}");
            return self::FAILURE;
        }
        
        $this->info('🚀 Starting Blogr data import...');
        $this->line("📁 Import file: {$filePath}");
        
        try {
            // Import data using the service
            $importService = new BlogrImportService();
            $result = $importService->importFromFile($filePath, [
                'skip_existing' => $skipExisting
            ]);
            
            if (!$result['success']) {
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
            
        } catch (\Exception $e) {
            $this->error('❌ Import failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
