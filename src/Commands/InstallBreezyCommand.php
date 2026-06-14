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

        // Only call make:filament-theme if the theme CSS doesn't exist yet
        // Never use --force to avoid overwriting AdminPanelProvider customizations
        if (! $alreadyExists) {
            $this->call('make:filament-theme', [
                'panel' => 'admin',
                '--panel' => 'admin',
                '--no-interaction' => true,
            ]);
        }

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
            // Update hasAvatars to use config() so the Settings toggle works
            if (preg_match('/hasAvatars:\s*(true|false)/', $content)) {
                $content = preg_replace(
                    '/hasAvatars:\s*(true|false)/',
                    "hasAvatars: config('blogr.enable_avatar_upload', true)",
                    $content,
                    1
                );
                $modified = true;
                $this->info('  ✅ hasAvatars updated to use config() for Settings integration.');
            }

            if (! str_contains($content, 'enableTwoFactorAuthentication')) {
                // Add enableTwoFactorAuthentication after myProfile or after BreezyCore::make()
                $enableInsertion = "\n                ->enableTwoFactorAuthentication(\n                    force: false,\n                )";

                $myProfilePos = strpos($content, '->myProfile(');
                if ($myProfilePos !== false) {
                    $insertPos = $this->findMatchingParen($content, $myProfilePos + strlen('->myProfile(')) + 1;
                    $content = substr_replace($content, $enableInsertion, $insertPos, 0);
                } else {
                    // No myProfile — insert after BreezyCore::make()
                    $content = preg_replace(
                        '/(BreezyCore::make\(\))/s',
                        '$1'.$enableInsertion,
                        $content,
                        1
                    );
                }
                $modified = true;
                $this->info('  ✅ enableTwoFactorAuthentication added to BreezyCore.');
            } else {
                $this->info('  ✅ enableTwoFactorAuthentication already present.');
            }

            // Repair misplaced avatarUploadComponent (from previous buggy versions that inserted it inside myProfile)
            if (str_contains($content, '->avatarUploadComponent')) {
                if ($this->isAvatarUploadInsideMyProfile($content)) {
                    // Extract the full avatarUploadComponent call
                    $avatarPos = strpos($content, '->avatarUploadComponent');
                    $closeParen = $this->findMatchingParen($content, $avatarPos + strlen('->avatarUploadComponent('));
                    $fullCall = substr($content, $avatarPos, $closeParen - $avatarPos + 1);
                    // Upgrade the call with image editor if not present
                    if (! str_contains($fullCall, 'imageEditor')) {
                        $fullCall = "->avatarUploadComponent(fn (\$fileUpload) => \$fileUpload->imageEditor()->imageCropAspectRatio('1:1')->imageResizeTargetWidth('200')->imageResizeTargetHeight('200'))";
                    }
                    // Remove the misplaced call
                    $content = substr_replace($content, '', $avatarPos, $closeParen - $avatarPos + 1);
                    // Clean up trailing comma/whitespace left after removal
                    $content = preg_replace('/,\s*\n\s*\)/', ')'."\n", $content);
                    // Find myProfile closing paren AFTER the removal
                    $myProfileOpen = strpos($content, '->myProfile(');
                    $myProfileClose = $this->findMatchingParen($content, $myProfileOpen + strlen('->myProfile('));
                    // Insert the call after myProfile's closing paren
                    $insertPos = $myProfileClose + 1;
                    $content = substr_replace($content, "\n            ".$fullCall, $insertPos, 0);
                    $modified = true;
                    $this->info('  ✅ Repaired misplaced avatarUploadComponent (moved outside myProfile).');
                }
            }

            // Add avatarUploadComponent with image editor if not present
            if (! str_contains($content, 'avatarUploadComponent')) {
                $insertion = "\n                ->avatarUploadComponent(fn (\$fileUpload) => \$fileUpload->imageEditor()->imageCropAspectRatio('1:1')->imageResizeTargetWidth('200')->imageResizeTargetHeight('200'))";

                $pos = strpos($content, '->myProfile(');
                if ($pos !== false) {
                    $insertPos = $this->findMatchingParen($content, $pos + strlen('->myProfile(')) + 1;
                    $content = substr_replace($content, $insertion, $insertPos, 0);
                    $modified = true;
                    $this->info('  ✅ avatarUploadComponent with image editor added to BreezyCore.');
                }
            } else {
                $this->info('  ✅ avatarUploadComponent already present.');
            }

            // Repair: remove ->imageUrl() calls (method doesn't exist in this Filament version)
            $imageUrlPos = strpos($content, '->imageUrl(');
            if ($imageUrlPos !== false) {
                $closePos = $this->findMatchingParen($content, $imageUrlPos + strlen('->imageUrl('));
                $content = substr_replace($content, '', $imageUrlPos, $closePos - $imageUrlPos + 1);
                $modified = true;
                $this->info('  ✅ Removed invalid ->imageUrl() call from avatarUploadComponent.');
            }

            // Add AuthorBio myProfileComponent if not present
            if (! str_contains($content, 'AuthorBio')) {
                $insertion = "\n                ->myProfileComponents(['author_bio' => \\Happytodev\\Blogr\\Filament\\Livewire\\AuthorBio::class])";

                $pos = strpos($content, '->enableTwoFactorAuthentication');
                if ($pos !== false) {
                    $content = substr_replace($content, $insertion, $pos, 0);
                } else {
                    $pos = strpos($content, 'BreezyCore::make()');
                    if ($pos !== false) {
                        $closeParen = $this->findMatchingParen($content, $pos + strlen('BreezyCore::make()'));
                        $content = substr_replace($content, $insertion, $closeParen + 1, 0);
                    }
                }
                $modified = true;
                $this->info('  ✅ AuthorBio component added to BreezyCore profile.');
            } else {
                $this->info('  ✅ AuthorBio component already present.');
            }
        } else {
            // BreezyCore::make() not found — add full config
            $breezyConfig = "BreezyCore::make()\n                ->myProfile(\n                    shouldRegisterUserMenu: true,\n                    hasAvatars: config('blogr.enable_avatar_upload', true),\n                )\n                ->avatarUploadComponent(fn (\$fileUpload) => \$fileUpload->imageEditor()->imageCropAspectRatio('1:1')->imageResizeTargetWidth('200')->imageResizeTargetHeight('200'))\n                ->myProfileComponents(['author_bio' => \Happytodev\Blogr\Filament\Livewire\AuthorBio::class])\n                ->enableTwoFactorAuthentication(\n                    force: false,\n                )";

            if (str_contains($content, '->plugins([')) {
                $pluginsOpen = strpos($content, '->plugins([');
                $innerOpen = $pluginsOpen + strlen('->plugins([');
                // Find the matching closing bracket for the opening [
                $closePos = $this->findMatchingBracket($content, $innerOpen);
                // Insert the Breezy config just before the closing ]
                $insertPos = $closePos;
                $content = substr_replace($content, "\n                {$breezyConfig},\n            ", $insertPos, 0);
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

            // Add avatar_url to fillable if not present
            if (! str_contains($userContent, "'avatar_url'")) {
                $userContent = preg_replace(
                    "/'avatar',\n/",
                    "'avatar',\n        'avatar_url',\n",
                    $userContent,
                    1
                );
                $modified = true;
                $this->info('  ✅ avatar_url added to User model fillable.');
            } else {
                $this->info('  ✅ avatar_url already in User model fillable.');
            }

            // Add getFilamentAvatarUrl() if not present
            if (! str_contains($userContent, 'getFilamentAvatarUrl')) {
                $method = "\n\n    public function getFilamentAvatarUrl(): ?string\n    {\n        if (\$this->avatar_url) {\n            return \\Illuminate\\Support\\Facades\\Storage::disk('public')->url(\$this->avatar_url);\n        }\n\n        if (\$this->avatar) {\n            return \\Illuminate\\Support\\Facades\\Storage::disk('public')->url(\$this->avatar);\n        }\n\n        return \$this->gravatar_url;\n    }";

                $userContent = preg_replace(
                    '/\n\s*public function canAccessPanel/',
                    "{$method}\n\n    public function canAccessPanel",
                    $userContent,
                    1
                );
                $modified = true;
            }

            // Add HasAvatar interface and import if not present
            if (! str_contains($userContent, 'HasAvatar')) {
                // Add import
                if (! str_contains($userContent, 'use Filament\\Models\\Contracts\\HasAvatar;')) {
                    $userContent = preg_replace(
                        '/(use Filament\\\Models\\\Contracts\\\FilamentUser;)/',
                        "$1\nuse Filament\\Models\\Contracts\\HasAvatar;",
                        $userContent,
                        1
                    );
                }
                // Add HasAvatar to implements
                $userContent = preg_replace(
                    '/(implements\s+FilamentUser)/',
                    '$1, HasAvatar',
                    $userContent,
                    1
                );
                $modified = true;
                $this->info('  ✅ HasAvatar interface added to User model.');
            } else {
                $this->info('  ✅ HasAvatar already present on User model.');
            }

            if ($modified) {
                File::put($userPath, $userContent);
            }
        } else {
            $this->warn('  ⚠️  User model not found. Add the trait manually:');
            $this->line('    use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;');
            $this->line('    class User extends Authenticatable { use TwoFactorAuthenticatable; }');
        }
    }

    private function findMatchingParen(string $content, int $openPos): int
    {
        $depth = 1;
        $i = $openPos;
        while ($depth > 0 && isset($content[$i])) {
            if ($content[$i] === '(') {
                $depth++;
            } elseif ($content[$i] === ')') {
                $depth--;
            }
            $i++;
        }

        return $i - 1;
    }

    private function findMatchingBracket(string $content, int $openPos): int
    {
        $depth = 1;
        $i = $openPos;
        while ($depth > 0 && isset($content[$i])) {
            if ($content[$i] === '[') $depth++;
            elseif ($content[$i] === ']') $depth--;
            $i++;
        }

        return $i - 1;
    }

    private function isAvatarUploadInsideMyProfile(string $content): bool
    {
        $myProfileOpen = strpos($content, '->myProfile(');
        if ($myProfileOpen === false) {
            return false;
        }

        $myProfileClose = $this->findMatchingParen($content, $myProfileOpen + strlen('->myProfile('));

        $avatarPos = strpos($content, '->avatarUploadComponent');
        if ($avatarPos === false) {
            return false;
        }

        return $avatarPos > $myProfileOpen && $avatarPos < $myProfileClose;
    }
}
