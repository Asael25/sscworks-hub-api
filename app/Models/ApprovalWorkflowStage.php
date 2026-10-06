<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflowStage extends Model
{
    protected $table = 'approval_workflow_stages';

    protected $fillable = [
        'workflow_id',
        'order',
        'role',
        'approver',
        'unit_scope',
        'threshold',
        'required',
    ];

    protected $casts = [
        'order'     => 'integer',
        'threshold' => 'float',
        'required'  => 'boolean',
    ];

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id', 'id');
    }
}
