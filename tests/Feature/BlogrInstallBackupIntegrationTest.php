<?php

use Happytodev\Blogr\Services\BackupInstallationChecker;
use Happytodev\Blogr\Services\BackupInstaller;
use Happytodev\Blogr\Commands\BlogrInstallCommand;

it('backup installation checker can be instantiated', function () {
    $checker = new BackupInstallationChecker();
    expect($checker)->toBeInstanceOf(BackupInstallationChecker::class);
});

it('backup installer can be instantiated', function () {
    $installer = new BackupInstaller();
    expect($installer)->toBeInstanceOf(BackupInstaller::class);
});

it('backup installation checker has required methods', function () {
    $checker = new BackupInstallationChecker();

    expect(method_exists($checker, 'isInstalled'))->toBeTrue();
    expect(method_exists($checker, 'isConfigPublished'))->toBeTrue();
    expect(method_exists($checker, 'getInstalledVersion'))->toBeTrue();
});

it('backup installer has required methods', function () {
    $installer = new BackupInstaller();

    expect(method_exists($installer, 'canInstall'))->toBeTrue();
    expect(method_exists($installer, 'install'))->toBeTrue();
    expect(method_exists($installer, 'publishConfig'))->toBeTrue();
    expect(method_exists($installer, 'configureForBlogr'))->toBeTrue();
});

it('blogr install command has setupBackupSystem method', function () {
    $command = new BlogrInstallCommand();
    expect(method_exists($command, 'setupBackupSystem'))->toBeTrue();
});
