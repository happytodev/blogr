<?php

use Happytodev\Blogr\Commands\BlogrInstallCommand;
use Illuminate\Support\Facades\Artisan;

it('blogr install command is registered', function () {
    $commands = Artisan::all();

    expect(array_key_exists('blogr:install', $commands))->toBeTrue();
    expect($commands['blogr:install'])->toBeInstanceOf(BlogrInstallCommand::class);
});

it('command can display help information', function () {
    $exitCode = Artisan::call('blogr:install', ['--help' => true]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('Install and configure Blogr');
    expect($output)->toContain('--skip-npm');
    expect($output)->toContain('--skip-tutorials');
});
