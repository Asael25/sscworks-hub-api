<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConstructionWork;
use App\Models\ConstructionScope;
use App\Models\ConstructionAttachment;
use App\Models\Approval;
use App\Models\ApprovalStage;
use App\Models\ApprovalHistory;
use App\Services\WorkflowEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConstructionController extends Controller
{
    public function index(Request $request)
    {
        $query = ConstructionWork::with(['scopes', 'weeklyProgresses', 'issues', 'photos', 'approvals', 'attachments'])
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

        $works = $query->get();

        $formatted = $works->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'unit' => $item->unit,
                'location' => $item->location,
                'vendor' => $item->vendor,
                'contractNumber' => $item->contract_number,
                'pic' => $item->pic,
                'startDate' => $item->start_date,
                'targetDate' => $item->target_date,
                'period' => $item->period,
                'target' => (float) $item->target,
                'actual' => (float) $item->actual,
                'status' => $item->status,
                'budget' => (float) $item->budget,
                'notes' => $item->notes ?? '',
                'scopes' => $item->scopes->map(function ($s) {
                    return [
                        'id' => (string) $s->id,
                        'scope' => $s->scope,
                        'quantity' => (string) ($s->quantity ?? '1'),
                        'unitOfMeasure' => (string) ($s->unit_of_measure ?? 'ls'),
                        'weight' => (float) $s->weight,
                        'target' => (float) $s->target,
                        'actual' => (float) $s->actual,
                    ];
                }),
                'attachments' => $item->attachments->map(function ($a) {
                    return [
                        'id' => (string) $a->id,
                        'file_name' => $a->file_name,
                        'file_type' => $a->file_type,
                        'file_size_kb' => (int) $a->file_size_kb,
                        'file_url' => $a->file_url,
                        'uploaded_by' => $a->uploaded_by,
                    ];
                }),
                'weekly' => $item->weeklyProgresses->map(function ($w) {
                    return [
                        'period' => $w->period,
                        'target' => (float) $w->target,
                        'actual' => (float) $w->actual,
                        'reportId' => $w->report_id,
                        'status' => $w->status,
                    ];
                }),
                'quality' => [
                    'checked' => (int) $item->quality_checked,
                    'passed' => (int) $item->quality_passed,
                    'reworks' => (int) $item->quality_reworks,
                    'note' => $item->quality_note ?? '',
                ],
                'safety' => [
                    'manhours' => (int) $item->safety_manhours,
                    'incidents' => (int) $item->safety_incidents,
                    'nearMiss' => (int) $item->safety_near_miss,
                    'toolbox' => (int) $item->safety_toolbox,
                ],
                'issues' => $item->issues->map(function ($iss) {
                    return [
                        'title' => $iss->title,
                        'severity' => $iss->severity,
                        'raisedBy' => $iss->raised_by,
                        'date' => $iss->date,
                        'status' => $iss->status,
                    ];
                }),
                'photos' => $item->photos->map(function ($p) {
                    return [
                        'caption' => $p->caption,
                        'date' => $p->date,
                        'tone' => $p->tone,
                    ];
                }),
                'approvals' => $item->approvals->map(function ($app) {
                    return [
                        'stage' => $app->stage,
                        'approver' => $app->approver,
                        'date' => $app->date,
                        'state' => $app->state,
                    ];
                }),
            ];
        });

        return response()->json($formatted);
    }

    public function store(Request $request)
    {
        $isDraft = $request->boolean('isDraft');

        $rules = $isDraft ? [
            'name'           => 'nullable|string',
            'unit'           => 'nullable|string',
            'location'       => 'nullable|string',
            'vendor'         => 'nullable|string',
            'contractNumber' => 'nullable|string',
            'pic'            => 'nullable|string',
            'startDate'      => 'nullable|string',
            'targetDate'     => 'nullable|string',
            'budget'         => 'nullable|numeric',
            'notes'          => 'nullable|string',
            'scopes'         => 'nullable|array',
            'attachments'    => 'nullable|array',
            'isDraft'        => 'nullable|boolean',
        ] : [
            'name'           => 'required|string',
            'unit'           => 'required|string',
            'location'       => 'required|string',
            'vendor'         => 'required|string',
            'contractNumber' => 'required|string',
            'pic'            => 'required|string',
            'startDate'      => 'required|string',
            'targetDate'     => 'required|string',
            'budget'         => 'nullable|numeric',
            'notes'          => 'nullable|string',
            'scopes'         => 'nullable|array',
            'attachments'    => 'nullable|array',
            'isDraft'        => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'error'    => 'Validation error',
                'messages' => $validator->errors()->all(),
            ], 422);
        }

        $validated = $validator->validated();

        $year = date('Y');
        $seq  = str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $workId = "KI-{$year}-{$seq}";

        $name = !empty($validated['name']) ? $validated['name'] : 'Draft Pekerjaan Internal (' . date('d/m/Y H:i') . ')';
        $unit = !empty($validated['unit']) ? $validated['unit'] : 'SSC Samarinda';
        $location = !empty($validated['location']) ? $validated['location'] : 'Lokasi Draft';
        $vendor = !empty($validated['vendor']) ? $validated['vendor'] : 'Vendor Draft';
        $contractNumber = !empty($validated['contractNumber']) ? $validated['contractNumber'] : 'SPK-DRAFT-' . rand(100, 999);
        $pic = !empty($validated['pic']) ? $validated['pic'] : 'PIC (Draft)';
        $startDate = !empty($validated['startDate']) ? $validated['startDate'] : date('Y-m-d');
        $targetDate = !empty($validated['targetDate']) ? $validated['targetDate'] : date('Y-m-d', strtotime('+30 days'));
        $status = $isDraft ? 'Draft' : 'Waiting Approval';

        $work = ConstructionWork::create([
            'id'              => $workId,
            'name'            => $name,
            'unit'            => $unit,
            'location'        => $location,
            'vendor'          => $vendor,
            'contract_number' => $contractNumber,
            'pic'             => $pic,
            'start_date'      => $startDate,
            'target_date'     => $targetDate,
            'period'          => date('M Y'),
            'target'          => 0,
            'actual'          => 0,
            'status'          => $status,
            'budget'          => $validated['budget'] ?? 0,
            'notes'           => $validated['notes'] ?? null,
        ]);

        // Simpan Scopes jika ada
        if (!empty($validated['scopes'])) {
            foreach ($validated['scopes'] as $sc) {
                if (!empty($sc['scope'])) {
                    ConstructionScope::create([
                        'work_id'         => $workId,
                        'scope'           => $sc['scope'],
                        'quantity'        => (string) ($sc['quantity'] ?? '1'),
                        'unit_of_measure' => (string) ($sc['unitOfMeasure'] ?? $sc['unit'] ?? 'ls'),
                        'weight'          => (float) ($sc['weight'] ?? 0),
                        'target'          => (float) ($sc['weeklyTarget'] ?? $sc['target'] ?? 0),
                        'actual'          => 0,
                    ]);
                }
            }
        }

        // Simpan Attachments jika ada
        if (!empty($validated['attachments'])) {
            foreach ($validated['attachments'] as $att) {
                ConstructionAttachment::create([
                    'work_id'      => $workId,
                    'file_name'    => $att['name'] ?? 'attachment.pdf',
                    'file_type'    => strtoupper(pathinfo($att['name'] ?? 'PDF', PATHINFO_EXTENSION) ?: 'PDF'),
                    'file_size_kb' => (int) ($att['sizeKb'] ?? 100),
                    'file_url'     => $att['url'] ?? null,
                    'uploaded_by'  => $pic,
                    'uploaded_at'  => date('Y-m-d'),
                ]);
            }
        }

        if (!$isDraft) {
            // ─── Auto-create global Approval Center record ───────────────────
            $approvalId = $workId;

            Approval::create([
                'id'              => $approvalId,
                'module'          => 'Konstruksi Internal',
                'request'         => $name,
                'summary'         => "Pekerjaan konstruksi di {$location} oleh {$vendor}.",
                'unit'            => $unit,
                'submitted_by'    => $pic,
                'submitted_by_id' => strtolower(str_replace(' ', '', $pic)),
                'submitted_date'  => date('d M Y'),
                'period'          => date('M Y'),
                'amount'          => $validated['budget'] ?? null,
                'amount_label'    => $validated['budget'] ? 'Rp ' . number_format($validated['budget'], 0, ',', '.') : '-',
                'current_stage'   => 1,
                'priority'        => 'Medium',
                'status'          => 'Waiting',
                'due_date'        => $targetDate,
                'notes'           => $validated['notes'] ?? null,
            ]);

            WorkflowEngine::attachStagesToApproval(
                $approvalId,
                'Internal Construction',
                'Master Pekerjaan',
                (float) ($validated['contractValue'] ?? 0),
                $unit,
                [
                    ['order' => 1, 'name' => 'Review Manajer Proyek', 'role' => 'Manajer Proyek', 'unit_scope' => $unit],
                    ['order' => 2, 'name' => 'Persetujuan Direktur', 'role' => 'Direktur', 'unit_scope' => $unit],
                ]
            );

            ApprovalHistory::create([
                'approval_id' => $approvalId,
                'actor'       => $pic,
                'role'        => 'PIC Proyek',
                'action'      => 'Pengajuan Dikirim',
                'note'        => 'Pekerjaan konstruksi internal berhasil diajukan dan menunggu review.',
                'at'          => date('d M Y, H:i'),
            ]);
            // ─────────────────────────────────────────────────────────────────
        }

        return response()->json([
            'workId'      => $workId,
            'submittedAt' => date('c'),
            'message'     => $isDraft ? 'Draft Pekerjaan Internal tersimpan di database.' : 'New Construction Work stored successfully.',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $work = ConstructionWork::find($id);
        if (!$work) {
            return response()->json(['error' => 'Construction work not found.'], 404);
        }

        $isDraft = $request->boolean('isDraft');

        $rules = $isDraft ? [
            'name'           => 'nullable|string',
            'unit'           => 'nullable|string',
            'location'       => 'nullable|string',
            'vendor'         => 'nullable|string',
            'contractNumber' => 'nullable|string',
            'pic'            => 'nullable|string',
            'startDate'      => 'nullable|string',
            'targetDate'     => 'nullable|string',
            'budget'         => 'nullable|numeric',
            'notes'          => 'nullable|string',
            'scopes'         => 'nullable|array',
            'attachments'    => 'nullable|array',
            'isDraft'        => 'nullable|boolean',
        ] : [
            'name'           => 'required|string',
            'unit'           => 'required|string',
            'location'       => 'required|string',
            'vendor'         => 'required|string',
            'contractNumber' => 'required|string',
            'pic'            => 'required|string',
            'startDate'      => 'required|string',
            'targetDate'     => 'required|string',
            'budget'         => 'nullable|numeric',
            'notes'          => 'nullable|string',
            'scopes'         => 'nullable|array',
            'attachments'    => 'nullable|array',
            'isDraft'        => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'error'    => 'Validation error',
                'messages' => $validator->errors()->all(),
            ], 422);
        }

        $validated = $validator->validated();

        $status = $isDraft ? 'Draft' : 'Waiting Approval';

        $name = !empty($validated['name']) ? $validated['name'] : $work->name;
        $unit = !empty($validated['unit']) ? $validated['unit'] : $work->unit;
        $location = !empty($validated['location']) ? $validated['location'] : $work->location;
        $vendor = !empty($validated['vendor']) ? $validated['vendor'] : $work->vendor;
        $contractNumber = !empty($validated['contractNumber']) ? $validated['contractNumber'] : $work->contract_number;
        $pic = !empty($validated['pic']) ? $validated['pic'] : $work->pic;
        $startDate = !empty($validated['startDate']) ? $validated['startDate'] : $work->start_date;
        $targetDate = !empty($validated['targetDate']) ? $validated['targetDate'] : $work->target_date;
        $budget = array_key_exists('budget', $validated) && $validated['budget'] !== null ? $validated['budget'] : $work->budget;
        $notes = array_key_exists('notes', $validated) ? $validated['notes'] : $work->notes;

        $work->update([
            'name'            => $name,
            'unit'            => $unit,
            'location'        => $location,
            'vendor'          => $vendor,
            'contract_number' => $contractNumber,
            'pic'             => $pic,
            'start_date'      => $startDate,
            'target_date'     => $targetDate,
            'status'          => $status,
            'budget'          => $budget,
            'notes'           => $notes,
        ]);

        // Perbarui Scopes
        if (isset($validated['scopes'])) {
            ConstructionScope::where('work_id', $work->id)->delete();
            foreach ($validated['scopes'] as $sc) {
                if (!empty($sc['scope'])) {
                    ConstructionScope::create([
                        'work_id'         => $work->id,
                        'scope'           => $sc['scope'],
                        'quantity'        => (string) ($sc['quantity'] ?? '1'),
                        'unit_of_measure' => (string) ($sc['unitOfMeasure'] ?? $sc['unit'] ?? 'ls'),
                        'weight'          => (float) ($sc['weight'] ?? 0),
                        'target'          => (float) ($sc['weeklyTarget'] ?? $sc['target'] ?? 0),
                        'actual'          => 0,
                    ]);
                }
            }
        }

        // Perbarui Attachments
        if (isset($validated['attachments'])) {
            ConstructionAttachment::where('work_id', $work->id)->delete();
            foreach ($validated['attachments'] as $att) {
                ConstructionAttachment::create([
                    'work_id'      => $work->id,
                    'file_name'    => $att['name'] ?? 'attachment.pdf',
                    'file_type'    => strtoupper(pathinfo($att['name'] ?? 'PDF', PATHINFO_EXTENSION) ?: 'PDF'),
                    'file_size_kb' => (int) ($att['sizeKb'] ?? 100),
                    'file_url'     => $att['url'] ?? null,
                    'uploaded_by'  => $pic,
                    'uploaded_at'  => date('Y-m-d'),
                ]);
            }
        }

        if (!$isDraft) {
            $approvalId = $work->id;
            $existingApp = Approval::find($approvalId);
            if (!$existingApp) {
                Approval::create([
                    'id'              => $approvalId,
                    'module'          => 'Konstruksi Internal',
                    'request'         => $name,
                    'summary'         => "Pekerjaan konstruksi di {$location} oleh {$vendor}.",
                    'unit'            => $unit,
                    'submitted_by'    => $pic,
                    'submitted_by_id' => strtolower(str_replace(' ', '', $pic)),
                    'submitted_date'  => date('d M Y'),
                    'period'          => date('M Y'),
                    'amount'          => $budget ?? null,
                    'amount_label'    => $budget ? 'Rp ' . number_format($budget, 0, ',', '.') : '-',
                    'current_stage'   => 1,
                    'priority'        => 'Medium',
                    'status'          => 'Waiting',
                    'due_date'        => $targetDate,
                    'notes'           => $notes,
                ]);

                WorkflowEngine::attachStagesToApproval(
                    $approvalId,
                    'Internal Construction',
                    'Master Pekerjaan',
                    (float) ($contractValue ?? 0),
                    $unit,
                    [
                        ['order' => 1, 'name' => 'Review Manajer Proyek', 'role' => 'Manajer Proyek', 'unit_scope' => $unit],
                        ['order' => 2, 'name' => 'Persetujuan Direktur', 'role' => 'Direktur', 'unit_scope' => $unit],
                    ]
                );

                ApprovalHistory::create([
                    'approval_id' => $approvalId,
                    'actor'       => $pic,
                    'role'        => 'PIC Proyek',
                    'action'      => 'Pengajuan Dikirim dari Draft',
                    'note'        => 'Pekerjaan konstruksi internal berhasil diajukan dari draf.',
                    'at'          => date('d M Y, H:i'),
                ]);
            }
        }

        return response()->json([
            'workId'      => $work->id,
            'submittedAt' => date('c'),
            'message'     => $isDraft ? 'Draft berhasil diperbarui.' : 'Pekerjaan internal berhasil diajukan ke antrean approval.',
        ], 200);
    }
}
