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
}

