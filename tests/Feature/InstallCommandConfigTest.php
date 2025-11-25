<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\StringInput;

uses(TestCase::class);

beforeEach(function () {
    // Create a test config file
    $configPath = config_path('blogr.php');
    $configDir = dirname($configPath);
    
    if (!is_dir($configDir)) {
        mkdir($configDir, 0755, true);
    }
    
    // Create minimal config file for testing
    $defaultConfig = <<<'PHP'
<?php

return [
    'cms' => [
        'enabled' => false,
    ],
    
    'homepage' => [
        'type' => 'blog',
    ],
];
PHP;
    
    file_put_contents($configPath, $defaultConfig);
});

afterEach(function () {
    // Clean up test config file
    $configPath = config_path('blogr.php');
    
    if (file_exists($configPath)) {
        unlink($configPath);
    }
});

function createMockConfigCommand(): \Happytodev\Blogr\Commands\BlogrInstallCommand {
    $command = new \Happytodev\Blogr\Commands\BlogrInstallCommand();
    $output = new OutputStyle(
        new StringInput(''),
        new BufferedOutput()
    );
    $command->setOutput($output);
    return $command;
}

it('updates config file with CMS enabled and blog homepage', function () {
    $configPath = config_path('blogr.php');
    
    // Run the updateConfigFile method
    $command = createMockConfigCommand();
    $reflectionMethod = new \ReflectionMethod($command, 'updateConfigFile');
    $reflectionMethod->setAccessible(true);
    
    // Update config with CMS enabled and blog homepage
    $reflectionMethod->invoke($command, [
        'cms.enabled' => true,
        'homepage.type' => 'blog',
    ]);
    
    // Verify config was updated
    $content = file_get_contents($configPath);
    expect($content)->toContain("'enabled' => true")
        ->and($content)->toContain("'type' => 'blog'");
})->group('installation', 'config');

it('updates config file with CMS enabled and cms homepage', function () {
    $configPath = config_path('blogr.php');
    
    // Run the updateConfigFile method
    $command = createMockConfigCommand();
    $reflectionMethod = new \ReflectionMethod($command, 'updateConfigFile');
    $reflectionMethod->setAccessible(true);
    
    // Update config with CMS enabled and cms homepage
    $reflectionMethod->invoke($command, [
        'cms.enabled' => true,
        'homepage.type' => 'cms',
    ]);
    
    // Verify config was updated
    $content = file_get_contents($configPath);
    expect($content)->toContain("'enabled' => true")
        ->and($content)->toContain("'type' => 'cms'");
})->group('installation', 'config');

it('updates config file with CMS disabled', function () {
    // First enable CMS
    $configPath = config_path('blogr.php');
    $enabledConfig = <<<'PHP'
<?php

return [
    'cms' => [
        'enabled' => true,
    ],
    
    'homepage' => [
        'type' => 'cms',
    ],
];
PHP;
    file_put_contents($configPath, $enabledConfig);
    
    // Run the updateConfigFile method to disable
    $command = createMockConfigCommand();
    $reflectionMethod = new \ReflectionMethod($command, 'updateConfigFile');
    $reflectionMethod->setAccessible(true);
    
    // Update config with CMS disabled
    $reflectionMethod->invoke($command, [
        'cms.enabled' => false,
        'homepage.type' => 'blog',
    ]);
    
    // Verify config was updated
    $content = file_get_contents($configPath);
    expect($content)->toContain("'enabled' => false")
        ->and($content)->toContain("'type' => 'blog'");
})->group('installation', 'config');

it('does not fail if config file does not exist yet', function () {
    // Delete config file
    $configPath = config_path('blogr.php');
    unlink($configPath);
    
    // Run the updateConfigFile method
    $command = createMockConfigCommand();
    $reflectionMethod = new \ReflectionMethod($command, 'updateConfigFile');
    $reflectionMethod->setAccessible(true);
    
    // Should not throw exception
    expect(fn() => $reflectionMethod->invoke($command, [
        'cms.enabled' => true,
        'homepage.type' => 'blog',
    ]))->not->toThrow(\Exception::class);
})->group('installation', 'config');

it('handles multiple config updates in single call', function () {
    $configPath = config_path('blogr.php');
    
    // Create config with multiple nested options
    $complexConfig = <<<'PHP'
<?php

return [
    'cms' => [
        'enabled' => false,
    ],
    
    'homepage' => [
        'type' => 'blog',
    ],
];
PHP;
    file_put_contents($configPath, $complexConfig);
    
    // Run the updateConfigFile method with multiple updates
    $command = createMockConfigCommand();
    $reflectionMethod = new \ReflectionMethod($command, 'updateConfigFile');
    $reflectionMethod->setAccessible(true);
    
    $reflectionMethod->invoke($command, [
        'cms.enabled' => true,
        'homepage.type' => 'cms',
    ]);
    
    // Verify all updates were applied
    $content = file_get_contents($configPath);
    expect($content)->toContain("'enabled' => true")
        ->and($content)->toContain("'type' => 'cms'");
})->group('installation', 'config');

it('preserves other config values when updating', function () {
    $configPath = config_path('blogr.php');
    
    // Create config with additional options
    $complexConfig = <<<'PHP'
<?php

return [
    'cms' => [
        'enabled' => false,
        'route' => [
            'prefix' => 'pages',
        ],
    ],
    
    'homepage' => [
        'type' => 'blog',
    ],
    
    'posts' => [
        'per_page' => 10,
    ],
];
PHP;
    file_put_contents($configPath, $complexConfig);
    
    // Run the updateConfigFile method
    $command = createMockConfigCommand();
    $reflectionMethod = new \ReflectionMethod($command, 'updateConfigFile');
    $reflectionMethod->setAccessible(true);
    
    $reflectionMethod->invoke($command, [
        'cms.enabled' => true,
    ]);
    
    // Verify other config values are preserved
    $content = file_get_contents($configPath);
    expect($content)->toContain("'enabled' => true")
        ->and($content)->toContain("'prefix' => 'pages'")
        ->and($content)->toContain("'per_page' => 10");
})->group('installation', 'config');
