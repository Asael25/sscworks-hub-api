<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'additional_units' => 'array',
            'menu_access' => 'array',
            'authorities' => 'array',
            'eula_agreed' => 'boolean',
            'eula_agreed_at' => 'datetime',
            'last_active_at' => 'datetime',
        ];
    }

    public function getChatUserIdAttribute(): string
    {
        $uname = strtolower($this->username);
        if ($uname === 'sultan' || $uname === 'andi') {
            return 'u-andi';
        }
        return 'u-' . $uname;
    }

    public const ONLINE_THRESHOLD_SECONDS = 10;

    public function getIsOnlineAttribute(): bool
    {
        if (!$this->last_active_at) {
            return false;
        }
        return $this->last_active_at->isAfter(\Carbon\Carbon::now()->subSeconds(self::ONLINE_THRESHOLD_SECONDS));
    }

    public function touchActive(): void
    {
        $this->update(['last_active_at' => \Carbon\Carbon::now()]);
    }

    public static function findByChatUserId(?string $chatId): ?User
    {
        if (!$chatId) return null;
        $clean = strtolower($chatId);
        if (str_starts_with($clean, 'u-')) {
            $clean = substr($clean, 2);
        }
        if ($clean === 'andi' || $clean === 'sultan') {
            return self::whereRaw('LOWER(username) IN ("sultan", "andi")')->first();
        }
        return self::whereRaw('LOWER(username) = ?', [$clean])
                   ->orWhereRaw('LOWER(identifier) = ?', [$clean])
                   ->first();
    }
}

