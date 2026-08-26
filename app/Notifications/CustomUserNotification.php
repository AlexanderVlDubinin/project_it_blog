<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomUserNotification extends Notification implements ShouldQueue // ShouldQueue added for sending in the background
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public array $channels, // Pass here ['database', 'mail'] or one of them
        public string $title,
        public string $message,
        public string $type,
        public string $icon
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $currentColors = $this->getEmailColors($this->type);

        return (new MailMessage)
            ->subject($this->title)
            ->view('notifications.custom-email', [
                'title' => $this->title,
                'notificationBody' => $this->message,
                'colors' => $currentColors,
                'actionUrl' => url('/'), // URL for the button
                'actionText' => 'Go to Website'
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->message,
            'type' => $this->type,
            'icon' => $this->icon,
        ];
    }

    protected function getEmailColors(string $type): array
    {
        $colors = [
            'warning' => [
                'bg'         => '#fef3c7', // bg-amber-100
                'border'     => '#fbbf24', // border-amber-400
                'mainText'   => '#f59e0b', // text-amber-500
                'headerText' => '#b45309', // text-amber-700
                'subText'    => '#f59e0b', // text-amber-400 (или #fbbf24)
                'btnBg'      => '#fef8e7', // bg-amber-50
                'btnText'    => '#b45309', // text-amber-700
            ],
            'success' => [
                'bg'         => '#dcfce7', // bg-green-100
                'border'     => '#4ade80', // border-green-400
                'mainText'   => '#22c55e', // text-green-500
                'headerText' => '#15803d', // text-green-700
                'subText'    => '#22c55e', // text-green-400
                'btnBg'      => '#f0fdf4', // bg-green-50
                'btnText'    => '#15803d', // text-green-700
            ],
            'danger'  => [
                'bg'         => '#fee2e2', // bg-red-100
                'border'     => '#f87171', // border-red-400
                'mainText'   => '#ef4444', // text-red-500
                'headerText' => '#b91c1c', // text-red-700
                'subText'    => '#ef4444', // text-red-400
                'btnBg'      => '#fef2f2', // bg-red-50
                'btnText'    => '#b91c1c', // text-red-700
            ],
            'info'    => [
                'bg'         => '#dbeafe', // bg-blue-100
                'border'     => '#60a5fa', // border-blue-400
                'mainText'   => '#3b82f6', // text-blue-500
                'headerText' => '#1d4ed8', // text-blue-700
                'subText'    => '#3b82f6', // text-blue-400
                'btnBg'      => '#eff6ff', // bg-blue-50
                'btnText'    => '#1d4ed8', // text-blue-700
            ],
        ];

        return $colors[$type] ?? $colors['info'];
    }
}
