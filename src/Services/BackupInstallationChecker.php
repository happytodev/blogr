<?php

namespace Happytodev\Blogr\Services;

use Illuminate\Support\Facades\File;

class BackupInstallationChecker
{
    /**
     * Check if Spatie Laravel Backup is installed
     */
    public function isInstalled(): bool
    {
        $composerPath = base_path('composer.json');
        
        if (!File::exists($composerPath)) {
            return false;
        }
        
        $content = File::get($composerPath);
        $composer = json_decode($content, true);
        
        if (!$composer) {
            return false;
        }
        
        // Check in require and require-dev
        $hasInRequire = isset($composer['require']['spatie/laravel-backup']);
        $hasInRequireDev = isset($composer['require-dev']['spatie/laravel-backup']);
        
        return $hasInRequire || $hasInRequireDev;
    }
    
    /**
     * Check if Spatie Backup config is published
     */
    public function isConfigPublished(): bool
    {
        return File::exists(config_path('backup.php'));
    }
    
    /**
     * Get installed version if available
     */
    public function getInstalledVersion(): ?string
    {
        $lockPath = base_path('composer.lock');
        
        if (!File::exists($lockPath)) {
            return null;
        }
        
        $lock = json_decode(File::get($lockPath), true);
        
        if (!$lock) {
            return null;
        }
        
        // Search in packages
        $packages = array_merge(
            $lock['packages'] ?? [],
            $lock['packages-dev'] ?? []
        );
        
        foreach ($packages as $package) {
            if ($package['name'] === 'spatie/laravel-backup') {
                return $package['version'] ?? null;
            }
        }
        
        return null;
    }
}
