<?php

test('all PHP files in src/ have valid syntax', function () {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__.'/../../src')
    );

    $errors = [];
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $output = [];
        $returnCode = 0;
        exec('php -l '.escapeshellarg($file->getPathname()).' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            $errors[] = $file->getPathname().': '.implode(' ', $output);
        }
    }

    if (! empty($errors)) {
        dump($errors);
    }

    expect($errors)->toBeEmpty();
});
