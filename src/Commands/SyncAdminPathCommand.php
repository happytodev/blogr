<?php

namespace Happytodev\Blogr\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncAdminPathCommand extends Command
{
    public $signature = 'blogr:sync-admin-path {--show : Only show the current admin path without making changes}';

    public $description = 'Sync the admin panel path from config/blogr.php to the AdminPanelProvider';

    public function handle(): int
    {
        $adminPath = config('blogr.admin_path', 'admin');

        $this->info("Current admin path config: <fg=yellow>{$adminPath}</>");

        if ($this->option('show')) {
            return self::SUCCESS;
        }

        $adminPanelPath = app_path('Providers/Filament/AdminPanelProvider.php');

        if (! File::exists($adminPanelPath)) {
            $this->warn('AdminPanelProvider not found at: '.$adminPanelPath);
            $this->line('Your admin panel path is configured in config/blogr.php but the AdminPanelProvider file was not found.');
            $this->line('Make sure to create one or update it manually with: ->path(\''.$adminPath.'\')');

            return self::FAILURE;
        }

        $content = File::get($adminPanelPath);

        if (! preg_match('/->path\(([^)]+)\)/', $content, $matches)) {
            $this->warn('No ->path() call found in AdminPanelProvider.');
            $this->line('Add this to your panel configuration: ->path(\''.$adminPath.'\')');

            return self::FAILURE;
        }

        $currentPath = trim($matches[1], "'\" ");
        $newPathValue = "'{$adminPath}'";

        if ($currentPath === $adminPath || trim($currentPath, "'\" ") === $adminPath) {
            $this->info("✅ AdminPanelProvider already uses path: <fg=yellow>{$adminPath}</>");

            return self::SUCCESS;
        }

        $replaced = preg_replace(
            '/->path\(([^)]+)\)/',
            "->path({$newPathValue})",
            $content,
            1
        );

        if ($replaced === $content) {
            $this->warn('Could not update the path. Please update it manually.');

            return self::FAILURE;
        }

        File::put($adminPanelPath, $replaced);
        $this->info("✅ Admin panel path updated from '<fg=red>{$currentPath}</>' to '<fg=green>{$adminPath}</>'");
        $this->line('');
        $this->line("Your admin panel is now accessible at: <fg=yellow>/{$adminPath}</>");
        $this->line('');
        $this->warn('⚠️  If you changed the path, make sure to update any bookmarks or redirects.');
        $this->line('   You may need to re-login after the path change.');

        return self::SUCCESS;
    }
}
