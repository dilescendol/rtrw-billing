<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A simple database notification used for in-app bell items.
 *
 * Stored payload schema:
 *   - title: short headline shown bold
 *   - message: descriptive body
 *   - icon: bootstrap-icons class name (without the leading "bi bi-")
 *   - level: one of info|success|warning|danger
 *   - url: optional click-through link
 */
class GenericNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $icon = 'bell',
        public string $level = 'info',
        public ?string $url = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'icon' => $this->icon,
            'level' => $this->level,
            'url' => $this->url,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->line($this->message);
    }
}
