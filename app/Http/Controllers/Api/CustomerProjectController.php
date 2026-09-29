<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerProject;
use App\Models\Approval;
use App\Models\ApprovalStage;
use App\Models\ApprovalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerProjectController extends Controller
{
    public function index()
    {
        $projects = CustomerProject::with(['scopes', 'weeklyProgresses', 'issues', 'photos', 'approvals'])
            ->orderBy('created_at', 'desc')
            ->get();

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
        $validator = Validator::make($request->all(), [
            'customer'      => 'required|string',
            'name'          => 'required|string',
            'unit'          => 'required|string',
            'location'      => 'required|string',
            'contractValue' => 'nullable|numeric',
            'pic'           => 'required|string',
            'startDate'     => 'required|string',
            'deadline'      => 'required|string',
            'paymentTerm'   => 'nullable|string',
            'retention'     => 'nullable|string',
            'notes'         => 'nullable|string',
        ]);

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

        CustomerProject::create([
            'id'             => $projectId,
            'customer'       => $validated['customer'],
            'name'           => $validated['name'],
            'contract_number'=> 'SPK-' . strtoupper(substr(md5($projectId), 0, 8)),
            'po_number'      => 'PO-' . strtoupper(substr(md5($projectId . 'po'), 0, 6)),
            'unit'           => $validated['unit'],
            'location'       => $validated['location'],
            'contract_value' => $validated['contractValue'] ?? 0,
            'target'         => 0,
            'actual'         => 0,
            'deadline'       => $validated['deadline'],
            'start_date'     => $validated['startDate'],
            'period'         => date('M Y'),
            'status'         => 'Waiting Approval',
            'pic'            => $validated['pic'],
            'payment_term'   => $validated['paymentTerm'] ?? '-',
            'retention'      => $validated['retention'] ?? '-',
        ]);

        // ─── Auto-create global Approval Center record ───────────────────
        $approvalId = $projectId;

        Approval::create([
            'id'              => $approvalId,
            'module'          => 'Project Customer',
            'request'         => $validated['name'],
            'summary'         => "Project untuk {$validated['customer']} di {$validated['location']}.",
            'unit'            => $validated['unit'],
            'submitted_by'    => $validated['pic'],
            'submitted_by_id' => strtolower(str_replace(' ', '', $validated['pic'])),
            'submitted_date'  => date('d M Y'),
            'period'          => date('M Y'),
            'amount'          => $validated['contractValue'] ?? null,
            'amount_label'    => $validated['contractValue']
                                    ? 'Rp ' . number_format($validated['contractValue'], 0, ',', '.')
                                    : '-',
            'current_stage'   => 1,
            'priority'        => 'Medium',
            'status'          => 'Waiting',
            'due_date'        => $validated['deadline'],
            'notes'           => $validated['notes'] ?? null,
        ]);

        $stages = [
            ['order' => 1, 'name' => 'Review Manajer Proyek',   'role' => 'Manajer Proyek'],
            ['order' => 2, 'name' => 'Persetujuan Keuangan',    'role' => 'Manajer Keuangan'],
            ['order' => 3, 'name' => 'Persetujuan Direktur',    'role' => 'Direktur'],
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

        ApprovalHistory::create([
            'approval_id' => $approvalId,
            'actor'       => $validated['pic'],
            'role'        => 'PIC Project',
            'action'      => 'Pengajuan Dikirim',
            'note'        => 'Project customer berhasil diajukan dan menunggu review.',
            'at'          => date('d M Y, H:i'),
        ]);
        // ─────────────────────────────────────────────────────────────────

        return response()->json([
            'projectId'   => $projectId,
            'submittedAt' => date('c'),
            'message'     => 'New Customer Project stored successfully.',
        ], 201);
    }
}

