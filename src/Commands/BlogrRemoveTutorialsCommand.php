<?php

namespace Happytodev\Blogr\Commands;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Illuminate\Console\Command;

class BlogrRemoveTutorialsCommand extends Command
{
    public $signature = 'blogr:remove-tutorials
                        {--force : Skip confirmation prompts}';

    public $description = 'Remove Blogr tutorial content';

    public function handle(): int
    {
        $this->info('Removing Blogr tutorial content...');

        if (!$this->option('force') && !$this->confirm('This will permanently delete tutorial posts. Continue?')) {
            $this->warn('Removal cancelled.');
            return self::FAILURE;
        }

        $category = Category::where('slug', 'blogr-tutorial')->first();
        if (!$category) {
            $this->warn('No tutorial category found.');
            return self::SUCCESS;
        }

        $count = BlogPost::where('category_id', $category->id)->delete();
        $category->delete();

        // Remove tutorial user if no other posts
        $user = User::where('email', 'tutorial@blogr.dev')->first();
        if ($user && $user->blogPosts()->count() === 0) {
            $user->delete();
        }

        $this->info("✅ Successfully removed {$count} tutorial posts.");
        
        return self::SUCCESS;
    }
}
