<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Create a temporary app directory structure for testing
    $this->testAppPath = __DIR__ . '/../.temp-app';
    $this->userModelPath = $this->testAppPath . '/Models/User.php';
    
    // Clean up if exists
    if (File::exists($this->testAppPath)) {
        File::deleteDirectory($this->testAppPath);
    }
    
    // Create directory structure
    File::makeDirectory($this->testAppPath . '/Models', 0755, true);
});

afterEach(function () {
    // Clean up
    if (File::exists($this->testAppPath)) {
        File::deleteDirectory($this->testAppPath);
    }
});

it('adds FilamentUser implementation to User model when not present', function () {
    // Create a basic User model without Filament configuration
    $basicUserModel = <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
PHP;

    File::put($this->userModelPath, $basicUserModel);
    
    // Call the configuration method
    $command = new \Happytodev\Blogr\Commands\BlogrInstallCommand();
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('configureUserModelForFilament');
    $method->setAccessible(true);
    $method->invoke($command, $this->userModelPath);
    
    // Read the modified file
    $modifiedContent = File::get($this->userModelPath);
    
    // Assertions
    expect($modifiedContent)->toContain('use Filament\Models\Contracts\FilamentUser;')
        ->and($modifiedContent)->toContain('use Filament\Panel;')
        ->and($modifiedContent)->toContain('implements FilamentUser')
        ->and($modifiedContent)->toContain('public function canAccessPanel(Panel $panel): bool');
});

it('does not duplicate FilamentUser imports if already present', function () {
    // Create a User model that already has Filament configuration
    $configuredUserModel = <<<'PHP'
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
PHP;

    File::put($this->userModelPath, $configuredUserModel);
    
    // Call the configuration method
    $command = new \Happytodev\Blogr\Commands\BlogrInstallCommand();
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('configureUserModelForFilament');
    $method->setAccessible(true);
    $method->invoke($command, $this->userModelPath);
    
    // Read the modified file
    $modifiedContent = File::get($this->userModelPath);
    
    // Count occurrences - should only appear once
    $filamentUserCount = substr_count($modifiedContent, 'use Filament\Models\Contracts\FilamentUser;');
    $filamentPanelCount = substr_count($modifiedContent, 'use Filament\Panel;');
    $implementsCount = substr_count($modifiedContent, 'implements FilamentUser');
    
    expect($filamentUserCount)->toBe(1)
        ->and($filamentPanelCount)->toBe(1)
        ->and($implementsCount)->toBe(1);
});

it('uses email domain check for authorization', function () {
    // Create a basic User model
    $basicUserModel = <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    //
}
PHP;

    File::put($this->userModelPath, $basicUserModel);
    
    // Call the configuration method
    $command = new \Happytodev\Blogr\Commands\BlogrInstallCommand();
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('configureUserModelForFilament');
    $method->setAccessible(true);
    $method->invoke($command, $this->userModelPath);
    
    // Read the modified file
    $modifiedContent = File::get($this->userModelPath);
    
    // Should use email domain check
    expect($modifiedContent)->toContain('str_ends_with($this->email');
});

it('handles User model with multiple interfaces correctly', function () {
    // Create a User model that implements other interfaces
    $userWithInterfaces = <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    //
}
PHP;

    File::put($this->userModelPath, $userWithInterfaces);
    
    // Call the configuration method
    $command = new \Happytodev\Blogr\Commands\BlogrInstallCommand();
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('configureUserModelForFilament');
    $method->setAccessible(true);
    $method->invoke($command, $this->userModelPath);
    
    // Read the modified file
    $modifiedContent = File::get($this->userModelPath);
    
    // Should preserve existing interface and add FilamentUser
    $hasCorrectImplements = str_contains($modifiedContent, 'implements MustVerifyEmail, FilamentUser') ||
                           str_contains($modifiedContent, 'implements FilamentUser, MustVerifyEmail');
    
    expect($hasCorrectImplements)->toBeTrue();
});
