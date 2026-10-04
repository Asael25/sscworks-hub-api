<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'attachments' => 'array',
        'reply_to' => 'array',
        'deleted' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id', 'id');
    }

    public function toFrontendFormat(): array
    {
        return [
            'id' => $this->id,
            'conversationId' => $this->conversation_id,
            'senderId' => $this->sender_id,
            'at' => $this->sent_at ? $this->sent_at->toISOString() : $this->created_at->toISOString(),
            'body' => $this->body,
            'attachments' => $this->attachments ?? [],
            'replyTo' => $this->reply_to,
            'status' => $this->status ?: 'sent',
            'deleted' => (bool)$this->deleted,
        ];
    }
}
