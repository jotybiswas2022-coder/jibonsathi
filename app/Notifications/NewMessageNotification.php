<?php

namespace App\Notifications;

use App\Models\Message;

class NewMessageNotification extends BaseNotification
{
    public function __construct(public Message $message)
    {
    }

    protected function preferenceKey(): ?string
    {
        return 'notify_messages';
    }

    public function content(): array
    {
        $sender = $this->message->sender;
        $preview = str($this->message->body)->limit(70)->toString();

        return [
            'title' => 'New message',
            'message' => "{$sender?->name}: {$preview}",
            'url' => route('messages.show', $this->message->conversation_id, absolute: false),
            'tone' => 'info',
            'icon' => 'chat',
            'actor_id' => $sender?->id,
            'actor_name' => $sender?->name,
            'actor_photo' => $sender?->photoUrl(),
        ];
    }
}
