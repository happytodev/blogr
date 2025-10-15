<?php

namespace Happytodev\Blogr\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Happytodev\Blogr\Tests\TestCase;
use Workbench\App\Models\User;
use Spatie\Permission\Models\Role;

class AssignAdminRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin role if it doesn't exist
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);
    }

    public function test_it_detects_existing_users_excluding_demo_users()
    {
        // Create a demo user (should be excluded)
        User::create([
            'name' => 'Admin Demo',
            'email' => 'admin@demo.com',
            'password' => bcrypt('password'),
        ]);

        // Create a real user
        $realUser = User::create([
            'name' => 'Real User',
            'email' => 'real@example.com',
            'password' => bcrypt('password'),
        ]);

        // Get users excluding demo
        $existingUsers = User::whereNotIn('email', [
            'admin@demo.com',
            'writer@demo.com',
        ])->get();

        $this->assertCount(1, $existingUsers);
        $this->assertEquals('real@example.com', $existingUsers->first()->email);
    }

    public function test_it_assigns_admin_role_to_first_user()
    {
        $user = User::create([
            'name' => 'First User',
            'email' => 'first@example.com',
            'password' => bcrypt('password'),
        ]);

        // Assign admin role
        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_it_skips_if_user_already_has_role()
    {
        $user = User::create([
            'name' => 'User With Role',
            'email' => 'withrole@example.com',
            'password' => bcrypt('password'),
        ]);

        // Assign writer role first
        $user->assignRole('writer');

        // Check if user already has a role
        $hasRole = $user->hasAnyRole(['admin', 'writer']);

        $this->assertTrue($hasRole);
    }
}
