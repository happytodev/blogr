
        // Create Admin User
        $adminUser = \App\Models\User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );
        $adminUser->assignRole('admin');

        // Create Writer User
        $writerUser = \App\Models\User::firstOrCreate(
            ['email' => 'writer@example.com'],
            [
                'name' => 'Writer User',
                'password' => bcrypt('password'),
            ]
        );
        $writerUser->assignRole('writer');
