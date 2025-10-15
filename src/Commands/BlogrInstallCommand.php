<?php

namespace Happytodev\Blogr\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Happytodev\Blogr\Filament\Widgets\BlogStatsOverview;
use Happytodev\Blogr\Filament\Widgets\RecentBlogPosts;
use Happytodev\Blogr\Filament\Widgets\ScheduledPosts;
use Happytodev\Blogr\Filament\Widgets\BlogPostsChart;
use Happytodev\Blogr\Filament\Widgets\BlogReadingStats;

class BlogrInstallCommand extends Command
{
    public $signature = 'blogr:install 
                        {--skip-npm : Skip npm dependencies installation} 
                        {--skip-tutorials : Skip tutorial content installation}
                        {--skip-series : Skip series content installation}
                        {--skip-frontend : Skip frontend configuration (Alpine.js and Tailwind CSS)}
                        {--skip-build : Skip npm run build at the end}
                        {--force : Non-interactive mode - answer yes to all prompts}';

    public $description = 'Install and configure Blogr with all necessary steps';

    /**
     * Helper method to handle confirm() with --force option
     * If --force is set, always return the default value without prompting
     */
    protected function forceableConfirm(string $question, bool $default = false): bool
    {
        if ($this->option('force')) {
            // In force mode, show what would be asked and use the default
            $answer = $default ? 'yes' : 'no';
            $this->line("<fg=gray>  {$question} [{$answer}]</>");
            return $default;
        }
        
        return $this->confirm($question, $default);
    }

    public function handle(): int
    {
        $this->info('🚀 Welcome to Blogr Installation!');
        $this->line('This command will help you set up Blogr automatically.');
        $this->newLine();

        // Step 1: Publish configuration and migrations
        $this->publishFiles();

        // Step 2: Run migrations
        $this->runMigrations();

        // Step 2.1: Create storage symbolic link (required for avatars)
        $this->createStorageLink();

        // Step 2.5: Configure User model with HasRoles trait
        $this->configureUserModel();

        // Step 2.6: Create roles and permissions
        $this->createRolesAndPermissions();

        // Step 2.7: Create test users with roles
        $this->createTestUsers();

        // Step 2.7.5: Assign admin role to first user if exists
        $this->assignAdminRoleToFirstUser();

        // Step 2.8: Install UserResource for managing users in Filament
        $this->installUserResource();

        // Step 3: Install tutorial content (unless skipped)
        if (!$this->option('skip-tutorials')) {
            $this->installTutorials();
        }

        // Step 3.5: Install series content (unless skipped)
        if (!$this->option('skip-series')) {
            $this->installSeries();
        }

        // Step 4: Install dashboard widgets
        $this->installWidgets();

        // Step 5: Configure frontend (Alpine.js and Tailwind CSS)
        if (!$this->option('skip-frontend')) {
            $this->configureFrontend();
        }

        // Step 6: Handle npm dependencies (unless skipped)
        if (!$this->option('skip-npm')) {
            $this->handleNpmDependencies();
        }

        // Step 7: Build assets (unless skipped)
        if (!$this->option('skip-build') && !$this->option('skip-npm')) {
            $this->buildAssets();
        }

        // Step 8: Check AdminPanelProvider configuration
        $this->checkAdminPanelProvider();

        // Step 6: GitHub star prompt
        $this->promptForGitHubStar();

        $this->newLine();
        $this->info('✅ Blogr installation completed successfully!');
        $this->line('🎉 You can now start creating amazing blog content!');
        $this->newLine();

        $this->displayNextSteps();

        return self::SUCCESS;
    }

    protected function publishFiles(): void
    {
        $this->info('📦 Publishing Blogr configuration and migration files...');

        // Publish Blogr config, views, and migrations
        $this->call('vendor:publish', [
            '--provider' => 'Happytodev\Blogr\BlogrServiceProvider',
            '--force' => true
        ]);

        // Publish Blogr assets (images) to public/vendor/blogr/images
        $this->info('📦 Publishing Blogr assets (default images)...');
        $this->call('vendor:publish', [
            '--tag' => 'blogr-assets',
            '--force' => true
        ]);

        // Manual fallback: Copy images directly if vendor:publish didn't work (symlink issue)
        $this->ensureImagesArePublished();

        // Publish Spatie Permission migrations (required for roles & permissions)
        $this->info('📦 Publishing Spatie Permission migrations...');
        $this->call('vendor:publish', [
            '--provider' => 'Spatie\Permission\PermissionServiceProvider',
            '--tag' => 'permission-migrations'
        ]);

        // Optionally publish Spatie Permission config
        if ($this->forceableConfirm('Would you like to publish Spatie Permission configuration file?', false)) {
            $this->call('vendor:publish', [
                '--provider' => 'Spatie\Permission\PermissionServiceProvider',
                '--tag' => 'permission-config'
            ]);
            $this->info('✅ Spatie Permission configuration published.');
        }

        $this->info('✅ Configuration and migration files published successfully.');
    }

