<?php

namespace Happytodev\Blogr\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallUserManagementCommand extends Command
{
    public $signature = 'blogr:install-user-management {--force : Overwrite existing files} {--with-test-users : Add test users to DatabaseSeeder}';

    public $description = 'Install User Management resources for Blogr';

    public function handle(): int
    {
        $this->info('🚀 Installing User Management for Blogr...');
        $this->newLine();

        if (!$this->checkPrerequisites()) {
            return self::FAILURE;
        }

        $this->publishResources();
        $this->publishPolicy();
        $this->publishSeeders();
        $this->updateUserModel();
        $this->updateDatabaseSeeder();

        // Assign admin role to the first existing user (e.g., created by filament:install)
        $this->assignAdminRoleToFirstUser();

        $this->newLine();
        $this->info('✅ User Management installed successfully!');
        $this->newLine();
        
        $this->displayNextSteps();

        return self::SUCCESS;
    }

    protected function checkPrerequisites(): bool
    {
        if (!class_exists('Spatie\Permission\PermissionServiceProvider')) {
            $this->error('❌ Spatie Laravel Permission is not installed!');
            $this->newLine();
            $this->line('Please install it first:');
            $this->line('composer require spatie/laravel-permission');
            $this->newLine();
            return false;
        }

        $this->line('✓ Spatie Laravel Permission is installed');
        return true;
    }

    protected function publishResources(): void
    {
        $this->info('🚀 Publishing UserResource files...');

        $stubsPath = __DIR__ . '/../../stubs/user-management';
        $appPath = app_path('Filament/Resources');

        $files = [
            'UserResource.php' => $appPath . '/UserResource.php',
            'Users/Pages/ListUsers.php' => $appPath . '/Users/Pages/ListUsers.php',
            'Users/Pages/CreateUser.php' => $appPath . '/Users/Pages/CreateUser.php',
            'Users/Pages/EditUser.php' => $appPath . '/Users/Pages/EditUser.php',
            'Users/Schemas/UserForm.php' => $appPath . '/Users/Schemas/UserForm.php',
            'Users/Tables/UsersTable.php' => $appPath . '/Users/Tables/UsersTable.php',
        ];

        foreach ($files as $stub => $destination) {
            if (File::exists($destination) && !$this->option('force')) {
                $this->warn("   ⚠ Skipped: {$destination} (already exists)");
                continue;
            }

            $this->copyStub($stubsPath . '/' . $stub, $destination);
            $this->line("   ✓ Created: " . str_replace(base_path() . '/', '', $destination));
        }
    }

    protected function publishPolicy(): void
    {
        $this->info('🚀 Publishing UserPolicy...');

        $stubPath = __DIR__ . '/../../stubs/user-management/UserPolicy.php';
        $destination = app_path('Policies/UserPolicy.php');

        if (File::exists($destination) && !$this->option('force')) {
            $this->warn("   ⚠ Skipped: {$destination} (already exists)");
            return;
        }

        $this->copyStub($stubPath, $destination);
        $this->line("   ✓ Created: " . str_replace(base_path() . '/', '', $destination));
    }

    protected function publishSeeders(): void
    {
        $this->info('🚀Publishing Seeders...');

        // Publish RoleSeeder
        $roleStubPath = __DIR__ . '/../../stubs/user-management/RoleSeeder.php';
        $roleDestination = database_path('seeders/RoleSeeder.php');

        if (File::exists($roleDestination) && !$this->option('force')) {
            $this->warn("   ⚠ Skipped: {$roleDestination} (already exists)");
        } else {
            $this->copyStub($roleStubPath, $roleDestination);
            $this->line("   ✓ Created: RoleSeeder");
        }

        // Publish PermissionSeeder
        $permissionStubPath = __DIR__ . '/../../stubs/user-management/PermissionSeeder.php';
        $permissionDestination = database_path('seeders/PermissionSeeder.php');

        if (File::exists($permissionDestination) && !$this->option('force')) {
            $this->warn("   ⚠ Skipped: {$permissionDestination} (already exists)");
        } else {
            $this->copyStub($permissionStubPath, $permissionDestination);
            $this->line("   ✓ Created: PermissionSeeder");
        }
    }

    protected function copyStub(string $stub, string $destination): void
    {
        File::ensureDirectoryExists(dirname($destination));
        File::copy($stub, $destination);
    }

