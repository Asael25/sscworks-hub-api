<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    protected $table = 'approval_workflows';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'module',
        'process',
        'mode',
        'status',
    ];

    public function stages()
    {
        return $this->hasMany(ApprovalWorkflowStage::class, 'workflow_id', 'id')->orderBy('order', 'asc');
    }
}
