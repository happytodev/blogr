<?php

use Happytodev\Blogr\Commands\InstallBreezyCommand;
use Happytodev\Blogr\Commands\SyncAdminPathCommand;
use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

// ─── ISOLATION ──────────────────────────────────────────────
//
// Les tests de ce fichier appellent BlogrSettings::save(), qui écrit dans le
// vrai .env de l'application de test ET pousse BLOGR_ADMIN_PATH dans
// l'environnement du processus. config/blogr.php se contentant d'un
// env('BLOGR_ADMIN_PATH', 'admin'), le test « default value » lisait ensuite la
// valeur laissée par celui qui venait de tourner — 'admin2026' un jour,
// 'from-property' le lendemain, selon l'ordre.
//
// On remet donc les deux en état après chaque test : le fichier et la variable.

beforeEach(function () {
    $this->envPath = base_path('.env');
    $this->envExisted = File::exists($this->envPath);
    $this->envOriginal = $this->envExisted ? File::get($this->envPath) : null;
    $this->adminPathOriginal = getenv('BLOGR_ADMIN_PATH');
});

afterEach(function () {
    if ($this->envExisted) {
        File::put($this->envPath, $this->envOriginal);
    } elseif (File::exists($this->envPath)) {
        File::delete($this->envPath);
    }

    if ($this->adminPathOriginal === false) {
        putenv('BLOGR_ADMIN_PATH');
        unset($_ENV['BLOGR_ADMIN_PATH'], $_SERVER['BLOGR_ADMIN_PATH']);
    } else {
        putenv('BLOGR_ADMIN_PATH='.$this->adminPathOriginal);
        $_ENV['BLOGR_ADMIN_PATH'] = $this->adminPathOriginal;
        $_SERVER['BLOGR_ADMIN_PATH'] = $this->adminPathOriginal;
    }
});

// ─── CONFIG ─────────────────────────────────────────────

test('admin_path key exists in blogr config with default value', function () {
    // Ce test parle du DÉFAUT du fichier de config : il doit donc se placer dans
    // les conditions du défaut. config/blogr.php ne fait qu'un
    // env('BLOGR_ADMIN_PATH', 'admin'), donc il répond au sujet de l'environnement
    // — or les tests de ce fichier ont de bonnes raisons d'écrire cette variable.
    // Sans la neutraliser, on ne lit pas le défaut mais ce que le test précédent
    // a laissé, et le résultat dépend de l'ordre d'exécution.
    $original = getenv('BLOGR_ADMIN_PATH');
    putenv('BLOGR_ADMIN_PATH');
    unset($_ENV['BLOGR_ADMIN_PATH'], $_SERVER['BLOGR_ADMIN_PATH']);

    try {
        $config = require __DIR__.'/../../config/blogr.php';

        expect($config)->toHaveKey('admin_path');
        expect($config['admin_path'])->toBe('admin');
    } finally {
        if ($original !== false) {
            putenv('BLOGR_ADMIN_PATH='.$original);
            $_ENV['BLOGR_ADMIN_PATH'] = $original;
            $_SERVER['BLOGR_ADMIN_PATH'] = $original;
        }
    }
});

test('admin_path can be overridden by config', function () {
    config()->set('blogr.admin_path', 'backoffice');

    expect(config('blogr.admin_path'))->toBe('backoffice');
});

// ─── SAVE BEHAVIOR ─────────────────────────────────────

test('save method applies admin_path to runtime config', function () {
    $settings = app(BlogrSettings::class);

    $settings->admin_path = 'admin2026';

    $reflection = new ReflectionMethod($settings, 'save');
    $reflection->setAccessible(true);

    try {
        $reflection->invoke($settings);

        expect(config('blogr.admin_path'))->toBe('admin2026');
    } catch (Exception $e) {
        // save() may throw in test env if dependencies are missing
        // but the runtime config should still be set before any exception
        expect(config('blogr.admin_path'))->toBe('admin2026');
    }
});

test('save method writes BLOGR_ADMIN_PATH to .env', function () {
    $envPath = base_path('.env');

    if (! File::exists($envPath)) {
        File::put($envPath, "APP_KEY=test-key\nAPP_ENV=testing\n");
    }

    $original = File::get($envPath);

    try {
        $settings = app(BlogrSettings::class);
        $settings->admin_path = 'from-test-save';
        $reflection = new ReflectionMethod($settings, 'save');
        $reflection->setAccessible(true);

        try {
            $reflection->invoke($settings);
        } catch (Exception $e) {
            // Expected — save() may throw, .env write should still happen
        }

        $envContent = File::get($envPath);
        expect($envContent)->toContain('BLOGR_ADMIN_PATH');
        expect($envContent)->toContain('from-test-save');
    } finally {
        File::put($envPath, $original);
    }
});