    protected function updateUserModel(): void
    {
        $this->info('👌 Checking User model...');
        $this->newLine();

        $userModelPath = app_path('Models/User.php');
        
        if (!File::exists($userModelPath)) {
            $this->error('   ❌ User model not found!');
            return;
        }

        $content = File::get($userModelPath);

        // Check if HasRoles trait is already present
        if (str_contains($content, 'use HasRoles')) {
            $this->line('   ✓ HasRoles trait already present in User model');
            return;
        }

        // Ask for confirmation
        if (!$this->confirm('    Add HasRoles trait to User model?', true)) {
            $this->warn('   ⚠ Skipped: User model update');
            return;
        }

        // Add use statement for HasRoles trait
        if (!str_contains($content, "use Spatie\Permission\Traits\HasRoles;")) {
            $content = preg_replace(
                '/(use Illuminate\\\\Notifications\\\\Notifiable;)/',
                "use Spatie\Permission\Traits\HasRoles;\n$1",
                $content
            );
        }

        // Add HasRoles trait to the class
        $content = preg_replace(
            '/(use\s+HasFactory.*?;)/',
            "$1\n    use HasRoles;",
            $content
        );

        File::put($userModelPath, $content);
        $this->line('   ✓ Added HasRoles trait to User model');
    }

    protected function updateDatabaseSeeder(): void
    {
        $this->info('🆕 Updating DatabaseSeeder...');
        $this->newLine();

        $seederPath = database_path('seeders/DatabaseSeeder.php');
        
        if (!File::exists($seederPath)) {
            $this->error('   ❌ DatabaseSeeder not found!');
            return;
        }

        $content = File::get($seederPath);

        // Check if already updated
        if (str_contains($content, 'RoleSeeder::class')) {
            $this->line('   ✓ DatabaseSeeder already includes RoleSeeder');
        } else {
            // Ask for confirmation to add seeders call
            if ($this->confirm('    Add RoleSeeder and PermissionSeeder to DatabaseSeeder?', true)) {
                // Add the seeders call
                $seedersSnippet = "
        // Seed roles and permissions
        \$this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);
";
                
                // Find the run() method and add the call
                $content = preg_replace(
                    '/(public function run\(\): void\s*\{)/',
                    "$1$seedersSnippet",
                    $content
                );

                File::put($seederPath, $content);
                $this->line('   ✓ Added RoleSeeder and PermissionSeeder to DatabaseSeeder');
            }
        }

        // Reload content after modification
        $content = File::get($seederPath);

        // Ask if user wants to add test users
        if ($this->option('with-test-users') || $this->confirm('    Add test users (admin and writer) to DatabaseSeeder?', true)) {
            if (!str_contains($content, 'admin@example.com')) {
                $testUsersSnippet = File::get(__DIR__ . '/../../stubs/user-management/DatabaseSeederSnippet.php');
                
                // Add test users after the seeders call
                $content = preg_replace(
                    '/(RoleSeeder::class,\s*PermissionSeeder::class,\s*\]\);)/',
                    "$1\n$testUsersSnippet",
                    $content
                );

                File::put($seederPath, $content);
                $this->line('   ✓ Added test users to DatabaseSeeder');
                $this->line('      - admin@example.com (password: password) with admin role');
                $this->line('      - writer@example.com (password: password) with writer role');
            } else {
                $this->line('   ✓ Test users already present in DatabaseSeeder');
            }
        }
    }

    protected function displayNextSteps(): void
    {
        $this->info('⏭️ Next steps:');
        $this->line('   1. Run migrations: php artisan migrate');
        $this->line('   2. Seed the database: php artisan db:seed');
        $this->line('   3. Clear cache: php artisan optimize:clear');
        $this->newLine();
        $this->info('ℹ️ The UserResource will be automatically discovered by Filament.');
        $this->line('   Admin users will see the "Users" menu item.');
    }

    protected function assignAdminRoleToFirstUser(): void
    {
        try {
            $userModel = config('auth.providers.users.model');
            
            // Get the first user in the database
            $firstUser = $userModel::first();

            if (!$firstUser) {
                $this->line('   ℹ️  No users found in database yet.');
                return;
            }

            // Check if user has HasRoles trait
            if (!method_exists($firstUser, 'assignRole')) {
                $this->line('   ⚠️  User model does not have HasRoles trait yet.');
                return;
            }

            // Only assign if the user doesn't already have admin or writer role
            if (!$firstUser->hasAnyRole(['admin', 'writer'])) {
                $firstUser->assignRole('admin');
                $this->line("   ✓ Assigned admin role to first user ({$firstUser->email})");
            } else {
                $this->line("   ✓ First user ({$firstUser->email}) already has a role");
            }
        } catch (\Exception $e) {
            $this->line("   ⚠️  Could not assign admin role: {$e->getMessage()}");
        }
    }
}