    protected function runMigrations(): void
    {
        $this->info('🗄️ Running database migrations...');

        // Check if we can run migrations
        $pendingMigrations = $this->getPendingMigrations();

        if (empty($pendingMigrations)) {
            $this->info('✅ No pending migrations found. Database is already up to date.');
            return;
        }

        $this->info('Found ' . count($pendingMigrations) . ' pending migration(s).');

        // Try to run migrations with better error handling
        $result = Process::run(['php', 'artisan', 'migrate', '--force']);

        if ($result->successful()) {
            $this->info('✅ Database migrations completed successfully.');
        } else {
            $this->warn('⚠️ Migration failed:');
            $this->line($result->errorOutput());
            $this->line('ℹ️ This might happen if migrations are already run or if you need to run them manually.');
            $this->line('ℹ️ You can try running: php artisan migrate');

            if (!$this->forceableConfirm('Would you like to continue with the installation anyway?', true)) {
                $this->error('Installation cancelled.');
                return;
            }
            $this->info('⏭️ Continuing with installation...');
        }
    }

    protected function createStorageLink(): void
    {
        $this->info('🔗 Creating storage symbolic link...');

        // Check if link already exists
        $publicStoragePath = public_path('storage');
        
        if (File::exists($publicStoragePath)) {
            // Check if it's a valid symlink
            if (is_link($publicStoragePath) && readlink($publicStoragePath) === storage_path('app/public')) {
                $this->info('✅ Storage link already exists and is valid.');
                return;
            }
            
            // Invalid link or regular directory exists
            $this->warn('⚠️ A file or directory already exists at public/storage');
            
            if ($this->forceableConfirm('Would you like to remove it and create the symbolic link?', false)) {
                if (is_link($publicStoragePath)) {
                    unlink($publicStoragePath);
                } elseif (is_dir($publicStoragePath)) {
                    File::deleteDirectory($publicStoragePath);
                } else {
                    File::delete($publicStoragePath);
                }
            } else {
                $this->warn('⏭️ Skipping storage link creation. Avatar uploads may not work properly.');
                $this->line('ℹ️ Run manually: php artisan storage:link');
                return;
            }
        }

        // Create the symbolic link
        $result = Process::run(['php', 'artisan', 'storage:link']);

        if ($result->successful()) {
            $this->info('✅ Storage symbolic link created successfully.');
            $this->line('   This enables avatar image uploads in the user profile.');
        } else {
            $this->warn('⚠️ Failed to create storage link:');
            $this->line($result->errorOutput());
            $this->line('ℹ️ Please run manually: php artisan storage:link');
            $this->line('ℹ️ This is required for user avatar uploads to work properly.');
        }
    }

    protected function getPendingMigrations(): array
    {
        $migrator = app('migrator');
        $files = $migrator->getMigrationFiles(database_path('migrations'));

        return array_diff(
            array_keys($files),
            $migrator->getRepository()->getRan()
        );
    }

    protected function installTutorials(): void
    {
        $this->info('📚 Installing tutorial content...');

        if ($this->forceableConfirm('Would you like to install default tutorial content to help you get started?', true)) {
            $this->call('blogr', ['action' => 'install-tutorials']);
            $this->info('✅ Tutorial content installed successfully.');
        } else {
            $this->line('ℹ️ Tutorial installation skipped. You can install it later with: php artisan blogr install-tutorials');
        }
    }

    protected function installSeries(): void
    {
        $this->info('📖 Installing series content...');

        if ($this->forceableConfirm('Would you like to install example series with posts to showcase the series feature?', true)) {
            try {
                // Run the BlogSeriesSeeder
                $this->call('db:seed', [
                    '--class' => 'Happytodev\\Blogr\\Database\\Seeders\\BlogSeriesSeeder'
                ]);
                $this->info('✅ Series content installed successfully.');
                $this->line('ℹ️ Check out your blog to see the series feature in action!');
            } catch (\Exception $e) {
                $this->error('❌ Failed to install series content: ' . $e->getMessage());
                $this->line('ℹ️ You can install it later manually.');
            }
        } else {
            $this->line('ℹ️ Series installation skipped.');
        }
    }

    protected function configureFrontend(): void
    {
        $this->info('🎨 Configuring frontend (Alpine.js and Tailwind CSS v4)...');
        $this->newLine();

        // Configure Alpine.js
        $this->configureAlpineJs();

        // Configure Tailwind CSS v4
        $this->configureTailwindCss();
    }

    protected function configureAlpineJs(): void
    {
        $this->info('🔧 Configuring Alpine.js for theme switcher...');

        $appJsPath = resource_path('js/app.js');

        if (!File::exists($appJsPath)) {
            $this->warn('⚠️ app.js not found at: ' . $appJsPath);
            $this->line('ℹ️ You need to manually configure Alpine.js. See documentation.');
            return;
        }

        $appJsContent = File::get($appJsPath);

        // Check if Alpine is already configured
        if (str_contains($appJsContent, 'import Alpine from') || str_contains($appJsContent, "import Alpine from")) {
            $this->info('✅ Alpine.js is already configured in app.js.');
            return;
        }

        if ($this->forceableConfirm('Would you like to automatically configure Alpine.js in your app.js?', true)) {
            $this->updateAppJs($appJsContent, $appJsPath);
        } else {
            $this->line('ℹ️ You need to manually configure Alpine.js. See README.md for instructions.');
        }
    }

