<?php

use Happytodev\Blogr\Commands\BlogrInstallShikiCommand;

function resolveShikiBinDirFor(?string $appBasePath = null): ?string
{
    $command = new BlogrInstallShikiCommand;

    return $command->resolveShikiBinDir($appBasePath);
}

test('install-shiki prefers the app vendor shiki bin dir when it exists', function () {
    $root = sys_get_temp_dir().'/blogr-338-app-'.uniqid();
    $bin = $root.'/vendor/spatie/shiki-php/bin';
    mkdir($bin, 0777, true);

    expect(resolveShikiBinDirFor($root))->toBe(realpath($bin));

    rmdir($bin);
    rmdir($root.'/vendor/spatie/shiki-php');
    rmdir($root.'/vendor/spatie');
    rmdir($root.'/vendor');
    rmdir($root);
});

test('install-shiki falls back to the package-local shiki bin dir when the app vendor dir is missing', function () {
    $root = sys_get_temp_dir().'/blogr-338-missing-'.uniqid();
    mkdir($root);

    $resolved = resolveShikiBinDirFor($root);

    // The fallback is the blogr package-local vendor dir (present when the package is the root / dev repo).
    expect($resolved)->not->toBeNull()
        ->and($resolved)->toEndWith('/vendor/spatie/shiki-php/bin')
        ->and(is_dir($resolved ?? ''))->toBeTrue();

    rmdir($root);
});
