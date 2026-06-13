<?php

beforeEach(function () {
    $this->reflection = new ReflectionClass(\Happytodev\Blogr\Commands\InstallBreezyCommand::class);
});

it('detects hasAvatars pattern in existing config', function () {
    $content = "BreezyCore::make()
                ->myProfile(
                    shouldRegisterUserMenu: true,
                    hasAvatars: false,
                )
                ->enableTwoFactorAuthentication(
                    force: false,
                )";

    $hasMatch = preg_match('/hasAvatars:\s*(true|false)/', $content);

    expect($hasMatch)->toBe(1);
});

it('replaces hasAvatars with config() call', function () {
    $content = "BreezyCore::make()
                ->myProfile(
                    shouldRegisterUserMenu: true,
                    hasAvatars: false,
                )
                ->enableTwoFactorAuthentication(
                    force: false,
                )";

    $result = preg_replace(
        '/hasAvatars:\s*(true|false)/',
        "hasAvatars: config('blogr.enable_avatar_upload', true)",
        $content,
        1
    );

    expect($result)->toContain("config('blogr.enable_avatar_upload', true)")
        ->and($result)->not->toContain('hasAvatars: false');
});

it('handles nested parentheses in myProfile correctly', function () {
    $content = "BreezyCore::make()
                ->myProfile(
                    shouldRegisterUserMenu: true,
                    hasAvatars: config('blogr.enable_avatar_upload', true),
                )
                ->enableTwoFactorAuthentication(
                    force: false,
                )";

    $insertion = "\n                ->avatarUploadComponent(fn (\$fileUpload) => \$fileUpload->imageEditor())";

    // Simulate the parentesis-balancing logic from InstallBreezyCommand
    $pos = strpos($content, '->myProfile(');
    expect($pos)->not->toBeFalse();

    $openParen = $pos + strlen('->myProfile(');
    $depth = 1;
    $i = $openParen;
    while ($depth > 0 && isset($content[$i])) {
        if ($content[$i] === '(') $depth++;
        elseif ($content[$i] === ')') $depth--;
        $i++;
    }
    $insertPos = $i;
    $result = substr_replace($content, $insertion, $insertPos, 0);

    expect($result)->toContain('avatarUploadComponent')
        ->and($result)->toContain('enableTwoFactorAuthentication');
});

it('adds avatarUploadComponent after single-line myProfile', function () {
    $content = "BreezyCore::make()
                ->myProfile(shouldRegisterUserMenu: true, hasAvatars: config('blogr.enable_avatar_upload', true))
                ->enableTwoFactorAuthentication(
                    force: false,
                )";

    $insertion = "\n                ->avatarUploadComponent(fn (\$fileUpload) => \$fileUpload->imageEditor())";

    $pos = strpos($content, '->myProfile(');
    expect($pos)->not->toBeFalse();

    $openParen = $pos + strlen('->myProfile(');
    $depth = 1;
    $i = $openParen;
    while ($depth > 0 && isset($content[$i])) {
        if ($content[$i] === '(') $depth++;
        elseif ($content[$i] === ')') $depth--;
        $i++;
    }
    $insertPos = $i;
    $result = substr_replace($content, $insertion, $insertPos, 0);

    expect($result)->toContain('avatarUploadComponent')
        ->and($result)->toContain('enableTwoFactorAuthentication');
});

it('detects enableTwoFactorAuthentication in existing config', function () {
    $content = "BreezyCore::make()
                ->myProfile(
                    shouldRegisterUserMenu: true,
                    hasAvatars: config('blogr.enable_avatar_upload', true),
                )
                ->enableTwoFactorAuthentication(
                    force: false,
                )";

    expect(str_contains($content, 'enableTwoFactorAuthentication'))->toBeTrue();
});

it('detects misplaced avatarUploadComponent inside myProfile', function () {
    $reflection = new ReflectionClass(\Happytodev\Blogr\Commands\InstallBreezyCommand::class);
    $method = $reflection->getMethod('isAvatarUploadInsideMyProfile');
    $method->setAccessible(true);
    $command = app(\Happytodev\Blogr\Commands\InstallBreezyCommand::class);

    // Simulate the broken state from previous buggy version
    $content = "BreezyCore::make()
                ->myProfile(
                    shouldRegisterUserMenu: true,
                    hasAvatars: config('blogr.enable_avatar_upload', true)
                ->avatarUploadComponent(fn (\$fileUpload) => \$fileUpload->imageEditor()),
                )
                ->enableTwoFactorAuthentication(
                    force: false,
                )";

    expect($method->invoke($command, $content))->toBeTrue();
});

it('does not detect correctly placed avatarUploadComponent', function () {
    $reflection = new ReflectionClass(\Happytodev\Blogr\Commands\InstallBreezyCommand::class);
    $method = $reflection->getMethod('isAvatarUploadInsideMyProfile');
    $method->setAccessible(true);
    $command = app(\Happytodev\Blogr\Commands\InstallBreezyCommand::class);

    $content = "BreezyCore::make()
                ->myProfile(
                    shouldRegisterUserMenu: true,
                    hasAvatars: config('blogr.enable_avatar_upload', true),
                )
                ->avatarUploadComponent(fn (\$fileUpload) => \$fileUpload->imageEditor())
                ->enableTwoFactorAuthentication(
                    force: false,
                )";

    expect($method->invoke($command, $content))->toBeFalse();
});

it('repairs misplaced avatarUploadComponent', function () {
    $content = "BreezyCore::make()
                ->myProfile(
                    shouldRegisterUserMenu: true,
                    hasAvatars: config('blogr.enable_avatar_upload', true)
                ->avatarUploadComponent(fn (\$fileUpload) => \$fileUpload->imageEditor()),
                )
                ->enableTwoFactorAuthentication(
                    force: false,
                )";

    // Simulate the repair logic from InstallBreezyCommand
    $avatarPos = strpos($content, '->avatarUploadComponent');
    $reflection = new ReflectionClass(\Happytodev\Blogr\Commands\InstallBreezyCommand::class);
    $findParen = $reflection->getMethod('findMatchingParen');
    $findParen->setAccessible(true);
    $command = app(\Happytodev\Blogr\Commands\InstallBreezyCommand::class);

    $closeParen = $findParen->invoke($command, $content, $avatarPos + strlen('->avatarUploadComponent('));
    $fullCall = substr($content, $avatarPos, $closeParen - $avatarPos + 1);
    $content = substr_replace($content, '', $avatarPos, $closeParen - $avatarPos + 1);
    $content = preg_replace('/,\s*\n\s*\)/', ')' . "\n", $content);

    $myProfileOpen = strpos($content, '->myProfile(');
    $myProfileClose = $findParen->invoke($command, $content, $myProfileOpen + strlen('->myProfile('));
    $insertPos = $myProfileClose + 1;
    $content = substr_replace($content, "\n            " . $fullCall, $insertPos, 0);

    expect($content)->toContain('avatarUploadComponent')
        ->and($content)->not->toMatch('/myProfile\([^)]*avatarUploadComponent/');
});
