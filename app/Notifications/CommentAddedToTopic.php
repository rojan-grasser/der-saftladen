<?php

namespace App\Notifications;

use App\Models\ForumPost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentAddedToTopic extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ForumPost $post)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $topic = $this->post->topic;
        return (new MailMessage)
            ->subject('Neuer Kommentar zu Ihrem Thema: ' . $topic->title)
            ->line('Es wurde ein neuer Kommentar zu Ihrem Thema "' . $topic->title . '" verfasst.')
            ->line('Inhalt: ' . $this->post->content)
            ->action('Thema ansehen', route('topics.show', [
                'professionId' => $topic->profession_id,
                'topicId' => $topic->id,
            ]))
            ->line('Vielen Dank für die Nutzung unserer Anwendung!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'post_id' => $this->post->id,
            'topic_id' => $this->post->topic_id,
            'user_id' => $this->post->user_id,
            'content' => $this->post->content,
        ];
    }
}
