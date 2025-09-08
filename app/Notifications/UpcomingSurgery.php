<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpcomingSurgery extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $scheduledTime)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
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
        return (new MailMessage)
            ->line('You have an upcoming surgery scheduled at ' . $this->scheduledTime)
            ->line('Thank you for using our application!');
    }
}
