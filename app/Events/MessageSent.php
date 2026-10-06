<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $message;

    public function __construct(array $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        $conversationId = $this->message['conversationId'] ?? '';
        return [
            new Channel('chat.conversation.' . $conversationId),
            new Channel('chat.global'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
