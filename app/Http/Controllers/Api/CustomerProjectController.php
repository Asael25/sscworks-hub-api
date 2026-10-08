<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerProject;
use App\Models\CustomerProjectAttachment;
use App\Models\Approval;
use App\Models\ApprovalStage;
use App\Models\ApprovalHistory;
use App\Services\WorkflowEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerProject::with(['scopes', 'weeklyProgresses', 'issues', 'photos', 'approvals', 'attachments'])
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

        $projects = $query->get();

        $formatted = $projects->map(function ($item) {
            return [
                'id' => $item->id,
                'customer' => $item->customer,
                'name' => $item->name,
                'contractNumber' => $item->contract_number,
                'poNumber' => $item->po_number,
                'unit' => $item->unit,
                'location' => $item->location,
                'contractValue' => (float) $item->contract_value,
                'target' => (float) $item->target,
                'actual' => (float) $item->actual,
                'deadline' => $item->deadline,
                'startDate' => $item->start_date,
                'period' => $item->period,
                'status' => $item->status,
                'pic' => $item->pic,
                'paymentTerm' => $item->payment_term,
                'retention' => $item->retention,
                'notes' => $item->notes ?? '',
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
                'scopes' => $item->scopes->map(function ($s) {
                    return [
                        'scope' => $s->scope,
                        'weight' => (float) $s->weight,
                        'target' => (float) $s->target,
                        'actual' => (float) $s->actual,
                    ];
                }),
                'weekly' => $item->weeklyProgresses->map(function ($w) {
                    return [
                        'period' => $w->period,
                        'target' => (float) $w->target,
                        'actual' => (float) $w->actual,
                        'note' => $w->note ?? '',
                        'reportedBy' => $w->reported_by,
                    ];
                }),
                'issues' => $item->issues->map(function ($iss) {
                    return [
                        'title' => $iss->title,
                        'severity' => $iss->severity,
                        'action' => $iss->action,
                        'owner' => $iss->owner,
                        'date' => $iss->date,
                        'status' => $iss->status,
                    ];
                }),
                'photos' => $item->photos->map(function ($p) {
                    return [
                        'caption' => $p->caption,
                        'date' => $p->date,
                    ];
                }),
                'approvals' => $item->approvals->isNotEmpty()
                    ? $item->approvals->map(function ($app) {
                        return [
                            'stage' => $app->stage,
                            'approver' => $app->approver,
                            'date' => $app->date,
                            'state' => $app->state,
                        ];
                    })
                    : (function () use ($item) {
                        $ga = \App\Models\Approval::with('stages')->find($item->id);
                        if (!$ga || $ga->stages->isEmpty()) {
                            return [];
                        }
                        return $ga->stages->map(function ($st) use ($ga) {
                            $state = 'Pending';
                            if ($ga->status === 'Approved') {
                                $state = 'Approved';
                            } elseif ($ga->status === 'Returned' || $ga->status === 'Rejected') {
                                $state = 'Returned';
                            } else {
                                if ($st->order < $ga->current_stage) {
                                    $state = 'Approved';
                                } elseif ($st->order == $ga->current_stage) {
                                    $state = 'Waiting';
                                } else {
                                    $state = 'Pending';
                                }
                            }
                            return [
                                'stage' => $st->name,
                                'approver' => $st->approver ? "{$st->approver} ({$st->role})" : $st->role,
                                'date' => $st->order < $ga->current_stage ? $ga->submitted_date : null,
                                'state' => $state,
                            ];
                        })->values();
                    })(),
            ];
        });

        return response()->json($formatted);
    }

    public function store(Request $request)
    {
        $isDraft = $request->boolean('isDraft');

        $rules = $isDraft ? [
            'customer'       => 'nullable|string',
            'name'           => 'nullable|string',
            'unit'           => 'nullable|string',
            'location'       => 'nullable|string',
            'contractValue'  => 'nullable|numeric',
            'contractNumber' => 'nullable|string',
            'poNumber'       => 'nullable|string',
            'target'         => 'nullable|numeric',
            'pic'            => 'nullable|string',
            'startDate'      => 'nullable|string',
            'deadline'       => 'nullable|string',
            'paymentTerm'    => 'nullable|string',
            'retention'      => 'nullable|string',
            'notes'          => 'nullable|string',
            'attachments'    => 'nullable|array',
            'isDraft'        => 'nullable|boolean',
        ] : [
            'customer'       => 'required|string',
            'name'           => 'required|string',
            'unit'           => 'required|string',
            'location'       => 'required|string',
            'contractValue'  => 'nullable|numeric',
            'contractNumber' => 'nullable|string',
            'poNumber'       => 'nullable|string',
            'target'         => 'nullable|numeric',
            'pic'            => 'required|string',
            'startDate'      => 'required|string',
            'deadline'       => 'required|string',
            'paymentTerm'    => 'nullable|string',
            'retention'      => 'nullable|string',
            'notes'          => 'nullable|string',
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
        $seq  = str_pad((string) rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $projectId = "CPR-{$year}-{$seq}";

        $customer = !empty($validated['customer']) ? $validated['customer'] : 'Customer (Draft)';
        $name = !empty($validated['name']) ? $validated['name'] : 'Draft Customer Project (' . date('d/m/Y H:i') . ')';
        $unit = !empty($validated['unit']) ? $validated['unit'] : 'Project Samarinda';
        $location = !empty($validated['location']) ? $validated['location'] : 'Lokasi Draft';
        $pic = !empty($validated['pic']) ? $validated['pic'] : 'PIC (Draft)';
        $startDate = !empty($validated['startDate']) ? $validated['startDate'] : date('Y-m-d');
        $deadline = !empty($validated['deadline']) ? $validated['deadline'] : date('Y-m-d', strtotime('+30 days'));
        $contractNumber = !empty($validated['contractNumber']) ? $validated['contractNumber'] : ('SPK-' . strtoupper(substr(md5($projectId), 0, 8)));
        $poNumber = !empty($validated['poNumber']) ? $validated['poNumber'] : ('PO-' . strtoupper(substr(md5($projectId . 'po'), 0, 6)));
        $target = isset($validated['target']) ? (float) $validated['target'] : 0;
        $status = $isDraft ? 'Draft' : 'Waiting Approval';

        $project = CustomerProject::create([
            'id'             => $projectId,
            'customer'       => $customer,
            'name'           => $name,
            'contract_number'=> $contractNumber,
            'po_number'      => $poNumber,
            'unit'           => $unit,
            'location'       => $location,
            'contract_value' => $validated['contractValue'] ?? 0,
            'target'         => $target,
            'actual'         => 0,
            'deadline'       => $deadline,
            'start_date'     => $startDate,
            'period'         => date('M Y'),
            'status'         => $status,
            'pic'            => $pic,
            'payment_term'   => $validated['paymentTerm'] ?? '-',
            'retention'      => $validated['retention'] ?? '-',
            'notes'          => $validated['notes'] ?? null,
        ]);

        // Simpan Attachments jika ada
        if (!empty($validated['attachments'])) {
            foreach ($validated['attachments'] as $att) {
                CustomerProjectAttachment::create([
                    'project_id'   => $projectId,
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
            $approvalId = $projectId;

            Approval::create([
                'id'              => $approvalId,
                'module'          => 'Project Customer',
                'request'         => $name,
                'summary'         => "Project untuk {$customer} di {$location}.",
                'unit'            => $unit,
                'submitted_by'    => $pic,
                'submitted_by_id' => strtolower(str_replace(' ', '', $pic)),
                'submitted_date'  => date('d M Y'),
                'period'          => date('M Y'),
                'amount'          => $validated['contractValue'] ?? null,
                'amount_label'    => $validated['contractValue']
                                        ? 'Rp ' . number_format($validated['contractValue'], 0, ',', '.')
                                        : '-',
                'current_stage'   => 1,
                'priority'        => 'Medium',
                'status'          => 'Waiting',
                'due_date'        => $deadline,
                'notes'           => $validated['notes'] ?? null,
            ]);

            WorkflowEngine::attachStagesToApproval(
                $approvalId,
                'Project Customer',
                'Master Project',
                (float) ($validated['contractValue'] ?? 0),
                $unit,
                [
                    ['order' => 1, 'name' => 'Review Manajer Proyek', 'role' => 'Manajer Proyek', 'unit_scope' => $unit],
                    ['order' => 2, 'name' => 'Persetujuan Keuangan', 'role' => 'Manajer Keuangan', 'unit_scope' => $unit],
                    ['order' => 3, 'name' => 'Persetujuan Direktur', 'role' => 'Direktur', 'unit_scope' => $unit],
                ]
            );

            ApprovalHistory::create([
                'approval_id' => $approvalId,
                'actor'       => $pic,
                'role'        => 'PIC Project',
                'action'      => 'Pengajuan Dikirim',
                'note'        => 'Project customer berhasil diajukan dan menunggu review.',
                'at'          => date('d M Y, H:i'),
            ]);
            // ─────────────────────────────────────────────────────────────────
        }

        return response()->json([
            'projectId'   => $projectId,
            'submittedAt' => date('c'),
            'message'     => $isDraft ? 'Draft Project Customer tersimpan di database.' : 'New Customer Project stored successfully.',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $project = CustomerProject::where('id', $id)->orWhere('contract_number', $id)->first();
        if (!$project) {
            return response()->json(['error' => 'Customer project not found.'], 404);
        }

        $isDraft = $request->boolean('isDraft');

        $rules = $isDraft ? [
            'customer'       => 'nullable|string',
            'name'           => 'nullable|string',
            'unit'           => 'nullable|string',
            'location'       => 'nullable|string',
            'contractValue'  => 'nullable|numeric',
            'contractNumber' => 'nullable|string',
            'poNumber'       => 'nullable|string',
            'target'         => 'nullable|numeric',
            'pic'            => 'nullable|string',
            'startDate'      => 'nullable|string',
            'deadline'       => 'nullable|string',
            'paymentTerm'    => 'nullable|string',
            'retention'      => 'nullable|string',
            'notes'          => 'nullable|string',
            'attachments'    => 'nullable|array',
            'isDraft'        => 'nullable|boolean',
        ] : [
            'customer'       => 'required|string',
            'name'           => 'required|string',
            'unit'           => 'required|string',
            'location'       => 'required|string',
            'contractValue'  => 'nullable|numeric',
            'contractNumber' => 'nullable|string',
            'poNumber'       => 'nullable|string',
            'target'         => 'nullable|numeric',
            'pic'            => 'required|string',
            'startDate'      => 'required|string',
            'deadline'       => 'required|string',
            'paymentTerm'    => 'nullable|string',
            'retention'      => 'nullable|string',
            'notes'          => 'nullable|string',
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

        $customer = !empty($validated['customer']) ? $validated['customer'] : $project->customer;
        $name = !empty($validated['name']) ? $validated['name'] : $project->name;
        $unit = !empty($validated['unit']) ? $validated['unit'] : $project->unit;
        $location = !empty($validated['location']) ? $validated['location'] : $project->location;
        $pic = !empty($validated['pic']) ? $validated['pic'] : $project->pic;
        $startDate = !empty($validated['startDate']) ? $validated['startDate'] : $project->start_date;
        $deadline = !empty($validated['deadline']) ? $validated['deadline'] : $project->deadline;
        $contractNumber = !empty($validated['contractNumber']) ? $validated['contractNumber'] : $project->contract_number;
        $poNumber = !empty($validated['poNumber']) ? $validated['poNumber'] : $project->po_number;
        $target = isset($validated['target']) ? (float) $validated['target'] : $project->target;
        $contractValue = array_key_exists('contractValue', $validated) && $validated['contractValue'] !== null
            ? $validated['contractValue']
            : $project->contract_value;
        $paymentTerm = $validated['paymentTerm'] ?? $project->payment_term;
        $retention = $validated['retention'] ?? $project->retention;
        $notes = array_key_exists('notes', $validated) ? $validated['notes'] : $project->notes;

        $project->update([
            'customer'       => $customer,
            'name'           => $name,
            'contract_number'=> $contractNumber,
            'po_number'      => $poNumber,
            'unit'           => $unit,
            'location'       => $location,
            'contract_value' => $contractValue,
            'target'         => $target,
            'deadline'       => $deadline,
            'start_date'     => $startDate,
            'status'         => $status,
            'pic'            => $pic,
            'payment_term'   => $paymentTerm,
            'retention'      => $retention,
            'notes'          => $notes,
        ]);

        // Perbarui Attachments
        if (isset($validated['attachments'])) {
            CustomerProjectAttachment::where('project_id', $project->id)->delete();
            foreach ($validated['attachments'] as $att) {
                CustomerProjectAttachment::create([
                    'project_id'   => $project->id,
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
            $approvalId = $project->id;
            $existingApp = Approval::find($approvalId);
            if (!$existingApp) {
                Approval::create([
                    'id'              => $approvalId,
                    'module'          => 'Project Customer',
                    'request'         => $name,
                    'summary'         => "Project untuk {$customer} di {$location}.",
                    'unit'            => $unit,
                    'submitted_by'    => $pic,
                    'submitted_by_id' => strtolower(str_replace(' ', '', $pic)),
                    'submitted_date'  => date('d M Y'),
                    'period'          => date('M Y'),
                    'amount'          => $contractValue ?? null,
                    'amount_label'    => $contractValue
                                            ? 'Rp ' . number_format($contractValue, 0, ',', '.')
                                            : '-',
                    'current_stage'   => 1,
                    'priority'        => 'Medium',
                    'status'          => 'Waiting',
                    'due_date'        => $deadline,
                    'notes'           => $notes,
                ]);

                WorkflowEngine::attachStagesToApproval(
                    $approvalId,
                    'Project Customer',
                    'Master Project',
                    (float) ($contractValue ?? 0),
                    $unit,
                    [
                        ['order' => 1, 'name' => 'Review Manajer Proyek', 'role' => 'Manajer Proyek', 'unit_scope' => $unit],
                        ['order' => 2, 'name' => 'Persetujuan Keuangan', 'role' => 'Manajer Keuangan', 'unit_scope' => $unit],
                        ['order' => 3, 'name' => 'Persetujuan Direktur', 'role' => 'Direktur', 'unit_scope' => $unit],
                    ]
                );

                ApprovalHistory::create([
                    'approval_id' => $approvalId,
                    'actor'       => $pic,
                    'role'        => 'PIC Project',
                    'action'      => 'Pengajuan Dikirim dari Draft',
                    'note'        => 'Project customer berhasil diajukan dari draf.',
                    'at'          => date('d M Y, H:i'),
                ]);
            }
        }

        return response()->json([
            'projectId'   => $project->id,
            'submittedAt' => date('c'),
            'message'     => $isDraft ? 'Draft berhasil diperbarui.' : 'Project customer berhasil diajukan ke antrean approval.',
        ], 200);
    }
}
