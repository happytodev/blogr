<?php

namespace Happytodev\Blogr\Commands;

use Happytodev\Blogr\Services\BlogrExportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BlogrExportCommand extends Command
{
    public $signature = 'blogr:export 
                        {--output= : Custom output path for the export file}
                        {--include-media : Include media files (images) in the export}';

    public $description = 'Export all Blogr data (posts, series, categories, tags) to a JSON or ZIP file';

    public function handle(): int
    {
        $this->info('🚀 Starting Blogr data export...');

        try {
            $exportService = new BlogrExportService;

            $outputPath = $this->option('output');
            $includeMedia = $this->option('include-media');

            $options = [];
            if ($includeMedia) {
                $options['include_media'] = true;
                $this->info('📸 Including media files in export...');
            }

            $filePath = $exportService->exportToFile($outputPath, $options);

            $this->info('✅ Blogr data exported successfully');
            $this->line("📁 Export file: {$filePath}");

            // Show file size
            $size = File::size($filePath);
            $this->line('📊 File size: '.$this->formatBytes($size));

            if ($includeMedia) {
                $this->line('📸 Media files included in ZIP archive');
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Export failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
