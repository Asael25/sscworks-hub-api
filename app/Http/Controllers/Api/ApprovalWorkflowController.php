<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStage;
use App\Models\Approval;
use App\Models\ApprovalStage;
use App\Models\ApprovalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApprovalWorkflowController extends Controller
{
    public function index()
    {
        $workflows = ApprovalWorkflow::with('stages')->orderBy('id', 'asc')->get();

        $formatted = $workflows->map(function ($wfl) {
            return [
                'id'        => $wfl->id,
                'module'    => $wfl->module,
                'process'   => $wfl->process,
                'mode'      => $wfl->mode,
                'status'    => $wfl->status,
                'stages'    => $wfl->stages->map(function ($stg) {
                    return [
                        'id'        => 'STG-' . str_pad((string) $stg->id, 3, '0', STR_PAD_LEFT),
                        'order'     => $stg->order,
                        'role'      => $stg->role,
                        'approver'  => $stg->approver ?? '',
                        'unitScope' => $stg->unit_scope ?? 'All units',
                        'threshold' => (float) $stg->threshold,
                        'required'  => (bool) $stg->required,
                    ];
                }),
                'updatedAt' => $wfl->updated_at ? $wfl->updated_at->format('d M Y, H:i') : 'Just now',
            ];
        });

        return response()->json($formatted);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module'  => 'required|string',
            'process' => 'required|string',
            'mode'    => 'required|string|in:Single,Layered',
            'status'  => 'nullable|string|in:Active,Draft',
            'stages'  => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        // Generate next ID: WFL-xxx
        $count = ApprovalWorkflow::count();
        $id = 'WFL-' . str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT);
        while (ApprovalWorkflow::where('id', $id)->exists()) {
            $count++;
            $id = 'WFL-' . str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT);
        }

        $workflow = DB::transaction(function () use ($id, $validated, $request) {
            $wfl = ApprovalWorkflow::create([
                'id'      => $id,
                'module'  => $validated['module'],
                'process' => $validated['process'],
                'mode'    => $validated['mode'],
                'status'  => $validated['status'] ?? 'Active',
            ]);

            $stages = $request->input('stages', []);
            foreach ($stages as $index => $stg) {
                ApprovalWorkflowStage::create([
                    'workflow_id' => $wfl->id,
                    'order'       => $index + 1,
                    'role'        => $stg['role'] ?? 'User',
                    'approver'    => !empty($stg['approver']) ? $stg['approver'] : null,
                    'unit_scope'  => $stg['unitScope'] ?? $stg['unit_scope'] ?? 'All units',
                    'threshold'   => (float) ($stg['threshold'] ?? 0),
                    'required'    => isset($stg['required']) ? (bool) $stg['required'] : true,
                ]);
            }

            return $wfl;
        });

        // Sync timeline realtime ke antrean aktif jika workflow statusnya Active
        if ($workflow->status === 'Active') {
            $this->syncRealtimeApprovalTimeline($workflow);
        }

        return response()->json($this->formatSingleWorkflow($workflow), 201);
    }

    public function update(Request $request, $id)
    {
        $workflow = ApprovalWorkflow::find($id);
        if (!$workflow) {
            return response()->json(['message' => 'Approval workflow tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'module'  => 'required|string',
            'process' => 'required|string',
            'mode'    => 'required|string|in:Single,Layered',
            'status'  => 'nullable|string|in:Active,Draft',
            'stages'  => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($workflow, $validated, $request) {
            $workflow->update([
                'module'  => $validated['module'],
                'process' => $validated['process'],
                'mode'    => $validated['mode'],
                'status'  => $validated['status'] ?? $workflow->status,
            ]);

            // Re-create stages
            ApprovalWorkflowStage::where('workflow_id', $workflow->id)->delete();
            $stages = $request->input('stages', []);
            foreach ($stages as $index => $stg) {
                ApprovalWorkflowStage::create([
                    'workflow_id' => $workflow->id,
                    'order'       => $index + 1,
                    'role'        => $stg['role'] ?? 'User',
                    'approver'    => !empty($stg['approver']) ? $stg['approver'] : null,
                    'unit_scope'  => $stg['unitScope'] ?? $stg['unit_scope'] ?? 'All units',
                    'threshold'   => (float) ($stg['threshold'] ?? 0),
                    'required'    => isset($stg['required']) ? (bool) $stg['required'] : true,
                ]);
            }
        });

        $workflow->load('stages');

        // Sync timeline realtime ke pengajuan aktif di Approval Center
        if ($workflow->status === 'Active') {
            $this->syncRealtimeApprovalTimeline($workflow);
        }

        return response()->json($this->formatSingleWorkflow($workflow));
    }

    public function destroy($id)
    {
        $workflow = ApprovalWorkflow::find($id);
        if (!$workflow) {
            return response()->json(['message' => 'Approval workflow tidak ditemukan'], 404);
        }

        $workflow->delete();
        return response()->json(['message' => 'Workflow berhasil dihapus']);
    }

    public function toggleStatus($id)
    {
        $workflow = ApprovalWorkflow::with('stages')->find($id);
        if (!$workflow) {
            return response()->json(['message' => 'Approval workflow tidak ditemukan'], 404);
        }

        $workflow->status = $workflow->status === 'Active' ? 'Draft' : 'Active';
        $workflow->save();

        if ($workflow->status === 'Active') {
            $this->syncRealtimeApprovalTimeline($workflow);
        }

        return response()->json($this->formatSingleWorkflow($workflow));
    }

    public static function getModuleVariants(string $module): array
    {
        $m = trim($module);
        $map = [
            'Internal Construction' => ['Internal Construction', 'Konstruksi Internal', 'Konstruksi'],
            'Konstruksi Internal'   => ['Internal Construction', 'Konstruksi Internal', 'Konstruksi'],
            'Legal Operations'      => ['Legal Operations', 'Legal', 'Legalitas'],
            'Legal'                 => ['Legal Operations', 'Legal', 'Legalitas'],
            'Project Customer'      => ['Project Customer', 'Customer Project'],
            'Cashflow'              => ['Cashflow', 'Arus Kas'],
            'Budgeting'             => ['Budgeting', 'Anggaran'],
            'Contract'              => ['Contract', 'Kontrak'],
        ];

        return $map[$m] ?? [$m];
    }

    /**
     * Memperbarui secara REALTIME timeline approval yang berstatus 'Waiting' / 'In Review'
     * untuk modul terkait agar langsung mencerminkan tahapan baru yang disetting Super Admin.
     */
    public function syncRealtimeApprovalTimeline(ApprovalWorkflow $workflow)
    {
        try {
            $variants = self::getModuleVariants($workflow->module);

            $query = Approval::whereIn('module', $variants)
                ->whereIn('status', ['Waiting', 'In Review']);

            if ($workflow->process === 'Site Report') {
                $query->where(function ($q) {
                    $q->where('id', 'LIKE', 'CSR-%')
                      ->orWhere('request', 'LIKE', '%Site Report%');
                });
            } elseif ($workflow->process === 'Master Project') {
                $query->where(function ($q) {
                    $q->where('id', 'LIKE', 'CPR-%')
                      ->orWhere('id', 'LIKE', 'PRJ-%');
                })->where('id', 'NOT LIKE', 'CSR-%');
            } elseif ($workflow->process === 'Master Pekerjaan') {
                $query->where(function ($q) {
                    $q->where('id', 'LIKE', 'KI-%')
                      ->orWhere('id', 'LIKE', 'KSI-%');
                });
            }

            $activeApprovals = $query->get();

            $workflowStages = $workflow->stages()->orderBy('order', 'asc')->get();
            if ($workflowStages->isEmpty()) {
                return;
            }

            foreach ($activeApprovals as $approval) {
                $amount = (float) ($approval->amount ?? 0);
                
                // Filter stages yang required atau di atas threshold
                $applicableStages = $workflowStages->filter(function ($stg) use ($amount) {
                    return $stg->required || ($stg->threshold > 0 && $amount >= $stg->threshold);
                });

                if ($workflow->mode === 'Single') {
                    $applicableStages = $applicableStages->take(1);
                } else {
                    $applicableStages = $applicableStages->take(5);
                }

                if ($applicableStages->isEmpty()) {
                    continue;
                }

                // Perbarui stages approval di database
                ApprovalStage::where('approval_id', $approval->id)->delete();
                $order = 1;
                foreach ($applicableStages as $stg) {
                    ApprovalStage::create([
                        'approval_id' => $approval->id,
                        'order'       => $order++,
                        'name'        => $stg->role . ($stg->approver ? " ({$stg->approver})" : ''),
                        'role'        => $stg->role,
                        'approver'    => $stg->approver,
                        'unit_scope'  => $stg->unit_scope ?? 'All units',
                    ]);
                }

                // Catat di ApprovalHistory
                ApprovalHistory::create([
                    'approval_id' => $approval->id,
                    'actor'       => 'System Governance',
                    'role'        => 'System Administrator',
                    'action'      => 'Workflow Timeline Updated',
                    'note'        => "Timeline tahapan approval disinkronkan otomatis mengikuti settingan alur '{$workflow->process}' ({$workflow->mode}).",
                    'at'          => date('d M Y, H:i'),
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error("Gagal sinkronisasi realtime approval timeline: " . $e->getMessage());
        }
    }

    private function formatSingleWorkflow(ApprovalWorkflow $wfl): array
    {
        $wfl->load('stages');
        return [
            'id'        => $wfl->id,
            'module'    => $wfl->module,
            'process'   => $wfl->process,
            'mode'      => $wfl->mode,
            'status'    => $wfl->status,
            'stages'    => $wfl->stages->map(function ($stg) {
                return [
                    'id'        => 'STG-' . str_pad((string) $stg->id, 3, '0', STR_PAD_LEFT),
                    'order'     => $stg->order,
                    'role'      => $stg->role,
                    'approver'  => $stg->approver ?? '',
                    'unitScope' => $stg->unit_scope ?? 'All units',
                    'threshold' => (float) $stg->threshold,
                    'required'  => (bool) $stg->required,
                ];
            }),
            'updatedAt' => $wfl->updated_at ? $wfl->updated_at->format('d M Y, H:i') : 'Just now',
        ];
    }
}
