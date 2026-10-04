<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $table = 'conversations';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'member_ids' => 'array',
        'related_request_ids' => 'array',
        'unread_count' => 'integer',
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id', 'id')->orderBy('sent_at', 'asc');
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id', 'id')->latest('sent_at');
    }

    public function toFrontendSummary(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'unit' => $this->unit,
            'ownerId' => $this->owner_id,
            'memberIds' => $this->member_ids ?? [],
            'relatedRequestIds' => $this->related_request_ids ?? [],
            'unreadCount' => $this->unread_count ?? 0,
        ];
    }
}
