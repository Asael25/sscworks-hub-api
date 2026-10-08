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
                        'url' => $att->file_url ?? $att->file_path,
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

    public function decide(Request $request, $id)
    {
        $approval = Approval::with(['stages', 'attachments', 'history'])->find($id);
        if (!$approval) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'decision' => 'required|in:approve,return,reject',
            'reason' => 'nullable|string',
        ]);

        $decision = $validated['decision'];
        $reason = $validated['reason'] ?? null;

        $identifier = $request->header('X-User-Identifier');
        $user = null;
        if ($identifier) {
            $user = \App\Models\User::whereRaw('LOWER(identifier) = ?', [strtolower($identifier)])
                ->orWhereRaw('LOWER(username) = ?', [strtolower($identifier)])
                ->first();
        }

        $actorName = $user ? $user->name : ($request->input('actor') ?: 'User');
        $actorRole = $user ? $user->role : ($request->input('role') ?: 'Approver');

        // Check if already finalized
        if (in_array($approval->status, ['Approved', 'Rejected'])) {
            return response()->json(['message' => 'Pengajuan ini sudah difinalisasi.'], 422);
        }

        $stages = $approval->stages()->orderBy('order')->get();
        $currentStage = $stages->firstWhere('order', $approval->current_stage) ?? $stages->first();

        // Check approver assignment if not superadmin
        if ($currentStage && $user && $user->role !== 'Super Admin') {
            if ($currentStage->approver) {
                $targetApprover = strtolower(trim($currentStage->approver));
                $isNameMatch = strtolower(trim($user->name)) === $targetApprover;
                $isUsernameMatch = strtolower(trim($user->username ?? '')) === $targetApprover;
                $isEmailMatch = strtolower(trim($user->identifier ?? '')) === $targetApprover;
                if (!$isNameMatch && !$isUsernameMatch && !$isEmailMatch) {
                    return response()->json(['message' => "Tahap aktif ditugaskan kepada {$currentStage->approver}."], 422);
                }
            }
            if (!$currentStage->approver && $currentStage->role !== $user->role) {
                return response()->json(['message' => "Tahap aktif ({$currentStage->name}) memerlukan role {$currentStage->role}."], 422);
            }
        }

        $totalStages = $stages->count() ?: 1;
        $actionLabel = 'Approved';

        if ($decision === 'approve') {
            $actionLabel = 'Approved';
            if ($approval->current_stage < $totalStages) {
                $approval->current_stage += 1;
                $approval->status = 'In Review';
            } else {
                $approval->status = 'Approved';

                // Sync parent record
                if (in_array($approval->module, ['Project Customer', 'Customer Project'])) {
                    if (str_starts_with($approval->id, 'CSR-') || str_contains($approval->request, 'Site Report')) {
                        $projectName = trim(explode('·', $approval->request)[1] ?? '');
                        $project = \App\Models\CustomerProject::where('name', $projectName)->first();

                        $actual = 0;
                        $target = 0;
                        $period = date('Y-m-d');
                        $reportedBy = $approval->submitted_by;

                        if (preg_match('/Progres Realisasi (\d+(\.\d+)?)%/', $approval->summary, $mActual)) {
                            $actual = (float) $mActual[1];
                        }
                        if (preg_match('/Target (\d+(\.\d+)?)%/', $approval->summary, $mTarget)) {
                            $target = (float) $mTarget[1];
                        }
                        if (preg_match('/Laporan Mingguan \(([^)]+)\)/', $approval->summary, $mPeriod)) {
                            $period = $mPeriod[1];
                        }

                        if ($project) {
                            $project->update([
                                'actual' => $actual,
                                'status' => $actual >= 100 ? 'Completed' : 'On Schedule',
                            ]);

                            \App\Models\CustomerProjectWeeklyProgress::updateOrCreate(
                                [
                                    'project_id' => $project->id,
                                    'period'     => $period,
                                ],
                                [
                                    'target'      => $target,
                                    'actual'      => $actual,
                                    'note'        => $approval->notes ?? "Site report mingguan disetujui.",
                                    'reported_by' => $reportedBy,
                                ]
                            );
                        }
                    } else {
                        \App\Models\CustomerProject::where('id', $approval->id)->update(['status' => 'On Schedule']);
                    }
                } elseif (in_array($approval->module, ['Internal Construction', 'Konstruksi Internal', 'Construction'])) {
                    \App\Models\ConstructionWork::where('id', $approval->id)->update(['status' => 'On Schedule']);
                } elseif (in_array($approval->module, ['Legal Operations', 'Legal'])) {
                    \App\Models\LegalRequest::where('request_code', $approval->id)->orWhere('id', $approval->id)->update(['status' => 'completed']);
                }
            }
        } elseif ($decision === 'return') {
            $actionLabel = 'Returned';
            $approval->status = 'Returned';
        } else {
            $actionLabel = 'Rejected';
            $approval->status = 'Rejected';
        }

        $approval->save();

        // Record history
        \App\Models\ApprovalHistory::create([
            'approval_id' => $approval->id,
            'actor' => $actorName,
            'role' => $actorRole,
            'action' => $actionLabel,
            'note' => $reason,
            'at' => now()->translatedFormat('d M Y, H:i'),
        ]);

        $fresh = $approval->fresh(['stages', 'attachments', 'history']);

        $formatted = [
            'id' => $fresh->id,
            'module' => $fresh->module,
            'request' => $fresh->request,
            'summary' => $fresh->summary,
            'unit' => $fresh->unit,
            'submittedBy' => $fresh->submitted_by,
            'submittedById' => $fresh->submitted_by_id,
            'submittedDate' => $fresh->submitted_date,
            'period' => $fresh->period,
            'amount' => $fresh->amount ? (float) $fresh->amount : null,
            'amountLabel' => $fresh->amount_label,
            'stages' => $fresh->stages->map(function ($stg) {
                return [
                    'order' => $stg->order,
                    'name' => $stg->name,
                    'role' => $stg->role,
                    'approver' => $stg->approver,
                    'unitScope' => $stg->unit_scope,
                ];
            }),
            'currentStage' => $fresh->current_stage,
            'priority' => $fresh->priority,
            'status' => $fresh->status,
            'dueDate' => $fresh->due_date,
            'attachments' => $fresh->attachments->map(function ($att) {
                return [
                    'name' => $att->name,
                    'size' => $att->size,
                    'type' => $att->type,
                    'url' => $att->file_url,
                ];
            }),
            'history' => $fresh->history->map(function ($hst) {
                return [
                    'actor' => $hst->actor,
                    'role' => $hst->role,
                    'action' => $hst->action,
                    'note' => $hst->note,
                    'at' => $hst->at,
                ];
            }),
            'notes' => $fresh->notes ?? '',
        ];

        return response()->json([
            'message' => "Pengajuan berhasil di-{$actionLabel}.",
            'item' => $formatted
        ]);
    }
}
