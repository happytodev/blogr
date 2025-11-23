<?php

namespace Happytodev\Blogr\Jobs;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Notifications\PostSavedByWriter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPostNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $postId;

    /**
     * Create a new job instance.
     */
    public function __construct($postId)
    {
        $this->postId = $postId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get the post with all its translations
            $post = BlogPost::with('translations')->find($this->postId);

            if (!$post) {
                Log::warning('SendPostNotificationJob: Post not found', ['post_id' => $this->postId]);
                return;
            }

            Log::info('SendPostNotificationJob: Processing post', [
                'post_id' => $post->id,
                'user_id' => $post->user_id,
                'translations_count' => $post->translations->count(),
            ]);

            if (!$post->user_id || $post->translations->isEmpty()) {
                Log::info('SendPostNotificationJob: Skipping - no user_id or no translations', [
                    'post_id' => $post->id,
                ]);
                return;
            }

            // Get the author
            $userModel = config('auth.providers.users.model');
            $author = $userModel::find($post->user_id);
            if (!$author) {
                Log::warning('SendPostNotificationJob: Author not found', ['author_id' => $post->user_id]);
                return;
            }

            Log::info('SendPostNotificationJob: Author found', [
                'author' => $author->name,
                'roles' => $author->getRoleNames()->toArray(),
            ]);

            // Check if author is a writer (but not an admin)
            if (!method_exists($author, 'hasRole') || !$author->hasRole('writer') || $author->hasRole('admin')) {
                Log::info('SendPostNotificationJob: Author is not a writer or is admin, skipping');
                return;
            }

            // Get all admin users
            $adminUsers = collect();
            try {
                $adminUsers = $userModel::role('admin')->get();
                Log::info('SendPostNotificationJob: Found admins via role()', ['count' => $adminUsers->count()]);
            } catch (\Throwable $e) {
                Log::info('SendPostNotificationJob: role() failed, trying whereHas', ['error' => $e->getMessage()]);
                $adminUsers = $userModel::whereHas('roles', function ($q) {
                    $q->where('name', 'admin');
                })->get();
                Log::info('SendPostNotificationJob: Found admins via whereHas', ['count' => $adminUsers->count()]);
            }

            if ($adminUsers->isEmpty()) {
                Log::warning('SendPostNotificationJob: No admin users found');
                return;
            }

            Log::info('SendPostNotificationJob: Dispatching notifications', [
                'post_id' => $post->id,
                'author_id' => $author->id,
                'admin_count' => $adminUsers->count(),
            ]);

            // Dispatch notifications
            foreach ($adminUsers as $admin) {
                Log::info('SendPostNotificationJob: Notifying admin', [
                    'admin_id' => $admin->id,
                    'admin_email' => $admin->email,
                ]);
                $admin->notify(new PostSavedByWriter($post, $author));
            }

            Log::info('SendPostNotificationJob: Notifications dispatched successfully');
        } catch (\Throwable $e) {
            Log::error('SendPostNotificationJob: Failed to send notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
