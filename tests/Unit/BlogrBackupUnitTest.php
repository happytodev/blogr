<?php

use Orchestra\Testbench\TestCase as Orchestra;
use Happytodev\Blogr\Services\BackupInstallationChecker;
use Happytodev\Blogr\Services\BackupInstaller;
use Happytodev\Blogr\Commands\BlogrInstallCommand;

class BlogrBackupUnitTest extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            // Don't include BlogrServiceProvider to avoid migrations
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        $app['config']->set('app.key', 'base64:/QGZSf6gflmQp4zukiY3ab0DnTFMOqLK1//pgpQhFzw=');

        // Configure database for testing (but don't load migrations)
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    // Override to not load migrations
    protected function defineDatabaseMigrations()
    {
        // Don't load any migrations for unit tests
    }

    public function test_backup_installation_checker_can_be_instantiated()
    {
        $checker = new BackupInstallationChecker();
        $this->assertInstanceOf(BackupInstallationChecker::class, $checker);
    }

    public function test_backup_installer_can_be_instantiated()
    {
        $installer = new BackupInstaller();
        $this->assertInstanceOf(BackupInstaller::class, $installer);
    }

    public function test_backup_installation_checker_has_required_methods()
    {
        $checker = new BackupInstallationChecker();

        $this->assertTrue(method_exists($checker, 'isInstalled'));
        $this->assertTrue(method_exists($checker, 'isConfigPublished'));
        $this->assertTrue(method_exists($checker, 'getInstalledVersion'));
    }

    public function test_backup_installer_has_required_methods()
    {
        $installer = new BackupInstaller();

        $this->assertTrue(method_exists($installer, 'canInstall'));
        $this->assertTrue(method_exists($installer, 'install'));
        $this->assertTrue(method_exists($installer, 'publishConfig'));
        $this->assertTrue(method_exists($installer, 'configureForBlogr'));
    }

    public function test_blogr_install_command_has_setupBackupSystem_method()
    {
        $command = new BlogrInstallCommand();
        $this->assertTrue(method_exists($command, 'setupBackupSystem'));
    }
}
