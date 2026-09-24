<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification
{
    use Queueable;

    /**
     * Shape of every notification payload consumed by the inbox UI.
     *
     * @return array{title: string, message: string, url: ?string, tone: string, icon: string, actor_id?: int}
     */
    abstract public function content(): array;

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        if (! $this->allowedFor($notifiable)) {
            return [];
        }

        return $this->mailEnabled() ? ['database', 'mail'] : ['database'];
    }

    /**
     * Member notification preference column that can mute this notification.
     */
    protected function preferenceKey(): ?string
    {
        return null;
    }

    protected function allowedFor(object $notifiable): bool
    {
        $key = $this->preferenceKey();

        if (! $key || ! method_exists($notifiable, 'profile')) {
            return true;
        }

        return (bool) ($notifiable->profile?->{$key} ?? true);
    }

    protected function mailEnabled(): bool
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return array_merge($this->content(), [
            'created_at' => now()->toIso8601String(),
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $content = $this->content();

        $mail = (new MailMessage)
            ->subject($content['title'])
            ->greeting('Hello '.$notifiable->name.',')
            ->line($content['message']);

        if (! empty($content['url'])) {
            $mail->action('View on Jibon Sathi', url($content['url']));
        }

        return $mail->line('With love, the Jibon Sathi team.');
    }
}
