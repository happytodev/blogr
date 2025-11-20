<?php

namespace Happytodev\Blogr\Observers;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Jobs\SendPostNotificationJob;
use Illuminate\Support\Facades\Log;

class BlogPostObserver
{
    /**
     * Handle the BlogPost "created" event.
     * This observer runs AFTER all model hooks and events.
     */
    public function created(BlogPost $post): void
    {
        // Notification is now handled in CreateBlogPost::afterCreate()
        // This method only exists for potential future use
        // The afterCreate() hook runs AFTER all Filament relationships are saved
        Log::debug('BlogPostObserver::created - observer called (notification moved to page afterCreate)', [
            'post_id' => $post->id,
        ]);
    }
}
