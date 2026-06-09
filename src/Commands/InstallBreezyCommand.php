<?php

namespace Happytodev\Blogr\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class InstallBreezyCommand extends Command
{
    public $signature = 'blogr:install-breezy {--force : Non-interactive mode - answer yes to all prompts}';

    public $description = 'Install and configure Filament Breezy (2FA, profile, passkeys) for Blogr';

    public function handle(): int
    {
        $this->info('🔐 Installing Filament Breezy...');
        $this->newLine();

        if (class_exists(BreezyCore::class)) {
            $this->info('✅ Filament Breezy is already installed.');
        } else {
            $this->installPackage();
        }

        $this->newLine();

        // Publish and run Breezy migrations
        $this->publishMigrations();

        $this->newLine();

        // Step 3: Set up Filament admin theme (vite.config.js + panel provider registration)
        $this->setupFilamentTheme();

        $this->newLine();
        $this->setupBreezyTheme();

        $this->newLine();
        $this->updateAdminPanelProvider();
        $this->newLine();
        $this->call('filament:assets');
        $this->newLine();

        $this->info('✅ Filament Breezy has been installed and configured!');
        $this->newLine();
        $this->warn('⚠️  IMPORTANT: You must rebuild your assets to compile the theme:');
        $this->line('');
        $this->line('   npm run build');
        $this->line('');
        $this->line('📋 Then enable 2FA from your profile page:');
        $this->line('   1. Go to My Profile');
        $this->line('   2. Scroll to "Two Factor Authentication" section');
        $this->line('   3. Click "Enable" and scan the QR code with your authenticator app');
        $this->line('');

        return self::SUCCESS;
    }

    protected function installPackage(): void
    {
        $this->info('📦 Installing jeffgreco13/filament-breezy...');
        $this->line('  Running: composer require jeffgreco13/filament-breezy');
        $this->newLine();

        passthru('composer require jeffgreco13/filament-breezy 2>&1', $exitCode);

        if ($exitCode !== 0) {
            $this->warn('⚠️  Composer install may have failed. Continuing...');
        }
    }

    protected function publishMigrations(): void
    {
        $this->info('📋 Publishing Breezy migrations...');

        $this->call('vendor:publish', [
            '--tag' => 'filament-breezy-migrations',
            '--force' => $this->option('force'),
        ]);

        $this->info('  ✅ Breezy migrations published.');

        $this->call('migrate', [
            '--force' => true,
        ]);

        $this->info('  ✅ Breezy migrations executed.');
    }

    protected function setupFilamentTheme(): void
    {
        $this->info('🎨 Setting up Filament admin theme...');

        $themePath = resource_path('css/filament/admin/theme.css');
        $alreadyExists = File::exists($themePath);

        // Call make:filament-theme to properly set up vite.config.js + panel provider
        $this->call('make:filament-theme', [
            'panel' => 'admin',
            '--panel' => 'admin',
            '--force' => true,
            '--no-interaction' => true,
        ]);

        if ($alreadyExists) {
            $this->info('  ✅ Filament theme already configured.');
        } else {
            $this->info('  ✅ Filament admin theme created and registered.');
        }
    }

    protected function setupBreezyTheme(): void
    {
        $this->info('🎨 Setting up Filament admin theme...');

        $themePath = resource_path('css/filament/admin/theme.css');

        if (! File::exists($themePath)) {
            $this->line('  Creating Filament admin theme...');

            File::ensureDirectoryExists(dirname($themePath));

            $content = "@import 'tailwindcss';\n@import '../../../../vendor/filament/filament/resources/css/theme.css' layer(filament);\n@source '../../../../vendor/jeffgreco13/filament-breezy/resources/**/*';\n";

            File::put($themePath, $content);
            $this->info('  ✅ Filament admin theme created with Breezy support.');
        } else {
            $this->line('  Filament admin theme already exists.');
            $current = File::get($themePath);
            $modified = false;

            // Ensure the Filament theme import is present
            if (! str_contains($current, 'filament/resources/css/theme.css')) {
                if (str_contains($current, "@import 'tailwindcss'")) {
                    $current = preg_replace(
                        "/@import 'tailwindcss';/",
                        "@import 'tailwindcss';\n@import '../../../../vendor/filament/filament/resources/css/theme.css' layer(filament);",
                        $current,
                        1
                    );
                    $modified = true;
                    $this->info('  ✅ Filament theme import added.');
                } else {
                    $current = "@import 'tailwindcss';\n@import '../../../../vendor/filament/filament/resources/css/theme.css' layer(filament);\n".$current;
                    $modified = true;
                    $this->info('  ✅ Filament theme import added.');
                }
            }

            // Add Breezy @source if missing
            if (! str_contains($current, 'filament-breezy')) {
                $this->line('  Adding Breezy @source to theme...');

                if (str_contains($current, '@import ')) {
                    $current = preg_replace(
                        '/(@import .*?\n)/',
                        "$1\n@source '../../../../vendor/jeffgreco13/filament-breezy/resources/**/*';\n",
                        $current,
                        1
                    );
                } else {
                    $current .= "\n@source '../../../../vendor/jeffgreco13/filament-breezy/resources/**/*';\n";
                }

                $modified = true;
                $this->info('  ✅ Breezy @source added to theme.');
            } else {
                $this->info('  ✅ Breezy @source already present.');
            }

            if ($modified) {
                File::put($themePath, $current);
            }
        }
    }

