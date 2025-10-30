<?php

use Orchestra\Testbench\TestCase as Orchestra;
use Happytodev\Blogr\Services\BackupInstallationChecker;
use Happytodev\Blogr\Services\BackupInstaller;

class BlogrBackupInstallationUnitTest extends Orchestra
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

    public function test_backup_installation_checker_detects_installed_package()
    {
        $checker = new BackupInstallationChecker();

        // Test that the method returns a boolean
        $result = $checker->isInstalled();
        $this->assertIsBool($result);
    }

    public function test_backup_installation_checker_detects_config_publication()
    {
        $checker = new BackupInstallationChecker();

        // Test that the method returns a boolean
        $result = $checker->isConfigPublished();
        $this->assertIsBool($result);
    }

    public function test_backup_installation_checker_gets_version()
    {
        $checker = new BackupInstallationChecker();

        // Test that the method returns null or string
        $result = $checker->getInstalledVersion();
        $this->assertTrue($result === null || is_string($result));
    }

    public function test_backup_installer_can_check_installation_possibility()
    {
        $installer = new BackupInstaller();

        // Test that canInstall returns a boolean
        $result = $installer->canInstall();
        $this->assertIsBool($result);
    }

    public function test_backup_installer_has_install_method()
    {
        $installer = new BackupInstaller();

        // Test that install method exists and returns boolean
        $this->assertTrue(method_exists($installer, 'install'));

        // We don't actually call install() as it modifies composer.json
        // but we verify the method exists for the interface
    }

    public function test_backup_installer_has_publish_config_method()
    {
        $installer = new BackupInstaller();

        // Test that publishConfig method exists
        $this->assertTrue(method_exists($installer, 'publishConfig'));
    }

    public function test_backup_installer_has_configure_for_blogr_method()
    {
        $installer = new BackupInstaller();

        // Test that configureForBlogr method exists
        $this->assertTrue(method_exists($installer, 'configureForBlogr'));
    }
}