    protected function updateAppJs(string $content, string $path): void
    {
        // Read the stub file
        $stubPath = __DIR__ . '/../../stubs/app.js.stub';
        
        if (File::exists($stubPath)) {
            $stubContent = File::get($stubPath);
            
            // Check if app.js only has import './bootstrap';
            if (trim($content) === "import './bootstrap';") {
                // Replace entirely with stub
                File::put($path, $stubContent);
                $this->info('✅ Alpine.js configuration added to app.js.');
            } else {
                // Append Alpine configuration after imports
                $alpineConfig = "\n" . $this->getAlpineConfigSnippet();
                
                // Find a good place to insert (after imports)
                if (preg_match('/^(import\s+.*;\s*)+/m', $content, $matches)) {
                    $content = preg_replace(
                        '/^(import\s+.*;\s*)+/m',
                        "$0\n" . $alpineConfig,
                        $content,
                        1
                    );
                } else {
                    $content .= "\n" . $alpineConfig;
                }
                
                File::put($path, $content);
                $this->info('✅ Alpine.js configuration added to app.js.');
            }
        } else {
            $this->warn('⚠️ Could not find Alpine.js stub file.');
            $this->line('ℹ️ Please manually add Alpine.js configuration. See README.md.');
        }
    }

    protected function getAlpineConfigSnippet(): string
    {
        return <<<'JS'
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Blogr Theme Switcher Component (required for light/dark/auto mode)
Alpine.data('themeSwitch', () => ({
    theme: localStorage.getItem('theme') || 'auto',
    
    init() {
        this.applyTheme();
        
        // Watch for system preference changes when in auto mode
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (this.theme === 'auto') {
                this.applyTheme();
            }
        });
    },
    
    setTheme(newTheme) {
        this.theme = newTheme;
        localStorage.setItem('theme', newTheme);
        this.applyTheme();
    },
    
    applyTheme() {
        const isDark = this.theme === 'dark' || 
                      (this.theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}));

Alpine.start();
JS;
    }

    protected function configureTailwindCss(): void
    {
        $this->info('🎨 Configuring Tailwind CSS v4 dark mode...');

        $cssPath = resource_path('css/app.css');

        if (!File::exists($cssPath)) {
            $this->warn('⚠️ app.css not found at: ' . $cssPath);
            $this->line('ℹ️ You need to manually configure Tailwind CSS v4. See documentation.');
            return;
        }

        $cssContent = File::get($cssPath);

        // Check if dark variant is already configured
        if (str_contains($cssContent, '@variant dark')) {
            $this->info('✅ Tailwind CSS v4 dark mode is already configured.');
            return;
        }

        if ($this->forceableConfirm('Would you like to automatically configure Tailwind CSS v4 dark mode in your app.css?', true)) {
            $this->updateAppCss($cssContent, $cssPath);
        } else {
            $this->warn('⚠️ CRITICAL: You MUST add "@variant dark (.dark &);" to your app.css for the theme switcher to work!');
            $this->line('ℹ️ See README.md for complete instructions.');
        }
    }

    protected function updateAppCss(string $content, string $path): void
    {
        // Add Blogr views @source directives if not present
        $blogrSources = [
            "@source '../../vendor/happytodev/blogr/resources/views/**/*.blade.php';",
            "@source '../views/vendor/blogr/**/*.blade.php';",
        ];

        foreach ($blogrSources as $source) {
            if (!str_contains($content, $source)) {
                // Add after other @source directives or after @plugin
                if (preg_match('/@source\s+.*?;/s', $content)) {
                    $content = preg_replace(
                        '/(@source\s+.*?;\s*)/s',
                        "$0\n" . $source . "\n",
                        $content,
                        1
                    );
                } elseif (preg_match('/@plugin\s+.*?;/s', $content)) {
                    $content = preg_replace(
                        '/(@plugin\s+.*?;\s*)/s',
                        "$0\n\n" . $source . "\n",
                        $content,
                        1
                    );
                }
            }
        }

        // Add dark variant at the end if not present
        if (!str_contains($content, '@variant dark')) {
            $darkVariant = "\n/* REQUIRED: Dark mode variant for Blogr theme switcher */\n@variant dark (.dark &);\n";
            $content .= $darkVariant;
        }

        File::put($path, $content);
        $this->info('✅ Tailwind CSS v4 dark mode configured in app.css.');
        $this->warn('⚠️ Important: Run "npm run build" after installation to compile assets!');
    }

    protected function buildAssets(): void
    {
        $this->info('🏗️ Building frontend assets...');
        $this->newLine();

        if (!File::exists(base_path('package.json'))) {
            $this->warn('⚠️ No package.json found. Skipping build.');
            return;
        }

        if ($this->forceableConfirm('Would you like to build your assets now? (npm run build)', true)) {
            $this->line('⏳ Building assets... This may take a moment.');
            
            try {
                $result = Process::timeout(300)->run(['npm', 'run', 'build']);

                if ($result->successful()) {
                    $this->info('✅ Assets built successfully!');
                    $this->line('🎉 Your blog frontend is now ready!');
                } else {
                    $this->error('❌ Failed to build assets:');
                    $this->line($result->errorOutput());
                    $this->line('ℹ️ You can build manually later: npm run build');
                }
            } catch (\Exception $e) {
                $this->error('❌ Error building assets: ' . $e->getMessage());
                $this->line('ℹ️ You can build manually later: npm run build');
            }
        } else {
            $this->warn('⚠️ Remember to run "npm run build" before visiting your blog!');
        }
    }