test('save method reads admin_path from form state when available', function () {
    $settings = app(BlogrSettings::class);

    // Simulate Filament form state containing admin_path
    // First set the property as Livewire would
    $settings->admin_path = 'from-property';

    $reflection = new ReflectionMethod($settings, 'save');
    $reflection->setAccessible(true);

    try {
        $reflection->invoke($settings);
    } catch (Exception $e) {
        // save() may throw if form dependencies are missing
        // but runtime config should be set
    }

    // Verify the runtime config was updated
    expect(config('blogr.admin_path'))->not->toBeNull();
});

test('updateConfigFile persists admin_path when not in testing', function () {
    $settings = app(BlogrSettings::class);

    $reflection = new ReflectionMethod($settings, 'updateConfigFile');
    $reflection->setAccessible(true);

    $reflection->invoke($settings, ['admin_path' => 'updated-path']);

    expect(config('blogr.admin_path'))->toBe('updated-path');
});

test('mount method loads admin_path from config', function () {
    config()->set('blogr.admin_path', 'from-config');

    $settings = app(BlogrSettings::class);
    $settings->mount();

    expect($settings->admin_path)->toBe('from-config');
});

test('mount method defaults admin_path to admin when config is missing', function () {
    config()->offsetUnset('blogr.admin_path');

    $settings = app(BlogrSettings::class);
    $settings->mount();

    expect($settings->admin_path)->toBe('admin');
});

test('BlogrSettings form schema contains admin_path field', function () {
    $source = file_get_contents(
        (new ReflectionClass(BlogrSettings::class))->getFileName()
    );

    expect($source)->toContain("TextInput::make('admin_path')");
    expect($source)->toContain("'admin_path' =>");
});

// ─── SYNC COMMAND ──────────────────────────────────────

test('SyncAdminPathCommand class exists and extends Command', function () {
    expect(class_exists(SyncAdminPathCommand::class))->toBeTrue();
    expect(is_subclass_of(SyncAdminPathCommand::class, Command::class))->toBeTrue();
});

test('SyncAdminPathCommand has correct signature', function () {
    $command = new SyncAdminPathCommand;
    $reflection = new ReflectionClass($command);
    $property = $reflection->getProperty('signature');
    $property->setAccessible(true);

    $signature = $property->getValue($command);

    expect($signature)->toContain('blogr:sync-admin-path');
});

test('SyncAdminPathCommand fails gracefully when AdminPanelProvider does not exist', function () {
    config()->set('blogr.admin_path', 'backoffice');

    $this->artisan('blogr:sync-admin-path')
        ->assertFailed()
        ->expectsOutputToContain('AdminPanelProvider not found');
});

test('SyncAdminPathCommand updates path when file exists and path differs', function () {
    config()->set('blogr.admin_path', 'backoffice');

    $providerPath = app_path('Providers/Filament/AdminPanelProvider.php');
    File::ensureDirectoryExists(dirname($providerPath));
    File::put($providerPath, "<?php\n\nclass AdminPanelProvider extends PanelProvider\n{\n    public function panel(Panel \$panel): Panel\n    {\n        return \$panel\n            ->path('admin')\n            ->login();\n    }\n}");

    try {
        $this->artisan('blogr:sync-admin-path')
            ->assertSuccessful()
            ->expectsOutputToContain('backoffice');

        $updatedContent = File::get($providerPath);
        expect($updatedContent)->toContain("->path('backoffice')");
    } finally {
        File::delete($providerPath);
    }
});

test('SyncAdminPathCommand indicates no change needed when path matches', function () {
    config()->set('blogr.admin_path', 'admin');

    $providerPath = app_path('Providers/Filament/AdminPanelProvider.php');
    File::ensureDirectoryExists(dirname($providerPath));
    File::put($providerPath, "<?php\n\nclass AdminPanelProvider extends PanelProvider\n{\n    public function panel(Panel \$panel): Panel\n    {\n        return \$panel\n            ->path('admin')\n            ->login();\n    }\n}");

    try {
        $this->artisan('blogr:sync-admin-path')
            ->assertSuccessful()
            ->expectsOutputToContain('already uses path');
    } finally {
        File::delete($providerPath);
    }
});

// ─── INSTALL BREEZY COMMAND ────────────────────────────

test('InstallBreezyCommand class exists and extends Command', function () {
    expect(class_exists(InstallBreezyCommand::class))->toBeTrue();
    expect(is_subclass_of(InstallBreezyCommand::class, Command::class))->toBeTrue();
});

test('InstallBreezyCommand has correct signature', function () {
    $command = new InstallBreezyCommand;
    $reflection = new ReflectionClass($command);
    $property = $reflection->getProperty('signature');
    $property->setAccessible(true);

    $signature = $property->getValue($command);

    expect($signature)->toContain('blogr:install-breezy');
});
