<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConstructionApproval extends Model
{
    protected $table = 'construction_approvals';
    protected $fillable = ['work_id', 'stage', 'approver', 'date', 'state'];

    public function work()
    {
        return $this->belongsTo(ConstructionWork::class, 'work_id', 'id');
    }
}
