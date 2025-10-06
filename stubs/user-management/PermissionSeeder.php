<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions for user management
        $userPermissions = [
            'view_users',
            'view_any_users',
            'create_users',
            'update_users',
            'delete_users',
            'restore_users',
            'force_delete_users',
        ];

        // Permissions for Blog Posts
        $blogPostPermissions = [
            'view_blog::posts',
            'view_any_blog::posts',
            'create_blog::posts',
            'update_blog::posts',
            'delete_blog::posts',
            'restore_blog::posts',
            'force_delete_blog::posts',
            'publish_blog::posts',      // Special permission to publish
            'replicate_blog::posts',
        ];

        // Permissions for Categories
        $categoryPermissions = [
            'view_categories',
            'view_any_categories',
            'create_categories',
            'update_categories',
            'delete_categories',
            'restore_categories',
            'force_delete_categories',
        ];

        // Permissions for Tags
        $tagPermissions = [
            'view_tags',
            'view_any_tags',
            'create_tags',
            'update_tags',
            'delete_tags',
            'restore_tags',
            'force_delete_tags',
        ];

        // Create all permissions
        $allPermissions = array_merge(
            $userPermissions,
            $blogPostPermissions,
            $categoryPermissions,
            $tagPermissions
        );

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Configure Admin role - Full access
        $adminRole = Role::findByName('admin', 'web');
        $adminRole->givePermissionTo($allPermissions);

        // Configure Writer role - Can create and edit, but cannot publish or delete
        $writerRole = Role::findByName('writer', 'web');

        $writerPermissions = [
            // Blog Posts - can do everything except publish and delete
            'view_blog::posts',
            'view_any_blog::posts',
            'create_blog::posts',
            'update_blog::posts',
            'replicate_blog::posts',
            // Note: NO publish_blog::posts, delete_blog::posts, restore_blog::posts, force_delete_blog::posts

            // Categories - full access
            'view_categories',
            'view_any_categories',
            'create_categories',
            'update_categories',
            'delete_categories',

            // Tags - full access
            'view_tags',
            'view_any_tags',
            'create_tags',
            'update_tags',
            'delete_tags',

            // NO access to user management
        ];

        $writerRole->givePermissionTo($writerPermissions);
    }
}