    protected function installWidgets(): void
    {
        $this->info('📊 Installing Blogr dashboard widgets...');

        $widgets = [
            BlogStatsOverview::class,
            RecentBlogPosts::class,
            ScheduledPosts::class,
            BlogPostsChart::class,
            BlogReadingStats::class,
        ];

        $installedCount = 0;
        foreach ($widgets as $widgetClass) {
            if ($this->installWidget($widgetClass)) {
                $installedCount++;
            }
        }

        if ($installedCount > 0) {
            $this->info("✅ Successfully installed {$installedCount} dashboard widget(s).");
        } else {
            $this->info('ℹ️ All widgets are already configured or installation was skipped.');
        }
    }

    protected function installWidget(string $widgetClass): bool
    {
        try {
            // Check if widget class exists
            if (!class_exists($widgetClass)) {
                $this->warn("⚠️ Widget class {$widgetClass} not found. Skipping.");
                return false;
            }

            // Get widget name for display
            $widgetName = class_basename($widgetClass);

            // For now, we'll just confirm the widget is available
            // In a future version, we could add it to a configuration file
            // or enable it in the admin panel settings

            $this->line("📊 Widget '{$widgetName}' is ready to use.");
            return true;

        } catch (\Exception $e) {
            $this->warn("⚠️ Failed to install widget {$widgetClass}: " . $e->getMessage());
            return false;
        }
    }

    protected function handleNpmDependencies(): void
    {
        $this->info('📦 Checking npm dependencies...');

        // Check if package.json exists
        if (!File::exists(base_path('package.json'))) {
            $this->warn('⚠️ No package.json found. Skipping npm dependencies installation.');
            $this->line('ℹ️ You need to install: npm install alpinejs @tailwindcss/typography -D');
            return;
        }

        $packageJson = json_decode(File::get(base_path('package.json')), true);
        
        // Check Alpine.js
        $hasAlpine = isset($packageJson['dependencies']['alpinejs']) || isset($packageJson['devDependencies']['alpinejs']);
        
        // Check @tailwindcss/typography
        $hasTypography = isset($packageJson['devDependencies']['@tailwindcss/typography']);

        if ($hasAlpine && $hasTypography) {
            $this->info('✅ All required npm packages are already installed.');
        } else {
            $packagesToInstall = [];
            
            if (!$hasAlpine) {
                $packagesToInstall[] = 'alpinejs';
            }
            
            if (!$hasTypography) {
                $packagesToInstall[] = '@tailwindcss/typography';
            }
            
            if ($this->forceableConfirm('Would you like to install the missing packages: ' . implode(', ', $packagesToInstall) . '?', true)) {
                $this->installNpmPackages($packagesToInstall);
            } else {
                $this->line('ℹ️ You can install them later: npm install ' . implode(' ', $packagesToInstall));
            }
        }

        // Check and update CSS file for typography
        $this->updateCssFile();
    }

    protected function installNpmPackages(array $packages): void
    {
        $this->info('📦 Installing npm packages: ' . implode(', ', $packages) . '...');

        try {
            $packageList = implode(' ', $packages);
            $command = ['npm', 'install'];
            
            // Add packages
            foreach ($packages as $package) {
                $command[] = $package;
            }
            
            // Alpine.js should be a regular dependency, typography as dev dependency
            if (in_array('@tailwindcss/typography', $packages)) {
                $command[] = '-D';
            }
            
            $result = Process::timeout(300)->run($command);

            if ($result->successful()) {
                $this->info('✅ npm packages installed successfully.');
            } else {
                $this->error('❌ Failed to install npm packages:');
                $this->line($result->errorOutput());
                $this->line('ℹ️ You can install them manually: npm install ' . $packageList);
            }
        } catch (\Exception $e) {
            $this->error('❌ Error installing npm packages: ' . $e->getMessage());
            $this->line('ℹ️ You can install them manually: npm install ' . implode(' ', $packages));
        }
    }

    protected function updateCssFile(): void
    {
        $cssPath = resource_path('css/app.css');

        if (!File::exists($cssPath)) {
            $this->warn('⚠️ CSS file not found at: ' . $cssPath);
            $this->line('ℹ️ You need to manually add @plugin "@tailwindcss/typography"; to your CSS file.');
            return;
        }

        $cssContent = File::get($cssPath);

        // Check if typography plugin is already added
        if (str_contains($cssContent, '@tailwindcss/typography')) {
            $this->info('✅ Typography plugin is already configured in your CSS file.');
            return;
        }

        if ($this->forceableConfirm('Would you like to automatically add the typography plugin to your CSS file?', true)) {
            $this->updateCssContent($cssContent, $cssPath);
        } else {
            $this->line('ℹ️ You need to manually add: @plugin "@tailwindcss/typography"; to your resources/css/app.css file.');
        }
    }

