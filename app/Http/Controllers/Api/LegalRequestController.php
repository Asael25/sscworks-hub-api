<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegalRequest;
use Illuminate\Http\Request;

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
}
