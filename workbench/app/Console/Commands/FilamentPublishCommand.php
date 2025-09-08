<?php

namespace Workbench\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class FilamentPublishCommand extends Command
{
    protected $signature = 'filament:publish';
    protected $description = 'Publish Filament views and assets for Workbench testing';

    public function handle()
    {
        $this->info('Publishing Filament views and assets...');

        // Publish Filament views
        \Illuminate\Support\Facades\Artisan::call('vendor:publish', [
            '--tag' => 'filament-views',
            '--force' => true
        ]);

        \Illuminate\Support\Facades\Artisan::call('vendor:publish', [
            '--tag' => 'blogr-views',
            '--force' => true
        ]);

        $this->info('Filament views and assets published successfully!');
    }
}
