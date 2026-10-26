<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerProject;
use App\Models\CustomerProjectAttachment;
use App\Models\Approval;
use App\Models\ApprovalStage;
use App\Models\ApprovalHistory;
use App\Services\WorkflowEngine;
use App\Services\DocumentSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerProject::with(['scopes', 'weeklyProgresses', 'issues', 'photos', 'approvals', 'attachments', 'addenda'])
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
        $approvalMap = Approval::whereIn('id', $projects->pluck('id'))->get()->keyBy('id');

        $formatted = $projects->map(function ($item) use ($approvalMap) {
            $app = $approvalMap->get($item->id);
            $subId = $item->submitted_by_id ?? ($app ? $app->submitted_by_id : null);
            $subBy = $item->submitted_by ?? ($app ? $app->submitted_by : null);

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
                'submittedById' => $subId,
                'submittedBy' => $subBy,
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
                'addenda' => $item->addenda ? $item->addenda->map(function ($ad) {
                    return [
                        'id' => $ad->addendum_id,
                        'addendumNumber' => (int) $ad->addendum_number,
                        'submissionDate' => $ad->submitted_date,
                        'effectiveDate' => $ad->approved_at,
                        'previousBudget' => (float) $ad->previous_amount,
                        'revisedBudget' => (float) $ad->revised_amount,
                        'previousTargetDate' => $ad->previous_target_date,
                        'revisedTargetDate' => $ad->revised_target_date,
                        'reason' => $ad->reason,
                        'documentName' => $ad->document_name,
                        'documentUrl' => $ad->document_url,
                        'status' => $ad->status,
                        'approvedBy' => $ad->approved_by,
                        'approvedAt' => $ad->approved_at,
                        'rejectionReason' => $ad->rejection_reason,
                    ];
                }) : [],
                'hasPendingRevision' => $item->addenda ? $item->addenda->where('status', 'Waiting Approval')->count() > 0 : false,
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

        $identifier = $request->header('X-User-Identifier');
        $submitterUser = null;
        if ($identifier) {
            $submitterUser = \App\Models\User::whereRaw('LOWER(identifier) = ?', [strtolower($identifier)])
                ->orWhereRaw('LOWER(username) = ?', [strtolower($identifier)])
                ->first();
        }
        $submitterName = $submitterUser ? ($submitterUser->name ?: $submitterUser->username) : $pic;
        $submitterId = $submitterUser ? $submitterUser->identifier : ($identifier ?: strtolower(str_replace(' ', '', $pic)));

        $project = CustomerProject::create([
            'id'              => $projectId,
            'customer'        => $customer,
            'name'            => $name,
            'contract_number' => $contractNumber,
            'po_number'       => $poNumber,
            'unit'            => $unit,
            'location'        => $location,
            'contract_value'  => $validated['contractValue'] ?? 0,
            'target'          => $target,
            'actual'          => 0,
            'deadline'        => $deadline,
            'start_date'      => $startDate,
            'period'          => date('M Y'),
            'status'          => $status,
            'pic'             => $pic,
            'submitted_by'    => $submitterName,
            'submitted_by_id' => $submitterId,
            'payment_term'    => $validated['paymentTerm'] ?? '-',
            'retention'       => $validated['retention'] ?? '-',
            'notes'           => $validated['notes'] ?? null,
        ]);

        // Simpan Attachments jika ada
        if (!empty($validated['attachments'])) {
            foreach ($validated['attachments'] as $att) {
                $projAtt = CustomerProjectAttachment::create([
                    'project_id'   => $projectId,
                    'file_name'    => $att['name'] ?? 'attachment.pdf',
                    'file_type'    => strtoupper(pathinfo($att['name'] ?? 'PDF', PATHINFO_EXTENSION) ?: 'PDF'),
                    'file_size_kb' => (int) ($att['sizeKb'] ?? 100),
                    'file_url'     => $att['url'] ?? null,
                    'uploaded_by'  => $pic,
                    'uploaded_at'  => date('Y-m-d'),
                ]);

                // Auto-sync ke Document Center
                DocumentSyncService::syncCustomerProjectAttachment($project, $projAtt);
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

    public function submitSiteReport(Request $request)
    {
        $validated = $request->validate([
            'id'             => 'required|string',
            'projectId'      => 'required|string',
            'projectName'    => 'required|string',
            'reportingDate'  => 'required|string',
            'pic'            => 'required|string',
            'targetProgress' => 'required|numeric',
            'actualProgress' => 'required|numeric',
            'deviation'      => 'required|numeric',
            'workCompleted'  => 'required|string',
            'workPlanned'    => 'required|string',
            'issues'         => 'nullable|string',
            'correctiveAction'=> 'nullable|string',
            'attachments'    => 'nullable|array',
        ]);

        $project = CustomerProject::find($validated['projectId']);
        if ($project && in_array($project->status, ['Waiting Approval', 'Draft'])) {
            return response()->json([
                'error' => 'Project belum disetujui untuk beroperasi (Status saat ini: ' . $project->status . '). Laporan site report hanya dapat diinput untuk project yang sudah disetujui/beroperasi.'
            ], 422);
        }

        // Validasi Otorisasi: Hanya PIC atau Submitter yang berhak menginput site report (kecuali peran manajerial)
        $identifier = $request->header('X-User-Identifier');
        if ($identifier && $project) {
            $user = \App\Models\User::whereRaw('LOWER(identifier) = ?', [strtolower($identifier)])
                ->orWhereRaw('LOWER(username) = ?', [strtolower($identifier)])
                ->first();
            if ($user) {
                $role = strtolower(trim($user->role ?? ''));
                $isManagerOrAdmin = in_array($role, [
                    'super admin', 'admin super', 'branch manager', 'general manager', 
                    'manajer proyek', 'project manager', 'kepala cabang', 'unit head', 'direktur'
                ]);

                if (strtolower($user->identifier) === 'rudi@sscworks.com' || str_contains($role, 'it') || str_contains($role, 'support')) {
                    $isManagerOrAdmin = false;
                }

                if (!$isManagerOrAdmin) {
                    $userName = strtolower(trim($user->name ?: $user->username));
                    $userEmail = strtolower(trim($user->identifier));
                    $emailPrefix = explode('@', $userEmail)[0] ?? '';
                    $projPic = strtolower(trim($project->pic ?? ''));
                    
                    $app = Approval::find($project->id);
                    $subId = strtolower(trim($project->submitted_by_id ?? ($app ? $app->submitted_by_id : '')));
                    $subBy = strtolower(trim($project->submitted_by ?? ($app ? $app->submitted_by : '')));

                    $isPic = ($projPic === $userName)
                        || ($projPic === $userEmail)
                        || (!empty($emailPrefix) && ($projPic === $emailPrefix || str_contains($projPic, $emailPrefix)))
                        || (!empty($userName) && (str_contains($projPic, $userName) || str_contains($userName, $projPic)));

                    $isSubmitter = (!empty($subId) && ($subId === $userEmail || $subId === (string)$user->id || $subId === $emailPrefix))
                        || (!empty($subBy) && ($subBy === $userName || str_contains($subBy, $userName) || str_contains($userName, $subBy)));

                    if (!$isPic && !$isSubmitter) {
                        return response()->json([
                            'error' => 'Anda tidak memiliki wewenang untuk menginput site report pada project ini. Laporan hanya dapat diajukan oleh PIC yang ditugaskan (' . $project->pic . ') atau pengguna dengan peran manajerial.'
                        ], 403);
                    }
                }
            }
        }

        $unit = $project ? $project->unit : 'Cabang Surabaya';

        $approvalId = $validated['id'];

        $existing = Approval::find($approvalId);
        if (!$existing) {
            Approval::create([
                'id'              => $approvalId,
                'module'          => 'Project Customer',
                'request'         => "Site Report · {$validated['projectName']}",
                'summary'         => "Laporan Mingguan ({$validated['reportingDate']}): Progres Realisasi {$validated['actualProgress']}% (Target {$validated['targetProgress']}%, Deviasi {$validated['deviation']}%). Dilaporkan oleh {$validated['pic']}.",
                'unit'            => $unit,
                'submitted_by'    => $validated['pic'],
                'submitted_by_id' => strtolower(str_replace(' ', '', $validated['pic'])),
                'submitted_date'  => date('d M Y'),
                'period'          => date('M Y'),
                'amount'          => 0,
                'amount_label'    => '-',
                'current_stage'   => 1,
                'priority'        => 'Medium',
                'status'          => 'Waiting',
                'due_date'        => date('Y-m-d', strtotime('+7 days')),
                'notes'           => "Pekerjaan Selesai: {$validated['workCompleted']}\nRencana: {$validated['workPlanned']}",
            ]);

            if (!empty($validated['attachments'])) {
                foreach ($validated['attachments'] as $att) {
                    \App\Models\ApprovalAttachment::create([
                        'approval_id' => $approvalId,
                        'name'        => $att['name'] ?? 'lampiran.pdf',
                        'size'        => isset($att['sizeKb']) ? ($att['sizeKb'] . ' KB') : '100 KB',
                        'type'        => strtoupper(pathinfo($att['name'] ?? 'PDF', PATHINFO_EXTENSION) ?: 'PDF'),
                        'file_path'   => $att['url'] ?? null,
                        'file_url'    => $att['url'] ?? null,
                    ]);
                }
            }

            if ($project) {
                \App\Models\CustomerProjectWeeklyProgress::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'period'     => $validated['reportingDate'],
                    ],
                    [
                        'target'      => $validated['targetProgress'],
                        'actual'      => $validated['actualProgress'],
                        'note'        => "Pekerjaan Selesai: {$validated['workCompleted']}\nRencana: {$validated['workPlanned']}",
                        'reported_by' => $validated['pic'],
                    ]
                );
            }

            WorkflowEngine::attachStagesToApproval(
                $approvalId,
                'Project Customer',
                'Site Report',
                0,
                $unit,
                [
                    ['order' => 1, 'name' => 'Review Commercial', 'role' => 'Staff Commercial', 'approver' => 'Andi Sultan'],
                    ['order' => 2, 'name' => 'Persetujuan General Manager', 'role' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif'],
                ]
            );

            ApprovalHistory::create([
                'approval_id' => $approvalId,
                'actor'       => $validated['pic'],
                'role'        => 'Pengawas Lapangan',
                'action'      => 'Laporan Diajukan',
                'note'        => 'Laporan Site Report diajukan dan menunggu review bertahap.',
                'at'          => date('d M Y, H:i'),
            ]);
        } else {
            // Jika sebelumnya berstatus 'Returned' atau perlu revisi, aktifkan kembali
            // Pertahankan $existing->current_stage agar tidak mengulang tahap 1 jika revisi terjadi di tahap 2 dst.
            $nextStatus = ($existing->current_stage > 1) ? 'In Review' : 'Waiting';

            $existing->update([
                'status'  => $nextStatus,
                'summary' => "Laporan Mingguan ({$validated['reportingDate']}): Progres Realisasi {$validated['actualProgress']}% (Target {$validated['targetProgress']}%, Deviasi {$validated['deviation']}%). Dilaporkan oleh {$validated['pic']}.",
                'notes'   => "Pekerjaan Selesai: {$validated['workCompleted']}\nRencana: {$validated['workPlanned']}",
            ]);

            if (!empty($validated['attachments'])) {
                \App\Models\ApprovalAttachment::where('approval_id', $approvalId)->delete();
                foreach ($validated['attachments'] as $att) {
                    \App\Models\ApprovalAttachment::create([
                        'approval_id' => $approvalId,
                        'name'        => $att['name'] ?? 'lampiran.pdf',
                        'size'        => isset($att['sizeKb']) ? ($att['sizeKb'] . ' KB') : '100 KB',
                        'type'        => strtoupper(pathinfo($att['name'] ?? 'PDF', PATHINFO_EXTENSION) ?: 'PDF'),
                        'file_path'   => $att['url'] ?? null,
                        'file_url'    => $att['url'] ?? null,
                    ]);
                }
            }

            ApprovalHistory::create([
                'approval_id' => $approvalId,
                'actor'       => $validated['pic'],
                'role'        => 'Pengawas Lapangan',
                'action'      => 'Revisi Diajukan Kembali',
                'note'        => "Laporan site report telah direvisi oleh {$validated['pic']} dan diajukan kembali ke Tahap {$existing->current_stage}.",
                'at'          => date('d M Y, H:i'),
            ]);
        }

        return response()->json([
            'success'    => true,
            'approvalId' => $approvalId,
            'message'    => 'Site report berhasil diajukan ke Approval Center.',
        ]);
    }
}
