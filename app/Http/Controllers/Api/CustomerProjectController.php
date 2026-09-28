<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerProject;
use Illuminate\Http\Request;

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
}
