<?php

namespace Happytodev\Blogr\Commands;

use Happytodev\Blogr\Database\Seeders\CmsPageSeeder;
use Happytodev\Blogr\Services\CmsPageBackupService;
use Happytodev\Blogr\Models\CmsPage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BlogrPublishDemoPagesCommand extends Command
{
    protected $signature = 'blogr:publish-demo-pages
        {--force : Overwrite existing demo pages}
        {--backup : Create backup before publishing}';

    protected $description = 'Publish demo CMS pages (Home and Contact) in multiple languages';

    public function __construct(
        private CmsPageBackupService $backupService,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🚀 Publishing Blogr demo CMS pages...');

        // Check if demo pages already exist
        $homeExists = CmsPage::where('slug', 'home-page')->exists();
        $contactExists = CmsPage::where('slug', 'contact')->exists();
        $demoExists = $homeExists || $contactExists;

        if ($demoExists && !$this->option('force')) {
            if (!$this->option('no-interaction')) {
                $this->warn('⚠️  Demo pages already exist!');
                if (!$this->confirm('Do you want to overwrite them? Use --force to skip this prompt.')) {
                    $this->info('Cancelled. Use --force to skip confirmation.');
                    return 0;
                }
            } else {
                $this->error('Demo pages already exist. Use --force to overwrite.');
                return 1;
            }
        }

        // Create backup if requested
        if ($this->option('backup')) {
            try {
                $this->info('📦 Creating backup before publishing...');
                $backupPath = $this->backupService->backup();
                $this->info("✅ Backup created at: {$backupPath}");
            } catch (\Exception $e) {
                $this->error("❌ Backup failed: " . $e->getMessage());
                return 1;
            }
        }

        // Run the seeder
        try {
            $this->info('📝 Seeding demo pages...');
            $seeder = new CmsPageSeeder();
            $seeder->run();

            $this->info('✅ Demo CMS pages published successfully!');
            $this->newLine();
            $this->info('📄 Pages created:');
            $this->line('  • Home Page (slug: home-page) - EN & FR');
            $this->line('  • Contact Page (slug: contact) - EN & FR');
            $this->newLine();
            $this->info('🎨 Blocks used: Hero, Section Separator (Wavy/Zigzag/Smooth), Stats, Features, Content, Gallery, CTA');
            $this->newLine();

            Log::info('BlogrPublishDemoPagesCommand: Demo pages published successfully');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Failed to publish demo pages: " . $e->getMessage());
            Log::error('BlogrPublishDemoPagesCommand: ' . $e->getMessage());
            return 1;
        }
    }
}
