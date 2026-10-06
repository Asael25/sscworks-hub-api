<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $query = Approval::with(['stages', 'attachments', 'history'])
            ->orderBy('created_at', 'desc');

        $identifier = $request->header('X-User-Identifier');
        if ($identifier) {
            $user = \App\Models\User::whereRaw('LOWER(identifier) = ?', [strtolower($identifier)])
                ->orWhereRaw('LOWER(username) = ?', [strtolower($identifier)])
                ->first();
            if ($user) {
                $additional = is_array($user->additional_units)
                    ? $user->additional_units
                    : json_decode($user->additional_units ?? '[]', true);

                $isMultiUnitAdmin = in_array($user->role, ['Super Admin', 'Admin Super'])
                    || strtolower($user->identifier) === 'rudi@sscworks.com'
                    || strtolower($user->username ?? '') === 'rudi'
                    || (is_array($additional) && in_array('*', $additional));

                if (!$isMultiUnitAdmin) {
                    $allowedUnits = array_filter(array_merge([$user->primary_unit], $additional ?? []));
                    
                    $query->where(function ($q) use ($allowedUnits) {
                        foreach ($allowedUnits as $unit) {
                            if (!$unit || $unit === '*') continue;
                            $q->orWhere('unit', $unit);
                            
                            $dashHyphen = str_replace('—', '-', $unit);
                            $dashEm = str_replace('-', '—', $unit);
                            $q->orWhere('unit', $dashHyphen)
                              ->orWhere('unit', $dashEm);
                              
                            $clean = trim(str_replace(['SSC', 'Works', 'Cabang', '—', '-'], ' ', $unit));
                            if (!empty($clean)) {
                                $q->orWhere('unit', 'LIKE', '%' . $clean . '%');
                            }
                        }
                    });
                }
            }
        }

        $approvals = $query->get();

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
                        'url' => $att->file_url,
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