    protected function updateAdminPanelProvider(): void
    {
        $this->info('⚙️  Configuring AdminPanelProvider...');

        $adminPanelPath = app_path('Providers/Filament/AdminPanelProvider.php');

        if (! File::exists($adminPanelPath)) {
            $this->warn('⚠️  AdminPanelProvider not found.');
            $this->line('Add BreezyCore manually:');
            $this->line('  use Jeffgreco13\FilamentBreezy\BreezyCore;');
            $this->line('  ->plugins([BreezyCore::make()->myProfile(...)->enableTwoFactorAuthentication()])');

            return;
        }

        $content = File::get($adminPanelPath);
        $modified = false;

        // Add BreezyCore import if needed
        if (! str_contains($content, 'use Jeffgreco13\FilamentBreezy\BreezyCore;')) {
            $content = preg_replace(
                '/(use [^;]+;)\n+(?=\s*class)/s',
                "$1\nuse Jeffgreco13\\FilamentBreezy\\BreezyCore;\n",
                $content,
                1
            );
            $modified = true;
            $this->info('  ✅ BreezyCore import added.');
        }

        // Check for BreezyCore configuration — add or update
        if (str_contains($content, 'BreezyCore::make()')) {
            if (! str_contains($content, 'enableTwoFactorAuthentication')) {
                // Add enableTwoFactorAuthentication after myProfile or after BreezyCore::make()
                $content = preg_replace(
                    '/(BreezyCore::make\(\)\s*(?:->myProfile\([^)]*\)\s*)?)/s',
                    "$1\n                ->enableTwoFactorAuthentication(\n                    force: false,\n                )",
                    $content,
                    1
                );
                $modified = true;
                $this->info('  ✅ enableTwoFactorAuthentication added to BreezyCore.');
            } else {
                $this->info('  ✅ enableTwoFactorAuthentication already present.');
            }
        } else {
            // BreezyCore::make() not found — add full config
            $breezyConfig = "BreezyCore::make()\n                ->myProfile(\n                    shouldRegisterUserMenu: true,\n                    hasAvatars: false,\n                )\n                ->enableTwoFactorAuthentication(\n                    force: false,\n                )";

            if (str_contains($content, '->plugins([')) {
                $content = preg_replace(
                    '/(->plugins\(\[)([^\]]*?)(\]\))/s',
                    "$1$2\n                {$breezyConfig},\n            $3",
                    $content
                );
            } else {
                $content = preg_replace(
                    '/(->authMiddleware\(\[)/s',
                    "->plugins([\n            {$breezyConfig},\n        ])\n        $1",
                    $content
                );
            }

            $modified = true;
            $this->info('  ✅ BreezyCore with 2FA and profile added to plugins.');
        }

        if ($modified) {
            File::put($adminPanelPath, $content);
            $this->info('  ✅ AdminPanelProvider updated.');
        }

        $this->newLine();
        $this->info('⚙️  Adding TwoFactorAuthenticatable trait to User model...');

        $userPath = app_path('Models/User.php');

        if (File::exists($userPath)) {
            $userContent = File::get($userPath);
            $userModified = false;

            if (! str_contains($userContent, 'use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;')) {
                // Add import after existing use statements (before the class declaration)
                $userContent = preg_replace(
                    '/(\nclass\s+\w+\s+extends\s+Authenticatable)/',
                    "\nuse Jeffgreco13\\FilamentBreezy\\Traits\\TwoFactorAuthenticatable;$1",
                    $userContent,
                    1
                );
                $userModified = true;
            }

            if (! preg_match('/^\s*use\s+\w*TwoFactorAuthenticatable/m', $userContent)) {
                // Add the trait inside the class — find the class opening brace
                // and add after it, or append to an existing indented use statement
                if (preg_match('/\{(\s*)\n\s*use\s+(\w+(?:\s*,\s*\w+)*)\s*;/', $userContent, $useMatch)) {
                    // Existing use statement inside class — append to it
                    $userContent = preg_replace(
                        '/(\{(\s*)\n\s*use\s+)(\w+(?:\s*,\s*\w+)*)(\s*;)/',
                        '$1$3, TwoFactorAuthenticatable$4',
                        $userContent,
                        1
                    );
                    $userModified = true;
                } else {
                    // No existing use statement inside class — add after opening brace
                    $userContent = preg_replace(
                        '/\{(\s*)\n/',
                        "{\\1\n    use TwoFactorAuthenticatable;\n",
                        $userContent,
                        1
                    );
                    $userModified = true;
                }
            }

            if ($userModified) {
                File::put($userPath, $userContent);
                $this->info('  ✅ TwoFactorAuthenticatable trait added to User model.');
            } else {
                $this->info('  ✅ TwoFactorAuthenticatable trait already present.');
            }
        } else {
            $this->warn('  ⚠️  User model not found. Add the trait manually:');
            $this->line('    use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;');
            $this->line('    class User extends Authenticatable { use TwoFactorAuthenticatable; }');
        }
    }
}
