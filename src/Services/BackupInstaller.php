<?php

namespace Happytodev\Blogr\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class BackupInstaller
{
    protected BackupInstallationChecker $checker;
    
    public function __construct()
    {
        $this->checker = new BackupInstallationChecker();
    }
    
    /**
     * Check if we can install Spatie Backup
     */
    public function canInstall(): bool
    {
        // If already installed, we can't install again
        if ($this->checker->isInstalled()) {
            return false;
        }
        
        // Check if composer.json exists and is writable
        $composerPath = base_path('composer.json');
        return File::exists($composerPath) && File::isWritable($composerPath);
    }
    
    /**
     * Install Spatie Laravel Backup package
     * Note: This adds it to composer.json, but user needs to run composer update
     */
    public function install(): bool
    {
        if (!$this->canInstall()) {
            return false;
        }
        
        $composerPath = base_path('composer.json');
        $content = File::get($composerPath);
        $composer = json_decode($content, true);
        
        if (!$composer) {
            return false;
        }
        
        // Add spatie/laravel-backup to require
        $composer['require']['spatie/laravel-backup'] = '^9.0';
        
        // Write back to composer.json
        File::put(
            $composerPath,
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        
        return true;
    }
    
    /**
     * Publish Spatie Backup configuration
     */
    public function publishConfig(): bool
    {
        if (!$this->checker->isInstalled()) {
            return false;
        }
        
        try {
            \Artisan::call('vendor:publish', [
                '--provider' => 'Spatie\Backup\BackupServiceProvider',
                '--tag' => 'backup-config'
            ]);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Configure Spatie Backup for Blogr
     */
    public function configureForBlogr(): bool
    {
        $configPath = config_path('backup.php');
        
        if (!File::exists($configPath)) {
            return false;
        }
        
        // Read current config
        $config = include $configPath;
        
        // Add storage/app/public to files include
        if (!in_array(storage_path('app/public'), $config['backup']['source']['files']['include'] ?? [])) {
            // This would require more complex config manipulation
            // For now, we'll document this in the README
        }
        
        return true;
    }
}
