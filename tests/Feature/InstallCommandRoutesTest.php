<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\StringInput;

uses(TestCase::class);

beforeEach(function () {
    // Create a test routes/web.php file with default Laravel route
    $routesPath = base_path('routes');
    
    if (!is_dir($routesPath)) {
        mkdir($routesPath, 0755, true);
    }
    
    $webRoutesPath = base_path('routes/web.php');
    
    // Create default Laravel welcome route
    $defaultRoute = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
PHP;
    
    file_put_contents($webRoutesPath, $defaultRoute);
});

afterEach(function () {
    // Clean up test routes file
    $webRoutesPath = base_path('routes/web.php');
    
    if (file_exists($webRoutesPath)) {
        unlink($webRoutesPath);
    }
});

function createMockRoutesCommand(): \Happytodev\Blogr\Commands\BlogrInstallCommand {
    $command = new \Happytodev\Blogr\Commands\BlogrInstallCommand();
    $output = new OutputStyle(
        new StringInput(''),
        new BufferedOutput()
    );
    $command->setOutput($output);
    return $command;
}

it('comments out default Laravel welcome route during installation', function () {
    $webRoutesPath = base_path('routes/web.php');
    
    // Verify route exists before
    $contentBefore = file_get_contents($webRoutesPath);
    expect($contentBefore)->toContain("Route::get('/', function ()")
        ->and($contentBefore)->toContain("return view('welcome');");
    
    // Run the command method directly
    $command = createMockRoutesCommand();
    $reflectionMethod = new \ReflectionMethod($command, 'commentOutDefaultRoute');
    $reflectionMethod->setAccessible(true);
    
    // Execute the method
    $reflectionMethod->invoke($command);
    
    // Verify route is commented after
    $contentAfter = file_get_contents($webRoutesPath);
    expect($contentAfter)->toContain('// Commented out by Blogr installation')
        ->and($contentAfter)->toContain("// Route::get('/', function ()");
})->group('installation', 'routes');

it('does not fail if routes/web.php does not exist', function () {
    // Delete the routes file
    $webRoutesPath = base_path('routes/web.php');
    unlink($webRoutesPath);
    
    // Run the command method
    $command = createMockRoutesCommand();
    $reflectionMethod = new \ReflectionMethod($command, 'commentOutDefaultRoute');
    $reflectionMethod->setAccessible(true);
    
    // Should not throw exception
    expect(fn() => $reflectionMethod->invoke($command))->not->toThrow(\Exception::class);
})->group('installation', 'routes');

it('does not comment route if already commented', function () {
    $webRoutesPath = base_path('routes/web.php');
    
    // Create already commented route
    $commentedRoute = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

// Commented out by Blogr installation
// Route::get('/', function () {
//     return view('welcome');
// });
PHP;
    
    file_put_contents($webRoutesPath, $commentedRoute);
    
    // Run the command method
    $command = createMockRoutesCommand();
    $reflectionMethod = new \ReflectionMethod($command, 'commentOutDefaultRoute');
    $reflectionMethod->setAccessible(true);
    $reflectionMethod->invoke($command);
    
    // Verify content hasn't changed (no double commenting)
    $contentAfter = file_get_contents($webRoutesPath);
    expect($contentAfter)->toBe($commentedRoute);
})->group('installation', 'routes');

it('preserves other routes when commenting default route', function () {
    $webRoutesPath = base_path('routes/web.php');
    
    // Create routes file with multiple routes
    $multipleRoutes = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/about', function () {
    return view('about');
});

Route::get('/contact', function () {
    return view('contact');
});
PHP;
    
    file_put_contents($webRoutesPath, $multipleRoutes);
    
    // Run the command method
    $command = createMockRoutesCommand();
    $reflectionMethod = new \ReflectionMethod($command, 'commentOutDefaultRoute');
    $reflectionMethod->setAccessible(true);
    $reflectionMethod->invoke($command);
    
    // Verify only default route is commented
    $contentAfter = file_get_contents($webRoutesPath);
    expect($contentAfter)->toContain("// Route::get('/', function ()")
        ->and($contentAfter)->toContain("Route::get('/about', function ()")
        ->and($contentAfter)->toContain("Route::get('/contact', function ()");
})->group('installation', 'routes');
