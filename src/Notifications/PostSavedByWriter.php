<?php

namespace Happytodev\Blogr\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;

class PostSavedByWriter extends Notification
{
    use Queueable;

    protected $post;
    protected $author;

    public function __construct($post, $author)
    {
        $this->post = $post;
        $this->author = $author;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        // Reload the post with translations to ensure they're loaded
        $post = $this->post->fresh(['translations']);
        
        // Get the default locale for the post (or fallback to config)
        $locale = $post->default_locale ?? config('app.locale', 'en');
        
        // Get the first published translation for display
        $translation = $post->translations
            ->where('locale', $locale)
            ->first();
        
        if (!$translation && $post->translations->isNotEmpty()) {
            // Fallback to first translation if default locale not found
            $translation = $post->translations->first();
        }
        
        $postTitle = $translation ? $translation->title : '[Untitled]';
        
        // Link to the draft in admin (edit page in Filament)
        // Using direct URL construction for better compatibility
        $adminUrl = url('/admin/blog-posts/' . $post->id . '/edit');

        return (new MailMessage)
                    ->subject(__('blogr::notifications.post_saved_subject', ['author' => $this->author->name]))
                    ->line(__('blogr::notifications.post_saved_line1', ['author' => $this->author->name, 'title' => $postTitle]))
                    ->action(__('blogr::notifications.view_post'), $adminUrl)
                    ->line(__('blogr::notifications.post_saved_line2'));
    }

    public function toDatabase($notifiable)
    {
        // Reload the post with translations to ensure they're loaded
        $post = $this->post->fresh(['translations']);
        
        // Get the default locale for the post (or fallback to config)
        $locale = $post->default_locale ?? config('app.locale', 'en');
        
        // Get the translation for the database notification
        $translation = $post->translations
            ->where('locale', $locale)
            ->first();
        
        if (!$translation && $post->translations->isNotEmpty()) {
            // Fallback to first translation if default locale not found
            $translation = $post->translations->first();
        }
        
        $postTitle = $translation ? $translation->title : '[Untitled]';
        
        return [
            'post_id' => $post->id,
            'post_title' => $postTitle,
            'author_id' => $this->author->id,
            'author_name' => $this->author->name,
        ];
    }

    // For compatibility with older Laravel versions
    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }

    public function getPost()
    {
        return $this->post;
    }

    public function getAuthor()
    {
        return $this->author;
    }
}

