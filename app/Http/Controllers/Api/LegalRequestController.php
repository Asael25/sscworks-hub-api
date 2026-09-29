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
    public function index()
    {
        $requests = LegalRequest::with(['attachments', 'timeline', 'notes', 'outputDocuments', 'approvals'])
            ->orderBy('submitted_at', 'desc')
            ->get();

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
                'counterparty' => $item->counterparty,
                'estimated_value' => $item->estimated_value,
                'attachments' => $item->attachments->map(function ($a) {
                    return [
                        'id' => (string) $a->id,
                        'request_id' => (string) $a->request_id,
                        'file_name' => $a->file_name,
                        'file_type' => $a->file_type,
                        'file_size_kb' => (int) $a->file_size_kb,
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
            ];
        });

        return response()->json($formatted);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
        ]);

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

        $status = $request->boolean('isDraft') ? 'draft' : 'waiting_review';
        $currentStage = $request->boolean('isDraft') ? 'Draft' : 'Pengajuan Diterima';

        $legalRequest = LegalRequest::create([
            'id' => $id,
            'request_code' => $requestCode,
            'submitted_at' => $validated['submittedDate'],
            'unit' => $validated['unit'],
            'category' => $validated['category'],
            'title' => $validated['title'],
            'applicant' => $validated['pic'],
            'applicant_email' => strtolower(str_replace(' ', '', $validated['pic'])) . '@sscworks.com',
            'priority' => strtolower($validated['priority']),
            'target_date' => $validated['targetDate'],
            'current_stage' => $currentStage,
            'status' => $status,
            'description' => $validated['description'],
            'counterparty' => null,
            'estimated_value' => null,
        ]);

        LegalTimelineEvent::create([
            'id' => (string) rand(1000, 99999),
            'request_id' => $id,
            'stage' => 'Pengajuan Diterima',
            'actor' => $validated['pic'],
            'occurred_at' => date('d M Y, H:i'),
            'state' => 'done',
            'note' => $validated['notes'] ?? 'Legal request submitted.',
        ]);

        LegalTimelineEvent::create([
            'id' => (string) rand(1000, 99999),
            'request_id' => $id,
            'stage' => 'Review Tim Legal',
            'actor' => 'Legal Officer',
            'occurred_at' => null,
            'state' => 'current',
        ]);

        if (!empty($validated['attachments'])) {
            foreach ($validated['attachments'] as $att) {
                LegalAttachment::create([
                    'id' => (string) rand(1000, 99999),
                    'request_id' => $id,
                    'file_name' => $att['name'] ?? 'attachment.pdf',
                    'file_type' => strtoupper(pathinfo($att['name'] ?? 'PDF', PATHINFO_EXTENSION) ?: 'PDF'),
                    'file_size_kb' => $att['sizeKb'] ?? 100,
                    'uploaded_by' => $validated['pic'],
                    'uploaded_at' => date('Y-m-d'),
                ]);
            }
        }

        // ─── Auto-create global Approval Center record ───────────────────
        if (!$request->boolean('isDraft')) {
            $approvalId = $requestCode; // gunakan kode request yang sama sbg ID approval

            Approval::create([
                'id'              => $approvalId,
                'module'          => 'Legal',
                'request'         => $validated['title'],
                'summary'         => $validated['description'],
                'unit'            => $validated['unit'],
                'submitted_by'    => $validated['pic'],
                'submitted_by_id' => strtolower(str_replace(' ', '', $validated['pic'])),
                'submitted_date'  => $validated['submittedDate'],
                'period'          => date('M Y'),
                'amount'          => null,
                'amount_label'    => '-',
                'current_stage'   => 1,
                'priority'        => $validated['priority'],
                'status'          => 'Waiting',
                'due_date'        => $validated['targetDate'],
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
                    'unit_scope'  => $validated['unit'],
                ]);
            }

            // Riwayat awal
            ApprovalHistory::create([
                'approval_id' => $approvalId,
                'actor'       => $validated['pic'],
                'role'        => 'Pemohon',
                'action'      => 'Pengajuan Dikirim',
                'note'        => 'Legal request berhasil diajukan dan menunggu review.',
                'at'          => date('d M Y, H:i'),
            ]);
        }
        // ─────────────────────────────────────────────────────────────────

        return response()->json([
            'requestCode' => $requestCode,
            'submittedAt' => date('c'),
            'message' => 'New Legal Request stored successfully in MySQL database.'
        ], 201);
    }
}
