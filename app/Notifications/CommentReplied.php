<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class CommentReplied extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Comment $comment
    )
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
        //return ['mail'];
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    public function toDatabase($notifiable): array
    {
        // The basic array of the Filament
        $filamentMessage = FilamentNotification::make()
            ->title('A new response to your comment')
            ->body(auth()->user()->name . ' answered: "' . $this->comment->body . '"')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->info()
            ->getDatabaseMessage(); // Generates a Filament structure for the database

        // Add custom data for the notification
        $filamentMessage['data'] = array_merge($filamentMessage['data'] ?? [], [
            'type' => 'comment_reply',
            'post_id' => $this->comment->post_id,
            'comment_id' => $this->comment->id,
        ]);

        return $filamentMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    /*
    public function toArray(object $notifiable): array
    {
        return array_merge($this->toDatabase($notifiable), [
            'type' => 'comment_reply', // for filter in UI
            'post_id' => $this->comment->post_id,
            'comment_id' => $this->comment->id,
        ]);
    }
    */
}
