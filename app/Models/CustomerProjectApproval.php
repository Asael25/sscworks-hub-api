<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProjectApproval extends Model
{
    protected $table = 'customer_project_approvals';
    protected $fillable = ['project_id', 'stage', 'approver', 'date', 'state'];

    public function project()
    {
        return $this->belongsTo(CustomerProject::class, 'project_id', 'id');
    }
}
