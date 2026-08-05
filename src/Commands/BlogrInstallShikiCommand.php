<?php

namespace Happytodev\Blogr\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class BlogrInstallShikiCommand extends Command
{
    public $signature = 'blogr:install-shiki';

    public $description = 'Install the Shiki npm package for syntax highlighting';

    public function handle(): int
    {
        $nodeBin = (new ExecutableFinder)->find('node');

        if (! $nodeBin) {
            $this->error('Node.js is not installed. Please install Node.js first (https://nodejs.org).');

            return self::FAILURE;
        }

        $this->info('Node.js found at: '.$nodeBin);

        $shikiDir = $this->resolveShikiBinDir();

        if (! $shikiDir) {
            $this->error('spatie/shiki-php is not installed. Run composer require spatie/shiki-php first.');

            return self::FAILURE;
        }

        $shikiInstalled = is_dir($shikiDir.'/node_modules/shiki');

        if ($shikiInstalled) {
            $this->info('Shiki is already installed.');

            return self::SUCCESS;
        }

        $this->info('Installing Shiki via npm...');

        $process = new Process(['npm', 'install', 'shiki'], $shikiDir);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Failed to install Shiki:');
            $this->error($process->getErrorOutput());

            return self::FAILURE;
        }

        $this->info('Shiki installed successfully.');

        return self::SUCCESS;
    }

    /**
     * Resolve the spatie/shiki-php bin directory.
     *
     * When blogr is a composer dependency, the package lives in the consuming
     * app's vendor directory. When blogr is the root package (development),
     * it lives in the package-local vendor directory.
     */
    public function resolveShikiBinDir(?string $appBasePath = null): ?string
    {
        $appBasePath ??= base_path();

        $candidates = [
            $appBasePath.'/vendor/spatie/shiki-php/bin',
            dirname(__DIR__, 2).'/vendor/spatie/shiki-php/bin',
        ];

        foreach ($candidates as $dir) {
            if (is_dir($dir)) {
                $resolved = realpath($dir);

                return $resolved === false ? $dir : $resolved;
            }
        }

        return null;
    }
}
