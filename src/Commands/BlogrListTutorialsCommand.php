<?php

namespace Happytodev\Blogr\Commands;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Illuminate\Console\Command;

class BlogrListTutorialsCommand extends Command
{
    public $signature = 'blogr:list-tutorials';

    public $description = 'List Blogr tutorial content';

    public function handle(): int
    {
        $category = Category::where('slug', 'blogr-tutorial')->first();
        if (!$category) {
            $this->warn('No tutorial category found.');
            return self::SUCCESS;
        }

        $posts = BlogPost::where('category_id', $category->id)
            ->orderBy('created_at')
            ->get();

        if ($posts->isEmpty()) {
            $this->warn('No tutorial posts found.');
            return self::SUCCESS;
        }

        $this->info('Blogr Tutorial Posts:');
        $this->line('');

        foreach ($posts as $post) {
            $status = $post->is_published ? 'Published' : 'Draft';
            $this->line("• {$post->title} ({$status})");
        }

        $this->line('');
        $this->info("Total: {$posts->count()} posts");
        
        return self::SUCCESS;
    }
}
