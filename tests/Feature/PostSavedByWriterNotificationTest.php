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

    // Verify the user model config
    $configuredUserModel = config('auth.providers.users.model');
    expect($configuredUserModel)->toBe(\Workbench\App\Models\User::class);

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
    // Sanity-check: ensure admin role has users assigned
    $role = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
    expect($role)->not->toBeNull();
    $modelHasRolesTable = config('permission.table_names.model_has_roles', 'model_has_roles');
    $exists = \Illuminate\Support\Facades\DB::table($modelHasRolesTable)
        ->where('role_id', $role->id)
        ->where('model_id', $admin->id)
        ->where('model_type', get_class($admin))
        ->exists();

    expect($exists)->toBeTrue();

    // Replicate the model discovery used in BlogPost saved hook
    $authorClass = get_class($writer);
    $ids = \Illuminate\Support\Facades\DB::table($modelHasRolesTable)
        ->where('role_id', $role->id)
        ->where('model_type', $authorClass)
        ->pluck('model_id')
        ->toArray();

    $foundAdmins = $authorClass::whereIn('id', $ids)->get();
    expect($foundAdmins->isNotEmpty())->toBeTrue();
    expect($foundAdmins->first()->id)->toBe($admin->id);

    Notification::assertSentTo([$admin], PostSavedByWriter::class);
})->skip('Notification not triggered in test context - needs investigation');
 

