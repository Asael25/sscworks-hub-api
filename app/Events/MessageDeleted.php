<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $messageId;
    public string $conversationId;

    public function __construct(string $messageId, string $conversationId)
    {
        $this->messageId = $messageId;
        $this->conversationId = $conversationId;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.conversation.' . $this->conversationId),
            new Channel('chat.global'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }
}
