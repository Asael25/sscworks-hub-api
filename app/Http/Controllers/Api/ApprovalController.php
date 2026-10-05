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
            $user = \App\Models\User::where('identifier', $identifier)->first();
            if ($user) {
                // User dengan role IT / Super Admin atau multi-unit memiliki akses penuh ke seluruh cabang/unit
                $isMultiUnitAdmin = in_array($user->role, [
                    'IT & Bussiness System Support',
                    'Admin Super',
                ]) || (is_array($user->additional_units) && in_array('*', $user->additional_units));

                if (!$isMultiUnitAdmin) {
                    // Batasi hanya unit utama (primary_unit) dan unit tambahan (additional_units)
                    $allowedUnits = array_filter(array_merge([$user->primary_unit], $user->additional_units ?? []));
                    
                    $query->where(function ($q) use ($allowedUnits) {
                        foreach ($allowedUnits as $unit) {
                            if (!$unit) continue;
                            $q->orWhere('unit', $unit);
                            
                            // Normalisasi dash (-) dan em-dash (—)
                            $dashHyphen = str_replace('—', '-', $unit);
                            $dashEm = str_replace('-', '—', $unit);
                            $q->orWhere('unit', $dashHyphen)
                              ->orWhere('unit', $dashEm);
                              
                            // Match kata kunci cabang (misal: "Head Office", "Surabaya", "Balikpapan")
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
