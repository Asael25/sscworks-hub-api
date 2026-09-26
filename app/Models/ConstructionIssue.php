<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConstructionIssue extends Model
{
    protected $table = 'construction_issues';
    protected $fillable = ['work_id', 'title', 'severity', 'raised_by', 'date', 'status'];

    public function work()
    {
        return $this->belongsTo(ConstructionWork::class, 'work_id', 'id');
    }
}
