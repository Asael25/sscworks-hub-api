<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStage;

class ApprovalWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $workflows = [
            [
                'id' => 'WFL-001',
                'module' => 'Budgeting',
                'process' => 'Budget Plan',
                'mode' => 'Layered',
                'status' => 'Active',
                'stages' => [
                    ['order' => 1, 'role' => 'Staff Commercial', 'approver' => 'Andi Sultan', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                    ['order' => 2, 'role' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                ],
            ],
            [
                'id' => 'WFL-002',
                'module' => 'Cashflow',
                'process' => 'Transaction Verification',
                'mode' => 'Single',
                'status' => 'Active',
                'stages' => [
                    ['order' => 1, 'role' => 'Finance Reviewer', 'approver' => null, 'unit_scope' => 'Same unit', 'threshold' => 0, 'required' => true],
                ],
            ],
            [
                'id' => 'WFL-003',
                'module' => 'Project Customer',
                'process' => 'Site Report',
                'mode' => 'Layered',
                'status' => 'Active',
                'stages' => [
                    ['order' => 1, 'role' => 'Staff Commercial', 'approver' => 'Andi Sultan', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                    ['order' => 2, 'role' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                ],
            ],
            [
                'id' => 'WFL-004',
                'module' => 'Project Customer',
                'process' => 'Master Project',
                'mode' => 'Layered',
                'status' => 'Active',
                'stages' => [
                    ['order' => 1, 'role' => 'Manajer Proyek', 'approver' => null, 'unit_scope' => 'Same unit', 'threshold' => 0, 'required' => true],
                    ['order' => 2, 'role' => 'Manajer Keuangan', 'approver' => null, 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                    ['order' => 3, 'role' => 'Direktur', 'approver' => null, 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                ],
            ],
            [
                'id' => 'WFL-005',
                'module' => 'Internal Construction',
                'process' => 'Master Pekerjaan',
                'mode' => 'Layered',
                'status' => 'Active',
                'stages' => [
                    ['order' => 1, 'role' => 'Unit Head', 'approver' => null, 'unit_scope' => 'Same unit', 'threshold' => 0, 'required' => true],
                    ['order' => 2, 'role' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                ],
            ],
            [
                'id' => 'WFL-006',
                'module' => 'Legal Operations',
                'process' => 'Legal Request',
                'mode' => 'Layered',
                'status' => 'Active',
                'stages' => [
                    ['order' => 1, 'role' => 'Legal Officer', 'approver' => null, 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                    ['order' => 2, 'role' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                ],
            ],
        ];

        foreach ($workflows as $wfl) {
            $stages = $wfl['stages'];
            unset($wfl['stages']);

            $workflow = ApprovalWorkflow::updateOrCreate(['id' => $wfl['id']], $wfl);

            // Re-create stages
            ApprovalWorkflowStage::where('workflow_id', $workflow->id)->delete();
            foreach ($stages as $stg) {
                ApprovalWorkflowStage::create([
                    'workflow_id' => $workflow->id,
                    'order'       => $stg['order'],
                    'role'        => $stg['role'],
                    'approver'    => $stg['approver'] ?: null,
                    'unit_scope'  => $stg['unit_scope'] ?? 'All units',
                    'threshold'   => $stg['threshold'] ?? 0,
                    'required'    => $stg['required'] ?? true,
                ]);
            }
        }
    }
}
