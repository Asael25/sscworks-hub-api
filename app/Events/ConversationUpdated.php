<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $conversation;

    public function __construct(array $conversation)
    {
        $this->conversation = $conversation;
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('chat.global'),
        ];

        if (!empty($this->conversation['id'])) {
            $channels[] = new Channel('chat.conversation.' . $this->conversation['id']);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'conversation.updated';
    }
}
