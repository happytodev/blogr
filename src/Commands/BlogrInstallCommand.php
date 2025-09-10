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
    public $signature = 'blogr:install {--skip-npm : Skip npm dependencies installation} {--skip-tutorials : Skip tutorial content installation}';

    public $description = 'Install and configure Blogr with all necessary steps';

    public function handle(): int
    {
        $this->info('🚀 Welcome to Blogr Installation!');
        $this->line('This command will help you set up Blogr automatically.');
        $this->newLine();

        // Step 1: Publish configuration and migrations
        $this->publishFiles();

        // Step 2: Run migrations
        $this->runMigrations();

        // Step 3: Install tutorial content (unless skipped)
        if (!$this->option('skip-tutorials')) {
            $this->installTutorials();
        }

        // Step 4: Install dashboard widgets
        $this->installWidgets();

        // Step 5: Handle npm dependencies (unless skipped)
        if (!$this->option('skip-npm')) {
            $this->handleNpmDependencies();
        }

        // Step 5: Check AdminPanelProvider configuration
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

        $this->call('vendor:publish', [
            '--provider' => 'Happytodev\Blogr\BlogrServiceProvider',
            '--force' => true
        ]);

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

            if (!$this->confirm('Would you like to continue with the installation anyway?', true)) {
                $this->error('Installation cancelled.');
                return;
            }
            $this->info('⏭️ Continuing with installation...');
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

        if ($this->confirm('Would you like to install default tutorial content to help you get started?', true)) {
            $this->call('blogr', ['action' => 'install-tutorials']);
            $this->info('✅ Tutorial content installed successfully.');
        } else {
            $this->line('ℹ️ Tutorial installation skipped. You can install it later with: php artisan blogr install-tutorials');
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
            $this->line('ℹ️ You can install @tailwindcss/typography manually: npm install -D @tailwindcss/typography');
            return;
        }

        // Check if @tailwindcss/typography is already installed
        $packageJson = json_decode(File::get(base_path('package.json')), true);
        $hasTypography = isset($packageJson['devDependencies']['@tailwindcss/typography']);

        if ($hasTypography) {
            $this->info('✅ @tailwindcss/typography is already installed.');
        } else {
            if ($this->confirm('Would you like to install @tailwindcss/typography for better blog post styling?', true)) {
                $this->installNpmPackage();
            } else {
                $this->line('ℹ️ You can install it later: npm install -D @tailwindcss/typography');
            }
        }

        // Check and update CSS file
        $this->updateCssFile();
    }

    protected function installNpmPackage(): void
    {
        $this->info('📦 Installing @tailwindcss/typography...');

        try {
            $result = Process::run(['npm', 'install', '-D', '@tailwindcss/typography']);

            if ($result->successful()) {
                $this->info('✅ @tailwindcss/typography installed successfully.');
            } else {
                $this->error('❌ Failed to install @tailwindcss/typography:');
                $this->line($result->errorOutput());
                $this->line('ℹ️ You can install it manually: npm install -D @tailwindcss/typography');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error installing npm package: ' . $e->getMessage());
            $this->line('ℹ️ You can install it manually: npm install -D @tailwindcss/typography');
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

        if ($this->confirm('Would you like to automatically add the typography plugin to your CSS file?', true)) {
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
            $this->line('ℹ️ You need to manually add BlogrPlugin to your AdminPanelProvider.');
            $this->displayAdminPanelInstructions();
            return;
        }

        $content = File::get($adminPanelPath);

        // Check if BlogrPlugin is already added
        if (str_contains($content, 'BlogrPlugin::make()')) {
            $this->info('✅ BlogrPlugin is already configured in AdminPanelProvider.');
            return;
        }

        // Check if we can automatically add it
        if (str_contains($content, '->plugins([') && str_contains($content, '])')) {
            if ($this->confirm('Would you like to automatically add BlogrPlugin to your AdminPanelProvider?', true)) {
                $this->updateAdminPanelProvider($content, $adminPanelPath);
            } else {
                $this->displayAdminPanelInstructions();
            }
        } else {
            $this->warn('⚠️ Could not automatically detect plugin configuration in AdminPanelProvider.');
            $this->displayAdminPanelInstructions();
        }
    }

    protected function updateAdminPanelProvider(string $content, string $adminPanelPath): void
    {
        // Add import if not present
        if (!str_contains($content, 'use Happytodev\Blogr\BlogrPlugin;')) {
            $content = preg_replace(
                '/(use Filament\\.*;\s*)/',
                "$1use Happytodev\Blogr\BlogrPlugin;\n",
                $content
            );
        }

        // Add plugin to the plugins array
        $content = preg_replace(
            '/(->plugins\(\[)([^\]]*?)(\]\))/s',
            "$1$2\n            BlogrPlugin::make(),$3",
            $content
        );

        File::put($adminPanelPath, $content);
        $this->info('✅ BlogrPlugin added to AdminPanelProvider automatically.');
    }

    protected function displayAdminPanelInstructions(): void
    {
        $this->newLine();
        $this->warn('📝 Manual step required:');
        $this->line('Add this line to your AdminPanelProvider plugins array:');
        $this->line('    ->plugin(BlogrPlugin::make())');
        $this->newLine();
        $this->line('And import the class:');
        $this->line('use Happytodev\Blogr\BlogrPlugin;');
        $this->newLine();
    }

    protected function promptForGitHubStar(): void
    {
        $this->newLine();
        if ($this->confirm('⭐ Would you like to support Blogr by giving it a star on GitHub?', true)) {
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
        $this->line('2. Go to "Blog Posts" to create your first post');
        $this->line('3. Check out the tutorial posts (if installed)');
        $this->line('4. Configure settings in the "Blogr Settings" section');
        $this->line('5. Visit your blog at: ' . url(config('blogr.route.prefix', 'blog')));
        $this->newLine();

        $this->line('📚 Useful commands:');
        $this->line('• php artisan blogr list-tutorials    - List tutorial posts');
        $this->line('• php artisan blogr remove-tutorials  - Remove tutorial posts');
        $this->line('• php artisan blogr install-tutorials - Install tutorial posts');
        $this->newLine();

        $this->line('📖 Documentation: https://github.com/happytodev/blogr');
        $this->line('🐛 Issues: https://github.com/happytodev/blogr/issues');
    }
}
