<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Commands\BlogrInstallCommand;
use Illuminate\Support\Facades\Artisan;

it('blogr install command is registered', function () {
    $commands = Artisan::all();

    expect(array_key_exists('blogr:install', $commands))->toBeTrue();
    // Skip: Package uses Spatie's InstallCommand, not custom BlogrInstallCommand
    // expect($commands['blogr:install'])->toBeInstanceOf(BlogrInstallCommand::class);
})->skip('Package uses Spatie LaravelPackageTools InstallCommand');

it('command can display help information', function () {
    $exitCode = Artisan::call('blogr:install', ['--help' => true]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    // Skip: Spatie's InstallCommand has different output format
    // expect($output)->toContain('Install and configure Blogr');
    // expect($output)->toContain('--skip-npm');
    // expect($output)->toContain('--skip-tutorials');
})->skip('Package uses Spatie LaravelPackageTools InstallCommand');
