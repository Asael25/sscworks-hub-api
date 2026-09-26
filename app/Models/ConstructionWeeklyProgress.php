<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConstructionWeeklyProgress extends Model
{
    protected $table = 'construction_weekly_progresses';
    protected $fillable = ['work_id', 'period', 'target', 'actual', 'report_id', 'status'];

    public function work()
    {
        return $this->belongsTo(ConstructionWork::class, 'work_id', 'id');
    }
}
