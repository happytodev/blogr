<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

it('configures User model for Filament during installation', function () {
    // Create a temporary app directory structure
    $testAppPath = __DIR__ . '/../.temp-install-test';
    $userModelPath = $testAppPath . '/Models/User.php';
    
    // Clean up if exists
    if (File::exists($testAppPath)) {
        File::deleteDirectory($testAppPath);
    }
    
    // Create directory structure
    File::makeDirectory($testAppPath . '/Models', 0755, true);
    
    // Create a basic User model
    $basicUserModel = <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password'];
}
PHP;

    File::put($userModelPath, $basicUserModel);
    
    // Mock app_path to return our test path
    // Test the configuration method directly
    $command = new \Happytodev\Blogr\Commands\BlogrInstallCommand();
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('configureUserModelForFilament');
    $method->setAccessible(true);
    $method->invoke($command, $userModelPath);
    
    // Read the modified file
    $modifiedContent = File::get($userModelPath);
    
    // Verify Filament configuration was added
    expect($modifiedContent)
        ->toContain('use Filament\Models\Contracts\FilamentUser;')
        ->toContain('use Filament\Panel;')
        ->toContain('implements FilamentUser')
        ->toContain('public function canAccessPanel(Panel $panel): bool')
        ->toContain('str_ends_with($this->email');
    
    // Clean up
    File::deleteDirectory($testAppPath);
});

it('skips Filament configuration if User model already has FilamentUser', function () {
    // Create a temporary app directory structure
    $testAppPath = __DIR__ . '/../.temp-install-test-2';
    $userModelPath = $testAppPath . '/Models/User.php';
    
    // Clean up if exists
    if (File::exists($testAppPath)) {
        File::deleteDirectory($testAppPath);
    }
    
    // Create directory structure
    File::makeDirectory($testAppPath . '/Models', 0755, true);
    
    // Create a User model that already has Filament configuration
    $configuredUserModel = <<<'PHP'
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
PHP;

    File::put($userModelPath, $configuredUserModel);
    
    // Test the configuration method
    $command = new \Happytodev\Blogr\Commands\BlogrInstallCommand();
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('configureUserModelForFilament');
    $method->setAccessible(true);
    $method->invoke($command, $userModelPath);
    
    // Read the file (should be unchanged)
    $modifiedContent = File::get($userModelPath);
    
    // Count occurrences - should still be exactly 1
    $filamentUserCount = substr_count($modifiedContent, 'use Filament\Models\Contracts\FilamentUser;');
    
    expect($filamentUserCount)->toBe(1);
    
    // Clean up
    File::deleteDirectory($testAppPath);
});
