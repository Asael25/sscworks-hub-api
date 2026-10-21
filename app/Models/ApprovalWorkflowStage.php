<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflowStage extends Model
{
    protected $table = 'approval_workflow_stages';

    protected $fillable = [
        'workflow_id',
        'order',
        'stage_level',
        'stage_name',
        'is_sequential',
        'sub_order',
        'role',
        'approver',
        'unit_scope',
        'threshold',
        'required',
    ];

    protected $casts = [
        'order'         => 'integer',
        'stage_level'   => 'integer',
        'is_sequential' => 'boolean',
        'sub_order'     => 'integer',
        'threshold'     => 'float',
        'required'      => 'boolean',
    ];

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id', 'id');
    }
}
