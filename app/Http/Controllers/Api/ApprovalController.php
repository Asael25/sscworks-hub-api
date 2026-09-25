<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index()
    {
        $approvals = Approval::with(['stages', 'attachments', 'history'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Format data sesuai ekspektasi TypeScript Interface Frontend (camelCase)
        $formatted = $approvals->map(function ($item) {
            return [
                'id' => $item->id,
                'module' => $item->module,
                'request' => $item->request,
                'summary' => $item->summary,
                'unit' => $item->unit,
                'submittedBy' => $item->submitted_by,
                'submittedById' => $item->submitted_by_id,
                'submittedDate' => $item->submitted_date,
                'period' => $item->period,
                'amount' => $item->amount ? (float) $item->amount : null,
                'amountLabel' => $item->amount_label,
                'stages' => $item->stages->map(function ($stg) {
                    return [
                        'order' => $stg->order,
                        'name' => $stg->name,
                        'role' => $stg->role,
                        'approver' => $stg->approver,
                        'unitScope' => $stg->unit_scope,
                    ];
                }),
                'currentStage' => $item->current_stage,
                'priority' => $item->priority,
                'status' => $item->status,
                'dueDate' => $item->due_date,
                'attachments' => $item->attachments->map(function ($att) {
                    return [
                        'name' => $att->name,
                        'size' => $att->size,
                        'type' => $att->type,
                    ];
                }),
                'history' => $item->history->map(function ($hst) {
                    return [
                        'actor' => $hst->actor,
                        'role' => $hst->role,
                        'action' => $hst->action,
                        'note' => $hst->note,
                        'at' => $hst->at,
                    ];
                }),
                'notes' => $item->notes ?? '',
            ];
        });

        return response()->json($formatted);
    }
}
