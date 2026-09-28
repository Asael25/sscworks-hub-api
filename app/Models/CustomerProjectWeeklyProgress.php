<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProjectWeeklyProgress extends Model
{
    protected $table = 'customer_project_weekly_progresses';
    protected $fillable = ['project_id', 'period', 'target', 'actual', 'note', 'reported_by'];

    public function project()
    {
        return $this->belongsTo(CustomerProject::class, 'project_id', 'id');
    }
}