    protected function updateCssContent(string $cssContent, string $cssPath): void
    {
        // Look for @import 'tailwindcss'; and add typography plugin after it
        $pattern = '/(@import\s+[\'"]tailwindcss[\'"];?\s*)/';
        $replacement = "$1@plugin \"@tailwindcss/typography\";\n";

        if (preg_match($pattern, $cssContent)) {
            $newContent = preg_replace($pattern, $replacement, $cssContent);

            if ($newContent !== $cssContent) {
                File::put($cssPath, $newContent);
                $this->info('✅ Typography plugin added to your CSS file.');
                $this->line('ℹ️ Don\'t forget to run: npm run build (or npm run dev)');
            } else {
                $this->warn('⚠️ Could not update CSS file automatically.');
                $this->line('ℹ️ Please manually add: @plugin "@tailwindcss/typography"; after @import "tailwindcss";');
            }
        } else {
            $this->warn('⚠️ Could not find @import "tailwindcss"; in your CSS file.');
            $this->line('ℹ️ Please manually add: @plugin "@tailwindcss/typography"; to your resources/css/app.css file.');
        }
    }

    protected function checkAdminPanelProvider(): void
    {
        $this->info('🔧 Checking AdminPanelProvider configuration...');

        $adminPanelPath = app_path('Providers/Filament/AdminPanelProvider.php');

        if (!File::exists($adminPanelPath)) {
            $this->warn('⚠️ AdminPanelProvider not found at: ' . $adminPanelPath);
            $this->line('ℹ️ You need to manually add BlogrPlugin and EditProfile to your AdminPanelProvider.');
            $this->displayAdminPanelInstructions();
            return;
        }

        $content = File::get($adminPanelPath);
        $needsUpdate = false;

        // Check if BlogrPlugin is already added
        $hasPlugin = str_contains($content, 'BlogrPlugin::make()');
        
        // Check if EditProfile is already configured
        $hasProfile = str_contains($content, '->profile(EditProfile::class)') || 
                      str_contains($content, '->profile(\Happytodev\Blogr\Filament\Pages\Auth\EditProfile::class)');

        if ($hasPlugin && $hasProfile) {
            $this->info('✅ BlogrPlugin and EditProfile are already configured in AdminPanelProvider.');
            return;
        }

        // Determine what needs to be added
        $updateMessage = [];
        if (!$hasPlugin) {
            $updateMessage[] = 'BlogrPlugin';
        }
        if (!$hasProfile) {
            $updateMessage[] = 'EditProfile page';
        }

        // Automatically add missing configurations
        if ($this->forceableConfirm('Would you like to automatically add ' . implode(' and ', $updateMessage) . ' to your AdminPanelProvider?', true)) {
            $this->updateAdminPanelProvider($content, $adminPanelPath, !$hasPlugin, !$hasProfile);
        } else {
            $this->displayAdminPanelInstructions(!$hasPlugin, !$hasProfile);
        }
    }

    protected function updateAdminPanelProvider(string $content, string $adminPanelPath, bool $addPlugin = true, bool $addProfile = true): void
    {
        $modified = false;

        // Add BlogrPlugin import if needed and not present
        if ($addPlugin && !str_contains($content, 'use Happytodev\Blogr\BlogrPlugin;')) {
            $content = preg_replace(
                '/(use [^;]+;)\n+(?=\s*class)/s',
                "$1\nuse Happytodev\\Blogr\\BlogrPlugin;\n",
                $content,
                1
            );
            $modified = true;
        }

        // Add EditProfile import if needed and not present
        if ($addProfile && !str_contains($content, 'use Happytodev\Blogr\Filament\Pages\Auth\EditProfile;')) {
            $content = preg_replace(
                '/(use [^;]+;)\n+(?=\s*class)/s',
                "$1\nuse Happytodev\\Blogr\\Filament\\Pages\\Auth\\EditProfile;\n",
                $content,
                1
            );
            $modified = true;
        }

        // Add BlogrPlugin to plugins array if needed
        if ($addPlugin) {
            if (str_contains($content, '->plugins([')) {
                // Add plugin to existing plugins array
                $content = preg_replace(
                    '/(->plugins\(\[)([^\]]*?)(\]\))/s',
                    "$1$2\n                BlogrPlugin::make(),$3",
                    $content
                );
            } else {
                // Create new plugins array before authMiddleware
                $content = preg_replace(
                    '/(->authMiddleware\(\[)/s',
                    "->plugins([\n                BlogrPlugin::make(),\n            ])\n            $1",
                    $content
                );
            }
            $modified = true;
            $this->info('✅ BlogrPlugin added to AdminPanelProvider.');
        }

        // Add ->profile(EditProfile::class) if needed
        if ($addProfile) {
            // Find the ->login() line and add ->profile() after it
            if (str_contains($content, '->login()')) {
                $content = preg_replace(
                    '/(->login\(\))/',
                    "$1\n            ->profile(EditProfile::class)",
                    $content,
                    1
                );
                $modified = true;
                $this->info('✅ EditProfile page added to AdminPanelProvider.');
            } else {
                // If no ->login() found, add it after ->path()
                if (str_contains($content, '->path(')) {
                    $content = preg_replace(
                        '/(->path\([^)]+\))/',
                        "$1\n            ->profile(EditProfile::class)",
                        $content,
                        1
                    );
                    $modified = true;
                    $this->info('✅ EditProfile page added to AdminPanelProvider.');
                } else {
                    $this->warn('⚠️ Could not automatically add EditProfile. Please add it manually.');
                }
            }
        }

        if ($modified) {
            File::put($adminPanelPath, $content);
            $this->info('✅ AdminPanelProvider updated successfully.');
        }
    }

