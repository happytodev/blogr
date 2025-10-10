<?php

namespace Happytodev\Blogr\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for blog posts
        $permissions = [
            'view blog posts',
            'create blog posts',
            'edit blog posts',
            'delete blog posts',
            'publish blog posts',
            'view blog series',
            'create blog series',
            'edit blog series',
            'delete blog series',
            'manage blog settings',
            'view users',
            'create users',
            'edit users',
            'delete users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $writerRole = Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);

        // Admin has all permissions
        $adminRole->givePermissionTo(Permission::all());

        // Writer has limited permissions (cannot manage users or settings)
        $writerRole->givePermissionTo([
            'view blog posts',
            'create blog posts',
            'edit blog posts',
            'view blog series',
            'create blog series',
            'edit blog series',
        ]);

        $this->command->info('✅ Roles and permissions created successfully.');
        $this->command->info('   • Admin role: Full access to all features');
        $this->command->info('   • Writer role: Can create and edit blog posts and series');
    }
}
