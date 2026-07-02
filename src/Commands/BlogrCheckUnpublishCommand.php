<?php

namespace Happytodev\Blogr\Commands;

use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Console\Command;

class BlogrCheckUnpublishCommand extends Command
{
    public $signature = 'blogr:check-unpublish';

    public $description = 'Unpublish posts whose unpublish_at date has passed';

    public function handle(): int
    {
        $count = BlogPost::query()
            ->where('is_published', true)
            ->whereNotNull('unpublish_at')
            ->where('unpublish_at', '<=', now())
            ->update([
                'is_published' => false,
                'published_at' => null,
                'unpublish_at' => null,
            ]);

        if ($count > 0) {
            $this->info("Unpublished {$count} post(s) whose unpublish_at date has passed.");
        } else {
            $this->info('No posts to unpublish.');
        }

        return self::SUCCESS;
    }
}
