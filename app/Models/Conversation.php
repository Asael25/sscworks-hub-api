<?php

namespace App\Models;

use Carbon\Carbon;
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
        'unread_counts' => 'array',
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id', 'id')->orderBy('sent_at', 'asc');
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id', 'id')->latest('sent_at');
    }

    public function getUnreadCountForUser(?string $userId = null): int
    {
        if (!$userId) {
            return $this->unread_count ?? 0;
        }

        $counts = $this->unread_counts ?? [];
        if (isset($counts[$userId])) {
            return (int) $counts[$userId];
        }

        // If user is the last sender, unread is 0
        $lastMsg = $this->latestMessage;
        if ($lastMsg && $lastMsg->sender_id === $userId) {
            return 0;
        }

        return 0;
    }

    public function incrementUnreadForRecipients(string $senderId): void
    {
        $counts = $this->unread_counts ?? [];
        $members = $this->member_ids ?? [];

        // Ensure sender's unread is 0
        $counts[$senderId] = 0;

        // Increment for all other members
        foreach ($members as $memberId) {
            if ($memberId !== $senderId) {
                $counts[$memberId] = ($counts[$memberId] ?? 0) + 1;
            }
        }

        $this->unread_counts = $counts;
        $this->unread_count = max(0, ...array_values($counts ?: [0]));
        $this->save();
    }

    public function markAsReadForUser(string $userId): void
    {
        $counts = $this->unread_counts ?? [];
        $counts[$userId] = 0;

        $this->unread_counts = $counts;
        $this->unread_count = max(0, ...array_values($counts ?: [0]));
        $this->save();
    }

    public function toFrontendSummary(?string $userId = null): array
    {
        $lastMsg = $this->latestMessage;
        $lastActivity = $lastMsg && $lastMsg->sent_at
            ? ($this->updated_at && $this->updated_at->isAfter($lastMsg->sent_at) ? $this->updated_at : $lastMsg->sent_at)
            : ($this->updated_at ?: $this->created_at ?: now());

        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'unit' => $this->unit,
            'ownerId' => $this->owner_id,
            'memberIds' => $this->member_ids ?? [],
            'relatedRequestIds' => $this->related_request_ids ?? [],
            'unreadCount' => $this->getUnreadCountForUser($userId),
            'unreadCounts' => $this->unread_counts ?? [],
            'messageCount' => $this->messages()->count(),
            'updatedAt' => $this->updated_at ? $this->updated_at->toISOString() : null,
            'createdAt' => $this->created_at ? $this->created_at->toISOString() : null,
            'lastActivityAt' => $lastActivity ? Carbon::parse($lastActivity)->toISOString() : null,
        ];
    }
}
