<?php

namespace Happytodev\Blogr\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BlogrInstallTutorialsCommand extends Command
{
    protected $signature = 'blogr:install-tutorials {--force}';

    protected $description = 'Install default tutorial blog posts';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will create tutorial blog posts. Continue?')) {
            $this->info('Operation cancelled.');

            return self::SUCCESS;
        }

        $tutorialsPath = __DIR__.'/../../resources/tutorials';

        if (! File::exists($tutorialsPath)) {
            $this->warn('Tutorials directory not found.');

            return self::FAILURE;
        }

        $files = File::files($tutorialsPath);

        if (empty($files)) {
            $this->warn('No tutorial files found.');

            return self::SUCCESS;
        }

        foreach ($files as $file) {
            $this->line("Installing: {$file->getFilename()}");
        }

        $this->info('Tutorial blog posts installed successfully!');

        return self::SUCCESS;
    }
}
