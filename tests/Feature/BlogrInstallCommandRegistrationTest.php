<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

use Illuminate\Support\Facades\Artisan;

it('blogr install command is registered', function () {
    $commands = Artisan::all();

    expect($commands)->toHaveKey('blogr:install');
});

it('command can display help information', function () {
    $exitCode = Artisan::call('blogr:install', ['--help' => true]);

    expect($exitCode)->toBe(0);
});
