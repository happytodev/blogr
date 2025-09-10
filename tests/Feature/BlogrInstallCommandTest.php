<?php

use Happytodev\Blogr\Commands\BlogrInstallCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

// describe('BlogrInstallCommand - Unit Tests', function () {

//     beforeEach(function () {
//         // Mock Process facade for all tests to avoid actual system calls
//         Process::shouldReceive('run')->andReturn(new class {
//             public function successful() { return true; }
//             public function errorOutput() { return ''; }
//         });
//     });

//     it('executes successfully with skip options', function () {
//         $exitCode = Artisan::call('blogr:install', [
//             '--skip-npm' => true,
//             '--skip-tutorials' => true,
//         ]);

//         expect($exitCode)->toBe(0);
//         expect(Artisan::output())->toContain('✅ Blogr installation completed successfully!');
//     });

//     it('shows welcome message', function () {
//         Artisan::call('blogr:install', [
//             '--skip-npm' => true,
//             '--skip-tutorials' => true,
//         ]);

//         expect(Artisan::output())->toContain('🚀 Welcome to Blogr Installation!');
//     });

//     it('displays next steps', function () {
//         Artisan::call('blogr:install', [
//             '--skip-npm' => true,
//             '--skip-tutorials' => true,
//         ]);

//         $output = Artisan::output();
//         expect($output)->toContain('Next steps:');
//         expect($output)->toContain('Access your Filament admin panel');
//     });

//     it('handles different option combinations', function () {
//         $combinations = [
//             ['--skip-npm' => true, '--skip-tutorials' => true],
//             ['--skip-npm' => true],
//             ['--skip-tutorials' => true],
//             [], // No options
//         ];

//         foreach ($combinations as $options) {
//             $exitCode = Artisan::call('blogr:install', $options);
//             expect($exitCode)->toBe(0);
//         }
//     });

//     it('command has correct metadata', function () {
//         $command = new BlogrInstallCommand();

//         expect($command->getName())->toBe('blogr:install');
//         expect($command->getDescription())->toBe('Install and configure Blogr with all necessary steps');

//         $definition = $command->getDefinition();
//         $options = $definition->getOptions();

//         expect(isset($options['skip-npm']))->toBeTrue();
//         expect(isset($options['skip-tutorials']))->toBeTrue();
//     });

// });