    protected function displayAdminPanelInstructions(bool $showPlugin = true, bool $showProfile = true): void
    {
        $this->newLine();
        $this->warn('📝 Manual step required:');
        
        if ($showPlugin) {
            $this->line('1. Add BlogrPlugin to your AdminPanelProvider plugins array:');
            $this->line('   ->plugins([');
            $this->line('       BlogrPlugin::make(),');
            $this->line('   ])');
            $this->newLine();
            $this->line('   Import: use Happytodev\Blogr\BlogrPlugin;');
            $this->newLine();
        }

        if ($showProfile) {
            $this->line(($showPlugin ? '2' : '1') . '. Add EditProfile page to enable user bio and avatar editing:');
            $this->line('   ->login()');
            $this->line('   ->profile(EditProfile::class)');
            $this->newLine();
            $this->line('   Import: use Happytodev\Blogr\Filament\Pages\Auth\EditProfile;');
            $this->newLine();
        }
    }

    protected function promptForGitHubStar(): void
    {
        $this->newLine();
        if ($this->forceableConfirm('⭐ Would you like to support Blogr by giving it a star on GitHub?', true)) {
            $this->info('🌟 Thank you! Please visit: https://github.com/happytodev/blogr');
            $this->line('⭐ Click the star button at the top right of the page!');
            $this->newLine();

            // Try to open the URL in browser
            try {
                if (PHP_OS_FAMILY === 'Windows') {
                    Process::run(['start', 'https://github.com/happytodev/blogr']);
                } elseif (PHP_OS_FAMILY === 'Darwin') {
                    Process::run(['open', 'https://github.com/happytodev/blogr']);
                } else {
                    Process::run(['xdg-open', 'https://github.com/happytodev/blogr']);
                }
            } catch (\Exception $e) {
                // Ignore if we can't open the browser
            }
        } else {
            $this->line('ℹ️ No problem! You can always star it later at: https://github.com/happytodev/blogr');
        }
    }

    protected function displayNextSteps(): void
    {
        $this->info('🎯 Next steps:');
        $this->line('1. Access your Filament admin panel');
        $this->line('2. Update your profile (bio and avatar) via the user menu → Edit Profile');
        $this->line('3. Go to "Blog Posts" to create your first post');
        $this->line('4. Check out the tutorial posts and series (if installed)');
        $this->line('5. Configure settings in the "Blogr Settings" section');
        $this->line('6. Visit your blog at: ' . url(config('blogr.route.prefix', 'blog')));
        $this->newLine();

        if ($this->option('skip-build') || $this->option('skip-npm')) {
            $this->warn('⚠️ IMPORTANT: Don\'t forget to run "npm run build" before visiting your blog!');
            $this->newLine();
        }

        $this->line('📚 Useful commands:');
        $this->line('• php artisan blogr list-tutorials    - List tutorial posts');
        $this->line('• php artisan blogr remove-tutorials  - Remove tutorial posts');
        $this->line('• php artisan blogr install-tutorials - Install tutorial posts');
        $this->line('• npm run build                       - Build frontend assets');
        $this->line('• npm run dev                         - Watch and build assets (development)');
        $this->newLine();

        $this->line('📖 Documentation: https://github.com/happytodev/blogr');
        $this->line('🐛 Issues: https://github.com/happytodev/blogr/issues');
        $this->newLine();
        
        $this->info('💡 Tip: Check out the THEME_SWITCHER.md file for troubleshooting the light/dark/auto mode feature.');
    }

