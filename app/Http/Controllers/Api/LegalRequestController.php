<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegalRequest;
use App\Models\LegalAttachment;
use App\Models\LegalTimelineEvent;
use App\Models\Approval;
use App\Models\ApprovalStage;
use App\Models\ApprovalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LegalRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LegalRequest::with(['attachments', 'timeline', 'notes', 'outputDocuments', 'approvals'])
            ->orderBy('submitted_at', 'desc');

        $identifier = $request->header('X-User-Identifier');
        if ($identifier) {
            $user = \App\Models\User::where('identifier', $identifier)->first();
            if ($user) {
                $isMultiUnitAdmin = in_array($user->role, [
                    'IT & Bussiness System Support',
                    'Admin Super',
                ]) || (is_array($user->additional_units) && in_array('*', $user->additional_units));

                if (!$isMultiUnitAdmin) {
                    $allowedUnits = array_filter(array_merge([$user->primary_unit], $user->additional_units ?? []));
                    
                    $query->where(function ($q) use ($allowedUnits) {
                        foreach ($allowedUnits as $unit) {
                            if (!$unit) continue;
                            $q->orWhere('unit', $unit);
                            
                            $dashHyphen = str_replace('—', '-', $unit);
                            $dashEm = str_replace('-', '—', $unit);
                            $q->orWhere('unit', $dashHyphen)
                              ->orWhere('unit', $dashEm);
                              
                            $clean = trim(str_replace(['SSC', 'Works', 'Cabang', '—', '-'], ' ', $unit));
                            if (!empty($clean)) {
                                $q->orWhere('unit', 'LIKE', '%' . $clean . '%');
                                if (stripos($clean, 'Head Office') !== false) {
                                    $hoDepts = ['Procurement', 'IT & Digital', 'Human Resources', 'Commercial', 'Accounting', 'Human Capital', 'Finance', 'Operations'];
                                    $q->orWhereIn('unit', $hoDepts);
                                }
                            }
                        }
                    });
                }
            }
        }

        $requests = $query->get();

        $formatted = $requests->map(function ($item) {
            return [
                'id' => (string) $item->id,
                'request_code' => $item->request_code,
                'submitted_at' => $item->submitted_at,
                'unit' => $item->unit,
                'category' => $item->category,
                'title' => $item->title,
                'applicant' => $item->applicant,
                'applicant_email' => $item->applicant_email,
                'priority' => $item->priority,
                'target_date' => $item->target_date,
                'current_stage' => $item->current_stage,
                'status' => $item->status,
                'description' => $item->description ?? '',
                'purpose' => $item->purpose ?? '',
                'notes_text' => $item->notes_text ?? '',
                'counterparty' => $item->counterparty,
                'estimated_value' => $item->estimated_value,
                'attachments' => $item->attachments->map(function ($a) {
                    return [
                        'id' => (string) $a->id,
                        'request_id' => (string) $a->request_id,
                        'file_name' => $a->file_name,
                        'file_type' => $a->file_type,
                        'file_size_kb' => (int) $a->file_size_kb,
                        'file_url' => $a->file_url,
                        'uploaded_by' => $a->uploaded_by,
                        'uploaded_at' => $a->uploaded_at,
                    ];
                }),
                'timeline' => $item->timeline->map(function ($t) {
                    return [
                        'id' => (string) $t->id,
                        'request_id' => (string) $t->request_id,
                        'stage' => $t->stage,
                        'actor' => $t->actor,
                        'occurred_at' => $t->occurred_at,
                        'state' => $t->state,
                        'note' => $t->note,
                    ];
                }),
                'notes' => $item->notes->map(function ($n) {
                    return [
                        'id' => (string) $n->id,
                        'request_id' => (string) $n->request_id,
                        'author' => $n->author,
                        'created_at' => $n->created_at_str,
                        'body' => $n->body,
                    ];
                }),
                'output_documents' => $item->outputDocuments->map(function ($d) {
                    return [
                        'id' => (string) $d->id,
                        'request_id' => (string) $d->request_id,
                        'doc_number' => $d->doc_number,
                        'title' => $d->title,
                        'doc_type' => $d->doc_type,
                        'issued_at' => $d->issued_at,
                    ];
                }),
                'approvals' => $item->approvals->map(function ($app) {
                    return [
                        'id' => (string) $app->id,
                        'request_id' => (string) $app->request_id,
                        'approver' => $app->approver,
                        'role' => $app->role,
                        'decision' => $app->decision,
                        'decided_at' => $app->decided_at,
                        'comment' => $app->comment,
                    ];
                }),
                'created_at' => $item->created_at ? $item->created_at->toISOString() : null,
                'updated_at' => $item->updated_at ? $item->updated_at->toISOString() : null,
            ];
        });

        return response()->json($formatted);
    }

    public function store(Request $request)
    {
        $isDraft = $request->boolean('isDraft');

        $rules = $isDraft ? [
            'unit' => 'nullable|string',
            'submittedDate' => 'nullable|string',
            'category' => 'nullable|string',
            'priority' => 'nullable|string',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'purpose' => 'nullable|string',
            'targetDate' => 'nullable|string',
            'pic' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachments' => 'nullable|array',
            'isDraft' => 'nullable|boolean',
        ] : [
            'unit' => 'required|string',
            'submittedDate' => 'required|string',
            'category' => 'required|string',
            'priority' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',
            'purpose' => 'nullable|string',
            'targetDate' => 'required|string',
            'pic' => 'required|string',
            'notes' => 'nullable|string',
            'attachments' => 'nullable|array',
            'isDraft' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $validator->errors()->all()
            ], 422);
        }

        $validated = $validator->validated();

        $year = date('Y');
        $randomSeq = str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT);
        $requestCode = "LGL-{$year}-{$randomSeq}";
        
        $maxId = LegalRequest::selectRaw('MAX(CAST(id AS UNSIGNED)) as max_id')->value('max_id');
        $id = (string) ($maxId ? ((int)$maxId + 1) : rand(100, 9999));

        $status = $isDraft ? 'draft' : 'waiting_review';
        $currentStage = $isDraft ? 'Draft' : 'Pengajuan Diterima';

        $unit = !empty($validated['unit']) ? $validated['unit'] : 'Unit Pemohon';
        $category = !empty($validated['category']) ? $validated['category'] : 'Corporate Legal';
        $title = !empty($validated['title']) ? $validated['title'] : 'Draft Permintaan Legal (' . date('d/m/Y H:i') . ')';
        $pic = !empty($validated['pic']) ? $validated['pic'] : 'Pemohon (Draft)';
        $submittedDate = !empty($validated['submittedDate']) ? $validated['submittedDate'] : date('Y-m-d');
        $targetDate = !empty($validated['targetDate']) ? $validated['targetDate'] : date('Y-m-d', strtotime('+7 days'));
        $priority = !empty($validated['priority']) ? strtolower($validated['priority']) : 'medium';
        $description = $validated['description'] ?? '';
        $purpose = $validated['purpose'] ?? '';
        $notesText = $validated['notes'] ?? '';

        $legalRequest = LegalRequest::create([
            'id' => $id,
            'request_code' => $requestCode,
            'submitted_at' => $submittedDate,
            'unit' => $unit,
            'category' => $category,
            'title' => $title,
            'applicant' => $pic,
            'applicant_email' => strtolower(str_replace(' ', '', $pic)) . '@sscworks.com',
            'priority' => $priority,
            'target_date' => $targetDate,
            'current_stage' => $currentStage,
            'status' => $status,
            'description' => $description,
            'purpose' => $purpose,
            'notes_text' => $notesText,
            'counterparty' => null,
            'estimated_value' => null,
        ]);

        LegalTimelineEvent::create([
            'id' => (string) rand(1000, 99999),
            'request_id' => $id,
            'stage' => $isDraft ? 'Draft Dibuat' : 'Pengajuan Diterima',
            'actor' => $pic,
            'occurred_at' => date('d M Y, H:i'),
            'state' => 'done',
            'note' => $validated['notes'] ?? ($isDraft ? 'Draf permintaan disimpan.' : 'Legal request submitted.'),
        ]);

        if (!$isDraft) {
            LegalTimelineEvent::create([
                'id' => (string) rand(1000, 99999),
                'request_id' => $id,
                'stage' => 'Review Tim Legal',
                'actor' => 'Legal Officer',
                'occurred_at' => null,
                'state' => 'current',
            ]);
        }

        if (!empty($validated['attachments'])) {
            foreach ($validated['attachments'] as $att) {
                LegalAttachment::create([
                    'id' => (string) rand(1000, 99999),
                    'request_id' => $id,
                    'file_name' => $att['name'] ?? 'attachment.pdf',
                    'file_type' => strtoupper(pathinfo($att['name'] ?? 'PDF', PATHINFO_EXTENSION) ?: 'PDF'),
                    'file_size_kb' => $att['sizeKb'] ?? 100,
                    'file_url' => $att['url'] ?? null,
                    'uploaded_by' => $pic,
                    'uploaded_at' => date('Y-m-d'),
                ]);
            }
        }

        // ─── Auto-create global Approval Center record ───────────────────
        if (!$isDraft) {
            $approvalId = $requestCode; // gunakan kode request yang sama sbg ID approval

            Approval::create([
                'id'              => $approvalId,
                'module'          => 'Legal',
                'request'         => $title,
                'summary'         => $description,
                'unit'            => $unit,
                'submitted_by'    => $pic,
                'submitted_by_id' => strtolower(str_replace(' ', '', $pic)),
                'submitted_date'  => $submittedDate,
                'period'          => date('M Y'),
                'amount'          => null,
                'amount_label'    => '-',
                'current_stage'   => 1,
                'priority'        => $priority,
                'status'          => 'Waiting',
                'due_date'        => $targetDate,
                'notes'           => $validated['notes'] ?? null,
            ]);

            // Tahapan approval legal
            $stages = [
                ['order' => 1, 'name' => 'Review Tim Legal',      'role' => 'Legal Officer'],
                ['order' => 2, 'name' => 'Persetujuan Manager',    'role' => 'Manager Legal'],
                ['order' => 3, 'name' => 'Persetujuan Direktur',   'role' => 'Direktur'],
            ];
            foreach ($stages as $stg) {
                ApprovalStage::create([
                    'approval_id' => $approvalId,
                    'order'       => $stg['order'],
                    'name'        => $stg['name'],
                    'role'        => $stg['role'],
                    'approver'    => null,
                    'unit_scope'  => $unit,
                ]);
            }

            // Riwayat awal
            ApprovalHistory::create([
                'approval_id' => $approvalId,
                'actor'       => $pic,
                'role'        => 'Pemohon',
                'action'      => 'Pengajuan Dikirim',
                'note'        => 'Legal request berhasil diajukan dan menunggu review.',
                'at'          => date('d M Y, H:i'),
            ]);

            if (!empty($validated['attachments'])) {
                foreach ($validated['attachments'] as $att) {
                    \App\Models\ApprovalAttachment::create([
                        'id' => (string) rand(1000, 99999),
                        'approval_id' => $approvalId,
                        'name' => $att['name'] ?? 'attachment.pdf',
                        'type' => strtoupper(pathinfo($att['name'] ?? 'PDF', PATHINFO_EXTENSION) ?: 'PDF'),
                        'size' => ($att['sizeKb'] ?? 100) . ' KB',
                        'file_url' => $att['url'] ?? null,
                    ]);
                }
            }
        }
        // ─────────────────────────────────────────────────────────────────

        return response()->json([
            'id' => $id,
            'requestCode' => $requestCode,
            'submittedAt' => date('c'),
            'message' => $isDraft ? 'Draft tersimpan di database.' : 'New Legal Request stored successfully in MySQL database.'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $legalRequest = LegalRequest::where('id', $id)->orWhere('request_code', $id)->first();
        if (!$legalRequest) {
            return response()->json(['error' => 'Legal request not found.'], 404);
        }

        $isDraft = $request->boolean('isDraft');

        $rules = $isDraft ? [
            'unit' => 'nullable|string',
            'submittedDate' => 'nullable|string',
            'category' => 'nullable|string',
            'priority' => 'nullable|string',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'purpose' => 'nullable|string',
            'targetDate' => 'nullable|string',
            'pic' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachments' => 'nullable|array',
            'isDraft' => 'nullable|boolean',
        ] : [
            'unit' => 'required|string',
            'submittedDate' => 'required|string',
            'category' => 'required|string',
            'priority' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',
            'purpose' => 'nullable|string',
            'targetDate' => 'required|string',
            'pic' => 'required|string',
            'notes' => 'nullable|string',
            'attachments' => 'nullable|array',
            'isDraft' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $validator->errors()->all()
            ], 422);
        }

        $validated = $validator->validated();

        $status = $isDraft ? 'draft' : 'waiting_review';
        $currentStage = $isDraft ? 'Draft' : 'Pengajuan Diterima';

        $unit = !empty($validated['unit']) ? $validated['unit'] : $legalRequest->unit;
        $category = !empty($validated['category']) ? $validated['category'] : $legalRequest->category;
        $title = !empty($validated['title']) ? $validated['title'] : $legalRequest->title;
        $pic = !empty($validated['pic']) ? $validated['pic'] : $legalRequest->applicant;
        $submittedDate = !empty($validated['submittedDate']) ? $validated['submittedDate'] : $legalRequest->submitted_at;
        $targetDate = !empty($validated['targetDate']) ? $validated['targetDate'] : $legalRequest->target_date;
        $priority = !empty($validated['priority']) ? strtolower($validated['priority']) : $legalRequest->priority;
        $description = $validated['description'] ?? $legalRequest->description;
        $purpose = array_key_exists('purpose', $validated) ? $validated['purpose'] : ($legalRequest->purpose ?? '');
        $notesText = array_key_exists('notes', $validated) ? $validated['notes'] : ($legalRequest->notes_text ?? '');

        $legalRequest->update([
            'submitted_at' => $submittedDate,
            'unit' => $unit,
            'category' => $category,
            'title' => $title,
            'applicant' => $pic,
            'applicant_email' => strtolower(str_replace(' ', '', $pic)) . '@sscworks.com',
            'priority' => $priority,
            'target_date' => $targetDate,
            'current_stage' => $currentStage,
            'status' => $status,
            'description' => $description,
            'purpose' => $purpose,
            'notes_text' => $notesText,
        ]);

        if (!empty($validated['attachments'])) {
            LegalAttachment::where('request_id', $legalRequest->id)->delete();
            foreach ($validated['attachments'] as $att) {
                LegalAttachment::create([
                    'id' => (string) rand(1000, 99999),
                    'request_id' => $legalRequest->id,
                    'file_name' => $att['name'] ?? 'attachment.pdf',
                    'file_type' => strtoupper(pathinfo($att['name'] ?? 'PDF', PATHINFO_EXTENSION) ?: 'PDF'),
                    'file_size_kb' => $att['sizeKb'] ?? 100,
                    'file_url' => $att['url'] ?? null,
                    'uploaded_by' => $pic,
                    'uploaded_at' => date('Y-m-d'),
                ]);
            }
        }

        if (!$isDraft) {
            // Update/create timeline for submit
            LegalTimelineEvent::create([
                'id' => (string) rand(1000, 99999),
                'request_id' => $legalRequest->id,
                'stage' => 'Pengajuan Diterima',
                'actor' => $pic,
                'occurred_at' => date('d M Y, H:i'),
                'state' => 'done',
                'note' => $validated['notes'] ?? 'Draf berhasil disubmit untuk diproses tim legal.',
            ]);

            LegalTimelineEvent::create([
                'id' => (string) rand(1000, 99999),
                'request_id' => $legalRequest->id,
                'stage' => 'Review Tim Legal',
                'actor' => 'Legal Officer',
                'occurred_at' => null,
                'state' => 'current',
            ]);

            // Create Approval Center record if not exists
            $approvalId = $legalRequest->request_code;
            $existingApp = Approval::find($approvalId);
            if (!$existingApp) {
                Approval::create([
                    'id'              => $approvalId,
                    'module'          => 'Legal',
                    'request'         => $title,
                    'summary'         => $description,
                    'unit'            => $unit,
                    'submitted_by'    => $pic,
                    'submitted_by_id' => strtolower(str_replace(' ', '', $pic)),
                    'submitted_date'  => $submittedDate,
                    'period'          => date('M Y'),
                    'amount'          => null,
                    'amount_label'    => '-',
                    'current_stage'   => 1,
                    'priority'        => $priority,
                    'status'          => 'Waiting',
                    'due_date'        => $targetDate,
                    'notes'           => $validated['notes'] ?? null,
                ]);

                $stages = [
                    ['order' => 1, 'name' => 'Review Tim Legal',      'role' => 'Legal Officer'],
                    ['order' => 2, 'name' => 'Persetujuan Manager',    'role' => 'Manager Legal'],
                    ['order' => 3, 'name' => 'Persetujuan Direktur',   'role' => 'Direktur'],
                ];
                foreach ($stages as $stg) {
                    ApprovalStage::create([
                        'approval_id' => $approvalId,
                        'order'       => $stg['order'],
                        'name'        => $stg['name'],
                        'role'        => $stg['role'],
                        'approver'    => null,
                        'unit_scope'  => $unit,
                    ]);
                }

                ApprovalHistory::create([
                    'approval_id' => $approvalId,
                    'actor'       => $pic,
                    'role'        => 'Pemohon',
                    'action'      => 'Pengajuan Dikirim dari Draft',
                    'note'        => 'Draft legal request berhasil diajukan dan masuk ke antrean approval.',
                    'at'          => date('d M Y, H:i'),
                ]);

                if (!empty($validated['attachments'])) {
                    foreach ($validated['attachments'] as $att) {
                        \App\Models\ApprovalAttachment::create([
                            'id' => (string) rand(1000, 99999),
                            'approval_id' => $approvalId,
                            'name' => $att['name'] ?? 'attachment.pdf',
                            'type' => strtoupper(pathinfo($att['name'] ?? 'PDF', PATHINFO_EXTENSION) ?: 'PDF'),
                            'size' => ($att['sizeKb'] ?? 100) . ' KB',
                            'file_url' => $att['url'] ?? null,
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'id' => $legalRequest->id,
            'requestCode' => $legalRequest->request_code,
            'submittedAt' => date('c'),
            'message' => $isDraft ? 'Draft berhasil diperbarui.' : 'Permintaan legal berhasil disubmit ke antrean approval.'
        ], 200);
    }
}
