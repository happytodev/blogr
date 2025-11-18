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
        $url = route('blog.show', ['slug' => $this->post->slug]);

        return (new MailMessage)
                    ->subject(__('blogr::notifications.post_saved_subject', ['author' => $this->author->name]))
                    ->line(__('blogr::notifications.post_saved_line1', ['author' => $this->author->name, 'title' => $this->post->title]))
                    ->action(__('blogr::notifications.view_post'), $url)
                    ->line(__('blogr::notifications.post_saved_line2'));
    }

    public function toDatabase($notifiable)
    {
        return [
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
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

