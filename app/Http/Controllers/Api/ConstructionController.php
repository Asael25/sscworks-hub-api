<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConstructionWork;
use Illuminate\Http\Request;

class ConstructionController extends Controller
{
    public function index()
    {
        $works = ConstructionWork::with(['scopes', 'weeklyProgresses', 'issues', 'photos', 'approvals'])
            ->orderBy('created_at', 'desc')
            ->get();

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
}
