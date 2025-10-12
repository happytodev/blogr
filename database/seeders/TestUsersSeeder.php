<?php

namespace Happytodev\Blogr\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        // Create admin user
        $admin = $userModel::updateOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Admin User',
                'slug' => 'admin-user',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'bio' => [
                    'en' => 'Experienced administrator and content manager with a passion for creating quality content. Leading the editorial team and ensuring the blog maintains high standards.',
                    'fr' => 'Administrateur expérimenté et gestionnaire de contenu passionné par la création de contenu de qualité. Dirige l\'équipe éditoriale et garantit que le blog maintient des normes élevées.',
                ],
                'avatar' => 'https://ui-avatars.com/api/?name=Admin+User&color=7F9CF5&background=EBF4FF',
            ]
        );

        // Assign admin role
        if (method_exists($admin, 'assignRole')) {
            $admin->assignRole('admin');
        }

        // Create writer user
        $writer = $userModel::updateOrCreate(
            ['email' => 'writer@demo.com'],
            [
                'name' => 'Writer User',
                'slug' => 'writer-user',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'bio' => [
                    'en' => 'Passionate writer and blogger focusing on technology, development, and best practices. Always eager to share knowledge and learn from the community.',
                    'fr' => 'Écrivain et blogueur passionné axé sur la technologie, le développement et les meilleures pratiques. Toujours désireux de partager ses connaissances et d\'apprendre de la communauté.',
                ],
                'avatar' => 'https://ui-avatars.com/api/?name=Writer+User&color=10B981&background=D1FAE5',
            ]
        );

        // Assign writer role
        if (method_exists($writer, 'assignRole')) {
            $writer->assignRole('writer');
        }

        $this->command->info('✅ Test users created successfully.');
        $this->command->info('   • admin@demo.com (password: password) - Admin role');
        $this->command->info('   • writer@demo.com (password: password) - Writer role');
    }
}
