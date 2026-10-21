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
                'id'            => 'WFL-001',
                'module'        => 'Budgeting',
                'process'       => 'Budget Plan',
                'workflow_type' => 'submission',
                'mode'          => 'Layered',
                'status'        => 'Active',
                'is_customized' => false,
                'stages' => [
                    ['order' => 1, 'stage_level' => 1, 'stage_name' => 'Tahap 1: Verifikasi Keuangan', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Finance Reviewer', 'approver' => null, 'unit_scope' => 'Same unit', 'threshold' => 0, 'required' => true],
                    ['order' => 2, 'stage_level' => 2, 'stage_name' => 'Tahap 2: General Manager', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                ],
            ],
            [
                'id'            => 'WFL-002',
                'module'        => 'Cashflow',
                'process'       => 'Transaction Verification',
                'workflow_type' => 'submission',
                'mode'          => 'Single',
                'status'        => 'Active',
                'is_customized' => false,
                'stages' => [
                    ['order' => 1, 'stage_level' => 1, 'stage_name' => 'Tahap 1: Verifikasi Keuangan', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Finance Reviewer', 'approver' => null, 'unit_scope' => 'Same unit', 'threshold' => 0, 'required' => true],
                ],
            ],
            [
                'id'            => 'WFL-003',
                'module'        => 'Project Customer',
                'process'       => 'Site Report',
                'workflow_type' => 'progress',
                'mode'          => 'Layered',
                'status'        => 'Active',
                'is_customized' => false,
                'stages' => [
                    ['order' => 1, 'stage_level' => 1, 'stage_name' => 'Tahap 1: Branch Manager', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Branch Manager', 'approver' => null, 'unit_scope' => 'Same unit', 'threshold' => 0, 'required' => true],
                    ['order' => 2, 'stage_level' => 2, 'stage_name' => 'Tahap 2: Commercial', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Staff Commercial', 'approver' => 'Andi Sultan', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                    ['order' => 3, 'stage_level' => 3, 'stage_name' => 'Tahap 3: General Manager', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                ],
            ],
            [
                'id'            => 'WFL-004',
                'module'        => 'Project Customer',
                'process'       => 'Master Project',
                'workflow_type' => 'submission',
                'mode'          => 'Layered',
                'status'        => 'Active',
                'is_customized' => false,
                'stages' => [
                    ['order' => 1, 'stage_level' => 1, 'stage_name' => 'Tahap 1: Branch Manager', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Branch Manager', 'approver' => null, 'unit_scope' => 'Same unit', 'threshold' => 0, 'required' => true],
                    ['order' => 2, 'stage_level' => 2, 'stage_name' => 'Tahap 2: Commercial', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Staff Commercial', 'approver' => 'Andi Sultan', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                    ['order' => 3, 'stage_level' => 3, 'stage_name' => 'Tahap 3: General Manager', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                ],
            ],
            [
                'id'            => 'WFL-005',
                'module'        => 'Project Customer',
                'process'       => 'Revisi Kontrak / Addendum',
                'workflow_type' => 'revision',
                'mode'          => 'Layered',
                'status'        => 'Active',
                'is_customized' => false,
                'stages' => [
                    ['order' => 1, 'stage_level' => 1, 'stage_name' => 'Tahap 1: Branch Manager', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Branch Manager', 'approver' => null, 'unit_scope' => 'Same unit', 'threshold' => 0, 'required' => true],
                    ['order' => 2, 'stage_level' => 2, 'stage_name' => 'Tahap 2: Commercial', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Staff Commercial', 'approver' => 'Andi Sultan', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                    ['order' => 3, 'stage_level' => 3, 'stage_name' => 'Tahap 3: General Manager', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                ],
            ],
            [
                'id'            => 'WFL-006',
                'module'        => 'Internal Construction',
                'process'       => 'Master Pekerjaan',
                'workflow_type' => 'submission',
                'mode'          => 'Layered',
                'status'        => 'Active',
                'is_customized' => false,
                'stages' => [
                    ['order' => 1, 'stage_level' => 1, 'stage_name' => 'Tahap 1: Branch Manager', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Branch Manager', 'approver' => null, 'unit_scope' => 'Same unit', 'threshold' => 0, 'required' => true],
                    ['order' => 2, 'stage_level' => 2, 'stage_name' => 'Tahap 2: Commercial', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Staff Commercial', 'approver' => 'Andi Sultan', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                    ['order' => 3, 'stage_level' => 3, 'stage_name' => 'Tahap 3: General Manager', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                ],
            ],
            [
                'id'            => 'WFL-007',
                'module'        => 'Internal Construction',
                'process'       => 'Weekly Progress',
                'workflow_type' => 'progress',
                'mode'          => 'Layered',
                'status'        => 'Active',
                'is_customized' => false,
                'stages' => [
                    ['order' => 1, 'stage_level' => 1, 'stage_name' => 'Tahap 1: Branch Manager', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Branch Manager', 'approver' => null, 'unit_scope' => 'Same unit', 'threshold' => 0, 'required' => true],
                    ['order' => 2, 'stage_level' => 2, 'stage_name' => 'Tahap 2: Commercial', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Staff Commercial', 'approver' => 'Andi Sultan', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                    ['order' => 3, 'stage_level' => 3, 'stage_name' => 'Tahap 3: General Manager', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                ],
            ],
            [
                'id'            => 'WFL-008',
                'module'        => 'Internal Construction',
                'process'       => 'Revisi Kontrak / Addendum',
                'workflow_type' => 'revision',
                'mode'          => 'Layered',
                'status'        => 'Active',
                'is_customized' => false,
                'stages' => [
                    ['order' => 1, 'stage_level' => 1, 'stage_name' => 'Tahap 1: Branch Manager', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Branch Manager', 'approver' => null, 'unit_scope' => 'Same unit', 'threshold' => 0, 'required' => true],
                    ['order' => 2, 'stage_level' => 2, 'stage_name' => 'Tahap 2: Commercial', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Staff Commercial', 'approver' => 'Andi Sultan', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                    ['order' => 3, 'stage_level' => 3, 'stage_name' => 'Tahap 3: General Manager', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                ],
            ],
            [
                'id'            => 'WFL-009',
                'module'        => 'Legal Operations',
                'process'       => 'Legal Request',
                'workflow_type' => 'submission',
                'mode'          => 'Layered',
                'status'        => 'Active',
                'is_customized' => false,
                'stages' => [
                    ['order' => 1, 'stage_level' => 1, 'stage_name' => 'Tahap 1: Legal Review', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'Legal Officer', 'approver' => null, 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
                    ['order' => 2, 'stage_level' => 2, 'stage_name' => 'Tahap 2: General Manager', 'is_sequential' => false, 'sub_order' => 1, 'role' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif', 'unit_scope' => 'All units', 'threshold' => 0, 'required' => true],
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
                    'workflow_id'   => $workflow->id,
                    'order'         => $stg['order'],
                    'stage_level'   => $stg['stage_level'] ?? 1,
                    'stage_name'    => $stg['stage_name'] ?? ('Tahap ' . ($stg['stage_level'] ?? 1)),
                    'is_sequential' => (bool) ($stg['is_sequential'] ?? false),
                    'sub_order'     => $stg['sub_order'] ?? 1,
                    'role'          => $stg['role'],
                    'approver'      => $stg['approver'] ?: null,
                    'unit_scope'    => $stg['unit_scope'] ?? 'All units',
                    'threshold'     => $stg['threshold'] ?? 0,
                    'required'      => $stg['required'] ?? true,
                ]);
            }
        }
    }
}
