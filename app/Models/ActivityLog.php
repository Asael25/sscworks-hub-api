<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Map database row to frontend ActivityLogItem format
     */
    public function toFrontendFormat(): array
    {
        $meta = $this->metadata ?? [];

        $rawMod = (string)$this->module;
        $modCode = 'LO';
        $modLabel = 'Legal Operations (LO)';

        if (stripos($rawMod, 'Legal') !== false || strtoupper($rawMod) === 'LO') {
            $modCode = 'LO';
            $modLabel = 'Legal Operations (LO)';
        } elseif (stripos($rawMod, 'Construction') !== false || strtoupper($rawMod) === 'IC') {
            $modCode = 'IC';
            $modLabel = 'Internal Construction (IC)';
        } elseif (stripos($rawMod, 'Project') !== false || stripos($rawMod, 'Customer') !== false || strtoupper($rawMod) === 'CP') {
            $modCode = 'CP';
            $modLabel = 'Customer Project (CP)';
        } elseif (stripos($rawMod, 'Approval') !== false) {
            $modCode = 'Approval';
            $modLabel = 'Approval Center';
        } elseif (stripos($rawMod, 'Budget') !== false) {
            $modCode = 'Budgeting';
            $modLabel = 'Budgeting System';
        } elseif (stripos($rawMod, 'Cashflow') !== false) {
            $modCode = 'Cashflow';
            $modLabel = 'Cashflow Management';
        }

        $target = $this->entity_id && $this->entity_title 
            ? "{$this->entity_id} · {$this->entity_title}"
            : ($this->entity_title ?: ($this->entity_id ?: $this->action));

        $statusType = strtolower((string)$this->action_type);
        if ($statusType === 'create') $statusType = 'submitted';
        if ($statusType === 'approve') $statusType = 'approved';
        if ($statusType === 'upload') $statusType = 'updated';

        $hrefMap = [
            'LO' => '/legal',
            'IC' => '/internal-construction',
            'CP' => '/customer-project',
            'Approval' => '/approval-center',
            'Budgeting' => '/budgeting',
            'Cashflow' => '/cashflow',
        ];

        return [
            'id' => 'act-db-' . $this->id,
            'module' => $modCode,
            'moduleLabel' => $modLabel,
            'action' => $this->action,
            'target' => $target,
            'targetCode' => $this->entity_id,
            'targetTitle' => $this->entity_title,
            'detail' => $this->description,
            'actor' => [
                'name' => $this->user_name,
                'role' => $this->user_role,
                'email' => $meta['email'] ?? null,
                'unit' => $this->user_unit,
                'avatar' => $meta['avatar'] ?? null,
            ],
            'timestamp' => $this->created_at ? $this->created_at->toISOString() : now()->toISOString(),
            'statusType' => $statusType ?: 'info',
            'href' => $meta['href'] ?? ($hrefMap[$modCode] ?? '/'),
        ];
    }
}