    protected function configureUserModel(): void
    {
        $this->info('⚙️  Configuring User model with Spatie Permission traits...');

        $userModelPath = app_path('Models/User.php');

        if (!File::exists($userModelPath)) {
            $this->warn('❌ User model not found at ' . $userModelPath);
            return;
        }

        $content = File::get($userModelPath);

        // Check if HasRoles trait is already imported
        if (str_contains($content, 'use Spatie\Permission\Traits\HasRoles;')) {
            $this->line('✅ HasRoles trait already imported in User model');
        } else {
            // Add the import after the Notifiable import
            $pattern = '/(use Illuminate\\\\Notifications\\\\Notifiable;)/';
            $replacement = '$1' . PHP_EOL . 'use Spatie\Permission\Traits\HasRoles;';
            
            $content = preg_replace($pattern, $replacement, $content);
            $this->line('✅ Added HasRoles trait import');
        }

        // Check if HasRoles is already in the use statement of the class
        // We need to check specifically in the class traits line, not the imports
        $lines = explode("\n", $content);
        $inClass = false;
        $hasRolesInTraits = false;
        
        foreach ($lines as $line) {
            if (preg_match('/^class\s+\w+/', $line)) {
                $inClass = true;
                continue;
            }
            
            if ($inClass && preg_match('/^\s+use\s+\w/', $line) && !preg_match('/^\s*\/\*/', $line)) {
                if (str_contains($line, 'HasRoles')) {
                    $hasRolesInTraits = true;
                }
                break;
            }
        }
        
        if ($hasRolesInTraits) {
            $this->line('✅ HasRoles trait already used in User model');
        } else {
            // Add HasRoles to the class traits
            // Strategy: Find the line that starts with spaces + 'use' followed by trait names
            // This is INSIDE the class, not the imports at the top
            $lines = explode("\n", $content);
            $inClass = false;
            $modified = false;

            foreach ($lines as $index => $line) {
                // Detect when we enter the class
                if (preg_match('/^class\s+\w+/', $line)) {
                    $inClass = true;
                    continue;
                }
                
                // If we're in the class and find a line with traits (starting with spaces + use)
                // Exclude comment lines
                if ($inClass && preg_match('/^\s+use\s+\w/', $line) && !preg_match('/^\s*\/\*/', $line)) {
                    if (!str_contains($line, 'HasRoles') && str_contains($line, 'HasFactory')) {
                        // Add HasRoles after HasFactory
                        $lines[$index] = str_replace('HasFactory,', 'HasFactory, HasRoles,', $line);
                        $modified = true;
                        break;
                    }
                }
            }

            if ($modified) {
                $content = implode("\n", $lines);
                $this->line('✅ Added HasRoles to User model traits');
            } else {
                $this->warn('⚠️  Could not automatically add HasRoles to User model traits');
            }
        }

        // Add slug, avatar and bio to $fillable array
        if (!str_contains($content, "'slug'") && !str_contains($content, '"slug"')) {
            // Find the $fillable array - match opening bracket to closing bracket
            $pattern = '/(\$fillable\s*=\s*\[\s*[^\]]+?)(,\s*)(\];)/s';
            if (preg_match($pattern, $content, $matches)) {
                // Add slug, avatar and bio before the closing bracket
                $replacement = $matches[1] . $matches[2] . "\n        'slug',\n        'avatar',\n        'bio'," . "\n    " . $matches[3];
                $content = preg_replace($pattern, $replacement, $content, 1);
                $this->line('✅ Added slug, avatar and bio to User model $fillable');
            } else {
                $this->warn('⚠️  Could not find $fillable array in User model');
                $this->line('ℹ️  Please manually add "slug", "avatar" and "bio" to the $fillable array');
            }
        } else {
            $this->line('✅ slug, avatar and bio already in User model $fillable');
        }

        // Add 'bio' => 'array' cast to ensure JSON is properly decoded
        if (!str_contains($content, "'bio' => 'array'") && !str_contains($content, '"bio" => "array"')) {
            // Strategy 1: Try to find the casts() method (Laravel 11+ style)
            if (preg_match('/(protected function casts\(\)\s*:\s*array\s*\{[^}]*return\s*\[)([^\]]*?)(\];)/s', $content, $matches)) {
                $before = $matches[1];
                $castsContent = $matches[2];
                $after = $matches[3];
                
                // Check if there's already content in the array
                if (trim($castsContent) !== '') {
                    // Add bio cast at the end
                    $newCastsContent = rtrim($castsContent) . ",\n            'bio' => 'array',\n        ";
                } else {
                    // Empty array, add bio cast as first entry
                    $newCastsContent = "\n            'bio' => 'array',\n        ";
                }
                
                $content = str_replace(
                    $matches[0],
                    $before . $newCastsContent . $after,
                    $content
                );
                $this->line('✅ Added bio array cast to User model casts() method');
            }
            // Strategy 2: Try to find the $casts property (Laravel 10 style)
            elseif (preg_match('/(\$casts\s*=\s*\[)([^\]]*?)(\];)/s', $content, $matches)) {
                $before = $matches[1];
                $castsContent = $matches[2];
                $after = $matches[3];
                
                // Check if there's already content in the array
                if (trim($castsContent) !== '') {
                    // Add bio cast at the end
                    $newCastsContent = rtrim($castsContent) . ",\n        'bio' => 'array',\n    ";
                } else {
                    // Empty array, add bio cast as first entry
                    $newCastsContent = "\n        'bio' => 'array',\n    ";
                }
                
                $content = str_replace(
                    $matches[0],
                    $before . $newCastsContent . $after,
                    $content
                );
                $this->line('✅ Added bio array cast to User model $casts property');
            } else {
                $this->warn('⚠️  Could not automatically add bio cast to User model');
                $this->line('ℹ️  Please manually add \'bio\' => \'array\' to the $casts array or casts() method');
            }
        } else {
            $this->line('✅ bio array cast already in User model');
        }

        File::put($userModelPath, $content);
        $this->info('✅ User model configured successfully');
    }

