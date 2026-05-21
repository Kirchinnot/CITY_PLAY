<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCode extends Notification
{
    use Queueable;

    public string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ton code de connexion CityPlay')
            ->greeting('Bonjour '.($notifiable->name ?? ''))
            ->line('Voici ton code de connexion à usage unique.')
            ->line('Code : '.$this->code)
            ->line('Ce code expire dans 10 minutes.')
            ->line('Si tu n’as pas essayé de te connecter, ignore ce message.');
    }
}
