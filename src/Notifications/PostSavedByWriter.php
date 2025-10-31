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
                    ->subject("[Blogr] Article enregistré par {$this->author->name}")
                    ->line("L'utilisateur {$this->author->name} a enregistré un article intitulé \"{$this->post->title}\".")
                    ->action('Voir l\'article', $url)
                    ->line('Vous recevez cette notification car vous êtes administrateur.');
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
}