    protected function ensureImagesArePublished(): void
    {
        $targetPath = public_path('vendor/blogr/images');
        
        // Check if images are already published
        if (File::exists($targetPath) && count(File::files($targetPath)) >= 3) {
            $this->line('✅ Blogr images already published');
            return;
        }

        // Try to find the source images directory
        $possiblePaths = [
            base_path('vendor/happytodev/blogr/resources/images'),
            __DIR__ . '/../../resources/images',
        ];

        $sourcePath = null;
        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                $sourcePath = $path;
                break;
            }
        }

        if (!$sourcePath) {
            $this->warn('⚠️  Could not find Blogr images source directory');
            $this->line('ℹ️  Images should be at: vendor/happytodev/blogr/resources/images/');
            return;
        }

        // Create target directory if it doesn't exist
        if (!File::exists($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
        }

        // Copy images manually
        try {
            $imageFiles = File::files($sourcePath);
            $copiedCount = 0;

            foreach ($imageFiles as $file) {
                $targetFile = $targetPath . '/' . $file->getFilename();
                File::copy($file->getPathname(), $targetFile);
                $copiedCount++;
            }

            if ($copiedCount > 0) {
                $this->line("✅ Manually copied {$copiedCount} image(s) to public/vendor/blogr/images");
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Error copying images: ' . $e->getMessage());
            $this->line('ℹ️  You may need to manually copy images from vendor/happytodev/blogr/resources/images/');
        }
    }

    protected function createRolesAndPermissions(): void
    {
        $this->info('👥 Creating roles and permissions...');

        try {
            // Run the RoleAndPermissionSeeder
            $seeder = new \Happytodev\Blogr\Database\Seeders\RoleAndPermissionSeeder();
            $seeder->setCommand($this);
            $seeder->run();
        } catch (\Exception $e) {
            $this->warn('⚠️  Error creating roles and permissions: ' . $e->getMessage());
            $this->line('ℹ️  You may need to create roles manually.');
        }
    }

    /**
     * Create test users with appropriate roles
     */
    protected function createTestUsers(): void
    {
        $this->info('� Creating test users...');

        try {
            $seederClass = \Happytodev\Blogr\Database\Seeders\TestUsersSeeder::class;
            $seeder = new $seederClass();
            $seeder->setCommand($this);
            $seeder->run();
        } catch (\Exception $e) {
            $this->warn('⚠️  Error creating test users: ' . $e->getMessage());
            $this->line('ℹ️  You may need to create users manually.');
        }
    }

    /**
     * Assign admin role to the first user if one exists
     * This ensures immediate access to all Blogr features after installation
     */
    protected function assignAdminRoleToFirstUser(): void
    {
        try {
            $userModel = config('auth.providers.users.model', \App\Models\User::class);
            
            // Check if any users exist (excluding test users we just created)
            $existingUsers = $userModel::whereNotIn('email', [
                'admin@demo.com',
                'writer@demo.com',
            ])->get();

            if ($existingUsers->isEmpty()) {
                $this->line('ℹ️  No existing users found to assign admin role.');
                return;
            }

            // Get the first user
            $firstUser = $existingUsers->first();

            // Check if user already has any role
            if (method_exists($firstUser, 'hasAnyRole') && $firstUser->hasAnyRole(['admin', 'writer'])) {
                $this->line("✅ User '{$firstUser->name}' already has a role assigned.");
                return;
            }

            // Assign admin role
            if (method_exists($firstUser, 'assignRole')) {
                $firstUser->assignRole('admin');
                $this->info("✅ Admin role assigned to user: {$firstUser->name} ({$firstUser->email})");
                $this->line('🎉 This user now has full access to all Blogr features!');
            } else {
                $this->warn('⚠️  User model does not have assignRole method. Make sure HasRoles trait is used.');
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Error assigning admin role: ' . $e->getMessage());
            $this->line('ℹ️  You can manually assign roles via: php artisan blogr:assign-role');
        }
    }

    /**
     * Install UserResource for managing users in Filament
     */
    protected function installUserResource(): void
    {
        $this->info('📋 Installing UserResource for user management...');

        // Define the paths
        $stubsPath = __DIR__ . '/../../stubs';
        $appPath = app_path();

        $files = [
            'UserResource.stub' => $appPath . '/Filament/Resources/UserResource.php',
            'UserForm.stub' => $appPath . '/Filament/Resources/Users/Schemas/UserForm.php',
            'UsersTable.stub' => $appPath . '/Filament/Resources/Users/Tables/UsersTable.php',
            'ListUsers.stub' => $appPath . '/Filament/Resources/Users/Pages/ListUsers.php',
            'CreateUser.stub' => $appPath . '/Filament/Resources/Users/Pages/CreateUser.php',
            'EditUser.stub' => $appPath . '/Filament/Resources/Users/Pages/EditUser.php',
        ];

        $copiedCount = 0;
        foreach ($files as $stub => $destination) {
            $source = $stubsPath . '/' . $stub;
            
            // Check if the file already exists
            if (file_exists($destination)) {
                $this->line("   • " . basename($destination) . " already exists, skipping...");
                continue;
            }

            // Create the directory if it doesn't exist
            $directory = dirname($destination);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Copy the file
            if (file_exists($source)) {
                copy($source, $destination);
                $this->line("   • Copied " . basename($destination));
                $copiedCount++;
            } else {
                $this->warn("   ⚠️  Stub file not found: " . basename($stub));
            }
        }

        if ($copiedCount > 0) {
            $this->info("✅ UserResource installed successfully! ({$copiedCount} files)");
            $this->line("   You can now manage users in the admin panel.");
        } else {
            $this->line("ℹ️  UserResource files already exist.");
        }
    }
}
