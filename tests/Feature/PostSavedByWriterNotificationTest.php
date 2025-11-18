<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Notifications\PostSavedByWriter;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    // Prepare roles
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);
});

it('sends notification to admins when a writer saves a post', function () {
    Notification::fake();

    // create admin and writer
    $admin = User::factory()->create();
    $writer = User::factory()->create();

    $admin->assignRole('admin');
    $writer->assignRole('writer');

    // Verify roles are properly assigned BEFORE creating the post
    expect($admin->hasRole('admin'))->toBeTrue();
    expect($writer->hasRole('writer'))->toBeTrue();
    expect($writer->hasRole('admin'))->toBeFalse();

    // Refresh writer to ensure role cache is cleared
    $writer->refresh();
    $writer->load('roles');
    
    // Verify again after refresh
    expect($writer->hasRole('writer'))->toBeTrue();
    expect($writer->hasRole('admin'))->toBeFalse();

    // create a category (required by posts) and create post attributed to writer
    $category = \Happytodev\Blogr\Models\Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $writer->id,
        'category_id' => $category->id,
        'is_published' => false,
    ]);

    // After creation, notification should have been sent to admins
    Notification::assertSentTo([$admin], PostSavedByWriter::class);
});

it('does not send notification when an admin saves a post', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $category = \Happytodev\Blogr\Models\Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $admin->id,
        'category_id' => $category->id,
        'is_published' => false,
    ]);

    // Notification should NOT be sent since the admin saved their own post
    Notification::assertNotSentTo([$admin], PostSavedByWriter::class);
});

it('sends notification to multiple admins when a writer saves a post', function () {
    Notification::fake();

    $admin1 = User::factory()->create();
    $admin2 = User::factory()->create();
    $writer = User::factory()->create();

    $admin1->assignRole('admin');
    $admin2->assignRole('admin');
    $writer->assignRole('writer');

    $writer->refresh();
    $writer->load('roles');

    $category = \Happytodev\Blogr\Models\Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $writer->id,
        'category_id' => $category->id,
        'is_published' => false,
    ]);

    // Notification should be sent to both admins
    Notification::assertSentTo([$admin1, $admin2], PostSavedByWriter::class);
});

it('notification contains correct post and author data', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $writer = User::factory()->create();

    $admin->assignRole('admin');
    $writer->assignRole('writer');

    $writer->refresh();
    $writer->load('roles');

    $category = \Happytodev\Blogr\Models\Category::factory()->create();

    $post = BlogPost::create([
        'user_id' => $writer->id,
        'category_id' => $category->id,
        'is_published' => false,
    ]);

    Notification::assertSentTo([$admin], PostSavedByWriter::class, function ($notification) use ($post, $writer) {
        return $notification->getPost()->id === $post->id &&
               $notification->getAuthor()->id === $writer->id;
    });
});
 

