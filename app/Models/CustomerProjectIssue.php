<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProjectIssue extends Model
{
    protected $table = 'customer_project_issues';
    protected $fillable = ['project_id', 'title', 'severity', 'action', 'owner', 'date', 'status'];

    public function project()
    {
        return $this->belongsTo(CustomerProject::class, 'project_id', 'id');
    }
}
