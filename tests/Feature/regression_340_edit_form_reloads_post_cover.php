<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Services\VersioningService;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Role;

uses(CmsTestCase::class);

final class TestableEditBlogPost extends EditBlogPost
{
    public function setRecordModel(BlogPost $post): void
    {
        $this->record = $post;
    }

    public function fillPostData(array $data): array
    {
        return $this->mutateFormDataBeforeFill($data);
    }
}

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $this->session(['errors' => new ViewErrorBag]);
});

test('edit form overlays the draft post-level cover in the Post Metadata field', function () {
    $category = Category::factory()->create();
    $post = BlogPost::factory()->create([
        'user_id' => auth()->id(),
        'category_id' => $category->id,
        'is_published' => false,
        'photo' => 'blog-photos/old.jpg',
    ]);

    app(VersioningService::class)->savePostDraft($post, [
        'photo' => 'blog-photos/new.jpg',
        'translations' => [
            ['locale' => 'en', 'title' => $post->title, 'slug' => $post->slug, 'content' => $post->content],
        ],
    ]);

    $page = new TestableEditBlogPost;
    $page->setRecordModel($post);

    $data = $page->fillPostData([
        'photo' => 'blog-photos/old.jpg',
        'translations' => [
            ['locale' => 'en', 'photo' => 'blog-photos/trans.jpg'],
        ],
    ]);

    expect($data['photo'])->toBe('blog-photos/new.jpg');
});
