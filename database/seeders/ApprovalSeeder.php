<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $financeFlow = [
            ['order' => 1, 'name' => 'Unit Review', 'role' => 'Unit Head'],
            ['order' => 2, 'name' => 'Finance Approval', 'role' => 'Finance Reviewer'],
            ['order' => 3, 'name' => 'SSC Final Approval', 'role' => 'SSC Manager'],
        ];

        $contractFlow = [
            ['order' => 1, 'name' => 'Legal Review', 'role' => 'Legal Officer'],
            ['order' => 2, 'name' => 'SSC Approval', 'role' => 'SSC Approver'],
            ['order' => 3, 'name' => 'Director Sign-off', 'role' => 'Director'],
        ];

        $approvals = [
            [
                'id' => 'CF-2026-00312',
                'module' => 'Cashflow',
                'request' => 'Cashflow operasional Agustus',
                'summary' => 'Pengajuan arus kas operasional bulan Agustus 2026 untuk Head Office.',
                'unit' => 'SSC Works — Head Office',
                'submitted_by' => 'Rian Pratama',
                'submitted_by_id' => 'user-rian',
                'submitted_date' => '17 Agu 2026',
                'period' => 'Agustus 2026',
                'amount' => 425000000,
                'amount_label' => 'Rp 425.000.000',
                'stages' => $financeFlow,
                'current_stage' => 2,
                'priority' => 'High',
                'status' => 'Waiting',
                'due_date' => 'Hari ini, 17:00',
                'attachments' => [
                    ['name' => 'cashflow-agustus-2026.xlsx', 'size' => '248 KB', 'type' => 'XLSX'],
                    ['name' => 'rekap-bank.pdf', 'size' => '1.1 MB', 'type' => 'PDF'],
                ],
                'history' => [
                    ['actor' => 'Rian Pratama', 'role' => 'Staff Finance', 'action' => 'Submitted', 'at' => '17 Agu 2026 09:12'],
                    ['actor' => 'Dwi Santoso', 'role' => 'Unit Head', 'action' => 'Approved', 'note' => 'Sesuai plan bulanan.', 'at' => '17 Agu 2026 13:40'],
                ],
                'notes' => 'Mohon dicek selisih kas kecil pada baris 24.',
            ],
            [
                'id' => 'CTR-2026-00124',
                'module' => 'Contract',
                'request' => 'Kontrak Vendor PT Anugerah Karya',
                'summary' => 'Perpanjangan kontrak jasa maintenance selama 12 bulan.',
                'unit' => 'SSC Works — Balikpapan',
                'submitted_by' => 'Nadia Kusuma',
                'submitted_by_id' => 'user-nadia',
                'submitted_date' => '16 Agu 2026',
                'period' => 'Agustus 2026',
                'amount' => 1250000000,
                'amount_label' => 'Rp 1.250.000.000',
                'stages' => $contractFlow,
                'current_stage' => 2,
                'priority' => 'High',
                'status' => 'In Review',
                'due_date' => 'Besok, 12:00',
                'attachments' => [
                    ['name' => 'draft-kontrak-v3.pdf', 'size' => '820 KB', 'type' => 'PDF'],
                    ['name' => 'legal-review-note.docx', 'size' => '96 KB', 'type' => 'DOCX'],
                ],
                'history' => [
                    ['actor' => 'Nadia Kusuma', 'role' => 'Contract Admin', 'action' => 'Submitted', 'at' => '16 Agu 2026 10:02'],
                    ['actor' => 'Bagus Herlambang', 'role' => 'Legal Officer', 'action' => 'Approved', 'note' => 'Klausul pembayaran sudah disesuaikan.', 'at' => '16 Agu 2026 16:20'],
                ],
                'notes' => 'Perhatikan termin pembayaran 30 hari.',
            ],
            [
                'id' => 'LGL-2026-00087',
                'module' => 'Legal',
                'request' => 'Review MoU kerja sama logistik',
                'summary' => 'Permintaan review legal atas MoU dengan mitra logistik regional.',
                'unit' => 'SSC Works — Surabaya',
                'submitted_by' => 'Ayu Larasati',
                'submitted_by_id' => 'user-ayu',
                'submitted_date' => '15 Agu 2026',
                'period' => 'Agustus 2026',
                'amount' => null,
                'amount_label' => '—',
                'stages' => $contractFlow,
                'current_stage' => 1,
                'priority' => 'Medium',
                'status' => 'Waiting',
                'due_date' => '20 Agu 2026',
                'attachments' => [
                    ['name' => 'mou-logistik.pdf', 'size' => '540 KB', 'type' => 'PDF'],
                ],
                'history' => [
                    ['actor' => 'Ayu Larasati', 'role' => 'Admin Legal', 'action' => 'Submitted', 'at' => '15 Agu 2026 08:45'],
                ],
                'notes' => '',
            ],
            [
                'id' => 'BGT-2026-00045',
                'module' => 'Budgeting',
                'request' => 'Revisi budget Q4 unit Palu',
                'summary' => 'Realokasi anggaran operasional kuartal empat.',
                'unit' => 'SSC Works — Palu',
                'submitted_by' => 'Virga Damayanti',
                'submitted_by_id' => 'user-virga',
                'submitted_date' => '14 Agu 2026',
                'period' => 'Agustus 2026',
                'amount' => 310000000,
                'amount_label' => 'Rp 310.000.000',
                'stages' => $financeFlow,
                'current_stage' => 2,
                'priority' => 'Medium',
                'status' => 'Waiting',
                'due_date' => '21 Agu 2026',
                'attachments' => [
                    ['name' => 'revisi-budget-q4.xlsx', 'size' => '180 KB', 'type' => 'XLSX'],
                ],
                'history' => [
                    ['actor' => 'Virga Damayanti', 'role' => 'Account Payable', 'action' => 'Submitted', 'at' => '14 Agu 2026 11:15'],
                    ['actor' => 'Dwi Santoso', 'role' => 'Unit Head', 'action' => 'Approved', 'at' => '14 Agu 2026 15:00'],
                ],
                'notes' => 'Diajukan oleh Anda sendiri.',
            ],
            [
                'id' => 'KSI-2026-00019',
                'module' => 'Konstruksi Internal',
                'request' => 'Renovasi gudang Samarinda',
                'summary' => 'Pekerjaan renovasi atap dan lantai gudang unit Samarinda.',
                'unit' => 'SSC Works — Samarinda',
                'submitted_by' => 'Hendra Wijaya',
                'submitted_by_id' => 'user-hendra',
                'submitted_date' => '10 Agu 2026',
                'period' => 'Agustus 2026',
                'amount' => 780000000,
                'amount_label' => 'Rp 780.000.000',
                'stages' => $financeFlow,
                'current_stage' => 2,
                'priority' => 'High',
                'status' => 'Overdue',
                'due_date' => '13 Agu 2026',
                'attachments' => [
                    ['name' => 'rab-renovasi.pdf', 'size' => '1.4 MB', 'type' => 'PDF'],
                    ['name' => 'foto-kondisi.zip', 'size' => '6.2 MB', 'type' => 'ZIP'],
                ],
                'history' => [
                    ['actor' => 'Hendra Wijaya', 'role' => 'Site Engineer', 'action' => 'Submitted', 'at' => '10 Agu 2026 09:30'],
                    ['actor' => 'Dwi Santoso', 'role' => 'Unit Head', 'action' => 'Approved', 'at' => '11 Agu 2026 08:10'],
                ],
                'notes' => 'Sudah melewati batas waktu approval.',
            ],
            [
                'id' => 'PRJ-2026-00203',
                'module' => 'Project Customer',
                'request' => 'Adendum proyek PT Sinar Baru',
                'summary' => 'Penambahan scope pekerjaan instalasi fase dua.',
                'unit' => 'SSC Works — Balikpapan',
                'submitted_by' => 'Fajar Nugroho',
                'submitted_by_id' => 'user-fajar',
                'submitted_date' => '12 Agu 2026',
                'period' => 'Agustus 2026',
                'amount' => 560000000,
                'amount_label' => 'Rp 560.000.000',
                'stages' => $contractFlow,
                'current_stage' => 3,
                'priority' => 'Low',
                'status' => 'In Review',
                'due_date' => '22 Agu 2026',
                'attachments' => [
                    ['name' => 'adendum-fase2.pdf', 'size' => '690 KB', 'type' => 'PDF'],
                ],
                'history' => [
                    ['actor' => 'Fajar Nugroho', 'role' => 'Project Admin', 'action' => 'Submitted', 'at' => '12 Agu 2026 14:05'],
                    ['actor' => 'Bagus Herlambang', 'role' => 'Legal Officer', 'action' => 'Approved', 'at' => '12 Agu 2026 17:40'],
                    ['actor' => 'Virga Damayanti', 'role' => 'SSC Approver', 'action' => 'Approved', 'at' => '13 Agu 2026 09:00'],
                ],
                'notes' => '',
            ],
            [
                'id' => 'CF-2026-00298',
                'module' => 'Cashflow',
                'request' => 'Cashflow operasional Juli',
                'summary' => 'Arus kas operasional Juli 2026 unit Surabaya.',
                'unit' => 'SSC Works — Surabaya',
                'submitted_by' => 'Rian Pratama',
                'submitted_by_id' => 'user-rian',
                'submitted_date' => '05 Agu 2026',
                'period' => 'Juli 2026',
                'amount' => 398000000,
                'amount_label' => 'Rp 398.000.000',
                'stages' => $financeFlow,
                'current_stage' => 3,
                'priority' => 'Medium',
                'status' => 'Approved',
                'due_date' => '06 Agu 2026',
                'attachments' => [
                    ['name' => 'cashflow-juli-2026.xlsx', 'size' => '232 KB', 'type' => 'XLSX'],
                ],
                'history' => [
                    ['actor' => 'Rian Pratama', 'role' => 'Staff Finance', 'action' => 'Submitted', 'at' => '05 Agu 2026 08:20'],
                    ['actor' => 'Dwi Santoso', 'role' => 'Unit Head', 'action' => 'Approved', 'at' => '05 Agu 2026 10:00'],
                    ['actor' => 'Virga Damayanti', 'role' => 'Finance Reviewer', 'action' => 'Approved', 'note' => 'Sesuai realisasi.', 'at' => '05 Agu 2026 15:30'],
                ],
                'notes' => '',
            ],
            [
                'id' => 'LGL-2026-00079',
                'module' => 'Legal',
                'request' => 'Perjanjian kerahasiaan vendor IT',
                'summary' => 'NDA dengan vendor penyedia layanan IT.',
                'unit' => 'SSC Works — Head Office',
                'submitted_by' => 'Ayu Larasati',
                'submitted_by_id' => 'user-ayu',
                'submitted_date' => '02 Agu 2026',
                'period' => 'Juli 2026',
                'amount' => null,
                'amount_label' => '—',
                'stages' => $contractFlow,
                'current_stage' => 2,
                'priority' => 'Low',
                'status' => 'Returned',
                'due_date' => '04 Agu 2026',
                'attachments' => [
                    ['name' => 'nda-vendor-it.pdf', 'size' => '310 KB', 'type' => 'PDF'],
                ],
                'history' => [
                    ['actor' => 'Ayu Larasati', 'role' => 'Admin Legal', 'action' => 'Submitted', 'at' => '02 Agu 2026 09:10'],
                    ['actor' => 'Virga Damayanti', 'role' => 'SSC Approver', 'action' => 'Returned', 'note' => 'Lampirkan profil vendor terbaru.', 'at' => '03 Agu 2026 11:25'],
                ],
                'notes' => 'Menunggu revisi pengaju.',
            ],
        ];

        foreach ($approvals as $item) {
            DB::table('approvals')->insert([
                'id' => $item['id'],
                'module' => $item['module'],
                'request' => $item['request'],
                'summary' => $item['summary'],
                'unit' => $item['unit'],
                'submitted_by' => $item['submitted_by'],
                'submitted_by_id' => $item['submitted_by_id'],
                'submitted_date' => $item['submitted_date'],
                'period' => $item['period'],
                'amount' => $item['amount'],
                'amount_label' => $item['amount_label'],
                'current_stage' => $item['current_stage'],
                'priority' => $item['priority'],
                'status' => $item['status'],
                'due_date' => $item['due_date'],
                'notes' => $item['notes'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($item['stages'] as $stg) {
                DB::table('approval_stages')->insert([
                    'approval_id' => $item['id'],
                    'order' => $stg['order'],
                    'name' => $stg['name'],
                    'role' => $stg['role'],
                    'approver' => $stg['approver'] ?? null,
                    'unit_scope' => $stg['unitScope'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($item['attachments'] as $att) {
                DB::table('approval_attachments')->insert([
                    'approval_id' => $item['id'],
                    'name' => $att['name'],
                    'size' => $att['size'],
                    'type' => $att['type'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($item['history'] as $hst) {
                DB::table('approval_histories')->insert([
                    'approval_id' => $item['id'],
                    'actor' => $hst['actor'],
                    'role' => $hst['role'],
                    'action' => $hst['action'],
                    'note' => $hst['note'] ?? null,
                    'at' => $hst['at'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
