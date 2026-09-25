<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Approval extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    public function stages(): HasMany
    {
        return $this->hasMany(ApprovalStage::class, 'approval_id', 'id')->orderBy('order');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ApprovalAttachment::class, 'approval_id', 'id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class, 'approval_id', 'id');
    }
}
