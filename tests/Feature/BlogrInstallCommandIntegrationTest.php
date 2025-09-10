<?php

use Happytodev\Blogr\Commands\BlogrInstallCommand;
use Happytodev\Blogr\Filament\Widgets\BlogStatsOverview;
use Happytodev\Blogr\Filament\Widgets\RecentBlogPosts;
use Happytodev\Blogr\Filament\Widgets\ScheduledPosts;
use Happytodev\Blogr\Filament\Widgets\BlogPostsChart;
use Happytodev\Blogr\Filament\Widgets\BlogReadingStats;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

uses(RefreshDatabase::class);

describe('BlogrInstallCommand Integration Tests', function () {

    beforeEach(function () {
        // Mock Process facade for all tests to avoid actual system calls
        Process::shouldReceive('run')->andReturn(new class {
            public function successful() { return true; }
            public function errorOutput() { return ''; }
        });
    });

    it('installs all dashboard widgets correctly', function () {
        // Run the install command
        Artisan::call('blogr:install', [
            '--skip-npm' => true,
            '--skip-tutorials' => true,
        ]);

        // Verify that all widget classes are available
        expect(class_exists(BlogStatsOverview::class))->toBeTrue();
        expect(class_exists(RecentBlogPosts::class))->toBeTrue();
        expect(class_exists(ScheduledPosts::class))->toBeTrue();
        expect(class_exists(BlogPostsChart::class))->toBeTrue();
        expect(class_exists(BlogReadingStats::class))->toBeTrue();

        // Verify widgets can be instantiated
        $widgets = [
            new BlogStatsOverview(),
            new RecentBlogPosts(),
            new ScheduledPosts(),
            new BlogPostsChart(),
            new BlogReadingStats(),
        ];

        foreach ($widgets as $widget) {
            expect($widget)->toBeObject();
        }

        // Verify output mentions widget installation
        $output = Artisan::output();
        expect($output)->toContain('📊 Installing Blogr dashboard widgets');
        expect($output)->toContain('✅ Successfully installed 5 dashboard widget(s)');
    });

    it('creates tutorial content with proper structure', function () {
        // Run the install command without skipping tutorials
        Artisan::call('blogr:install', [
            '--skip-npm' => true,
        ]);

        // Verify tutorial category exists
        $tutorialCategory = Category::where('slug', 'blogr-tutorial')->first();
        expect($tutorialCategory)->not->toBeNull();
        expect($tutorialCategory->name)->toBe('Blogr Tutorial');
        expect($tutorialCategory->is_active)->toBeTrue();

        // Verify all 7 tutorial posts exist
        $tutorialPosts = BlogPost::where('category_id', $tutorialCategory->id)->get();
        expect($tutorialPosts->count())->toBe(7);

        // Verify each tutorial post has required fields
        foreach ($tutorialPosts as $post) {
            expect($post->title)->not->toBeEmpty();
            expect($post->slug)->not->toBeEmpty();
            expect($post->content)->not->toBeEmpty();
            expect($post->is_published)->toBeTrue();
            expect($post->published_at)->not->toBeNull();
        }

        // Verify specific tutorial titles exist
        $expectedTitles = [
            'Welcome to Blogr - Getting Started',
            'Installing and Configuring Blogr',
            'Creating Your First Blog Post',
            'Understanding Blogr Widgets',
            'Blogr Settings and Configuration',
            'SEO Optimization with Blogr',
            'Advanced Features and Customization',
        ];

        $actualTitles = $tutorialPosts->pluck('title')->toArray();
        foreach ($expectedTitles as $expectedTitle) {
            expect($actualTitles)->toContain($expectedTitle);
        }
    });

    it('publishes all required files and directories', function () {
        // Clean up any existing files first
        $configPath = config_path('blogr.php');
        $viewPath = resource_path('views/vendor/blogr');
        $migrationPath = database_path('migrations');

        if (File::exists($configPath)) {
            File::delete($configPath);
        }
        if (File::exists($viewPath)) {
            File::deleteDirectory($viewPath);
        }

        // Run the install command
        Artisan::call('blogr:install', [
            '--skip-npm' => true,
            '--skip-tutorials' => true,
        ]);

        // Verify configuration file was published
        expect(File::exists($configPath))->toBeTrue();

        // Verify views directory was published
        expect(File::exists($viewPath))->toBeTrue();

        // Verify migration files exist
        $migrationFiles = File::files($migrationPath);
        $blogrMigrations = array_filter($migrationFiles, function ($file) {
            return str_contains($file->getFilename(), 'create_blogr');
        });
        expect(count($blogrMigrations))->toBeGreaterThan(0);

        // Verify output mentions file publishing
        $output = Artisan::output();
        expect($output)->toContain('📦 Publishing Blogr configuration and migration files');
        expect($output)->toContain('✅ Configuration and migration files published successfully');
    });

    it('handles npm installation workflow correctly', function () {
        // Test with package.json present
        File::shouldReceive('exists')
            ->with(base_path('package.json'))
            ->andReturn(true);

        File::shouldReceive('get')
            ->with(base_path('package.json'))
            ->andReturn('{"devDependencies": {"@tailwindcss/typography": "^0.5.0"}}');

        // Run the install command without skipping npm
        Artisan::call('blogr:install', [
            '--skip-tutorials' => true,
        ]);

        $output = Artisan::output();
        expect($output)->toContain('📦 Checking npm dependencies');
        expect($output)->toContain('✅ @tailwindcss/typography is already installed');
    });

    it('provides helpful error messages and next steps', function () {
        // Run the install command
        Artisan::call('blogr:install', [
            '--skip-npm' => true,
            '--skip-tutorials' => true,
        ]);

        $output = Artisan::output();

        // Verify welcome message
        expect($output)->toContain('🚀 Welcome to Blogr Installation!');
        expect($output)->toContain('This command will help you set up Blogr automatically.');

        // Verify success message
        expect($output)->toContain('✅ Blogr installation completed successfully!');
        expect($output)->toContain('🎉 You can now start creating amazing blog content!');

        // Verify next steps
        expect($output)->toContain('Next steps:');
        expect($output)->toContain('1. Access your Filament admin panel');
        expect($output)->toContain('2. Go to "Blog Posts" to create your first post');
        expect($output)->toContain('3. Check out the tutorial posts (if installed)');
        expect($output)->toContain('4. Configure settings in the "Blogr Settings" section');
        expect($output)->toContain('5. Visit your blog at: http://blogr-test-with-install.test/blog');

        // Verify useful commands
        expect($output)->toContain('Useful commands:');
        expect($output)->toContain('php artisan blogr list-tutorials');
        expect($output)->toContain('php artisan blogr remove-tutorials');
        expect($output)->toContain('php artisan blogr install-tutorials');
    });

    it('gracefully handles missing package.json', function () {
        // Mock File facade to simulate missing package.json
        File::shouldReceive('exists')
            ->with(base_path('package.json'))
            ->andReturn(false);

        // Run the install command without skipping npm
        Artisan::call('blogr:install', [
            '--skip-tutorials' => true,
        ]);

        $output = Artisan::output();
        expect($output)->toContain('⚠️ No package.json found. Skipping npm dependencies installation.');
        expect($output)->toContain('ℹ️ You can install @tailwindcss/typography manually');
    });

    it('validates tutorial content integrity', function () {
        // Run the install command
        Artisan::call('blogr:install', [
            '--skip-npm' => true,
        ]);

        $tutorialCategory = Category::where('slug', 'blogr-tutorial')->first();
        $tutorialPosts = BlogPost::where('category_id', $tutorialCategory->id)->get();

        // Verify each post has SEO metadata
        foreach ($tutorialPosts as $post) {
            expect($post->meta_title)->not->toBeEmpty();
            expect($post->meta_description)->not->toBeEmpty();
            expect($post->tldr)->not->toBeEmpty();
        }

        // Verify posts have unique slugs
        $slugs = $tutorialPosts->pluck('slug')->toArray();
        expect(count($slugs))->toBe(count(array_unique($slugs)));

        // Verify posts are properly categorized
        foreach ($tutorialPosts as $post) {
            expect($post->category_id)->toBe($tutorialCategory->id);
        }
    });

    it('maintains idempotency across multiple runs', function () {
        // Run the install command multiple times
        for ($i = 0; $i < 3; $i++) {
            Artisan::call('blogr:install', [
                '--skip-npm' => true,
                '--skip-tutorials' => true,
            ]);
        }

        // Verify no duplicate data is created
        $tutorialCategories = Category::where('slug', 'blogr-tutorial')->get();
        expect($tutorialCategories->count())->toBeLessThanOrEqual(1);

        // Verify command succeeds each time
        expect(Artisan::output())->toContain('✅ Blogr installation completed successfully!');
    });

});
