<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalRequest extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    public function attachments(): HasMany
    {
        return $this->hasMany(LegalAttachment::class, 'request_id', 'id');
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(LegalTimelineEvent::class, 'request_id', 'id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LegalNote::class, 'request_id', 'id');
    }

    public function outputDocuments(): HasMany
    {
        return $this->hasMany(LegalOutputDocument::class, 'request_id', 'id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LegalApproval::class, 'request_id', 'id');
    }
}
