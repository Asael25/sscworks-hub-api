<?php

namespace App\Services;

use App\Models\ApprovalWorkflow;
use App\Models\ApprovalStage;

class WorkflowEngine
{
    /**
     * Generate and attach approval stages for a given approval record based on active workflow templates.
     */
    public static function attachStagesToApproval(
        string $approvalId,
        string $module,
        ?string $process = null,
        float $amount = 0,
        ?string $unit = null,
        array $fallbackStages = []
    ): void {
        $synonyms = [
            'Internal Construction' => ['Internal Construction', 'Konstruksi Internal', 'Konstruksi'],
            'Konstruksi Internal'   => ['Internal Construction', 'Konstruksi Internal', 'Konstruksi'],
            'Legal Operations'      => ['Legal Operations', 'Legal', 'Legalitas'],
            'Legal'                 => ['Legal Operations', 'Legal', 'Legalitas'],
            'Project Customer'      => ['Project Customer', 'Customer Project'],
            'Cashflow'              => ['Cashflow', 'Arus Kas'],
            'Budgeting'             => ['Budgeting', 'Anggaran'],
            'Contract'              => ['Contract', 'Kontrak'],
        ];
        $moduleList = $synonyms[$module] ?? [$module];

        $query = ApprovalWorkflow::with('stages')
            ->where('status', 'Active')
            ->where(function ($q) use ($moduleList, $module) {
                $q->whereIn('module', $moduleList)
                  ->orWhere('module', 'LIKE', '%' . trim(str_replace(['Operations', 'Internal'], '', $module)) . '%');
            });

        if ($process) {
            $query->where('process', $process);
        }

        $workflow = $query->first();

        // If not found with process, try matching just the module
        if (!$workflow && $process) {
            $workflow = ApprovalWorkflow::with('stages')
                ->where('status', 'Active')
                ->where(function ($q) use ($moduleList, $module) {
                    $q->whereIn('module', $moduleList)
                      ->orWhere('module', 'LIKE', '%' . trim(str_replace(['Operations', 'Internal'], '', $module)) . '%');
                })
                ->first();
        }

        $stagesToInsert = [];

        if ($workflow && $workflow->stages->isNotEmpty()) {
            $wfStages = $workflow->stages->sortBy('order');

            $applicable = $wfStages->filter(function ($stg) use ($amount) {
                return $stg->required || ($stg->threshold > 0 && $amount >= $stg->threshold);
            });

            if ($workflow->mode === 'Single') {
                $applicable = $applicable->take(1);
            } else {
                $applicable = $applicable->take(5);
            }

            $order = 1;
            foreach ($applicable as $stg) {
                $stagesToInsert[] = [
                    'order'      => $order++,
                    'name'       => $stg->role . ($stg->approver ? " ({$stg->approver})" : ''),
                    'role'       => $stg->role,
                    'approver'   => $stg->approver,
                    'unit_scope' => $stg->unit_scope ?? $unit ?? 'All units',
                ];
            }
        }

        // If no workflow stages configured, use fallback
        if (empty($stagesToInsert) && !empty($fallbackStages)) {
            $stagesToInsert = $fallbackStages;
        }

        // Clean any existing stages
        ApprovalStage::where('approval_id', $approvalId)->delete();

        foreach ($stagesToInsert as $s) {
            ApprovalStage::create([
                'approval_id' => $approvalId,
                'order'       => $s['order'],
                'name'        => $s['name'],
                'role'        => $s['role'],
                'approver'    => $s['approver'] ?? null,
                'unit_scope'  => $s['unit_scope'] ?? $unit,
            ]);
        }

        // Kirim notifikasi email ke approver tahap pertama
        \App\Services\ApprovalNotificationService::notifyPendingApprover($approvalId);

        // Kirim notifikasi email tanda terima pengajuan ke akun yang submit request
        \App\Services\ApprovalNotificationService::notifySubmissionReceipt($approvalId);
    }
}
