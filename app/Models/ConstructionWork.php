<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConstructionWork extends Model
{
    use HasFactory;

    protected $table = 'construction_works';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'unit', 'location', 'vendor', 'contract_number',
        'pic', 'start_date', 'target_date', 'period', 'target', 'actual',
        'status', 'budget', 'notes', 'quality_checked', 'quality_passed',
        'quality_reworks', 'quality_note', 'safety_manhours',
        'safety_incidents', 'safety_near_miss', 'safety_toolbox'
    ];

    public function scopes()
    {
        return $this->hasMany(ConstructionScope::class, 'work_id', 'id');
    }

    public function attachments()
    {
        return $this->hasMany(ConstructionAttachment::class, 'work_id', 'id');
    }

    public function weeklyProgresses()
    {
        return $this->hasMany(ConstructionWeeklyProgress::class, 'work_id', 'id');
    }

    public function issues()
    {
        return $this->hasMany(ConstructionIssue::class, 'work_id', 'id');
    }

    public function photos()
    {
        return $this->hasMany(ConstructionPhoto::class, 'work_id', 'id');
    }

    public function approvals()
    {
        return $this->hasMany(ConstructionApproval::class, 'work_id', 'id');
    }

    public function addenda()
    {
        return $this->hasMany(ContractAddendum::class, 'target_item_id', 'id')->orderBy('addendum_number', 'asc');
    }
}
