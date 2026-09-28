<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerProjectSeeder extends Seeder
{
    public function run(): void
    {
        $approvals = function ($stage2State) {
            return [
                ['stage' => 'Site Supervisor', 'approver' => 'Rizal Pratama', 'date' => '2026-08-24', 'state' => 'Approved'],
                ['stage' => 'Staff Commercial', 'approver' => 'Andi Sultan', 'date' => $stage2State === 'Approved' ? '2026-08-26' : null, 'state' => $stage2State],
                ['stage' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif', 'date' => null, 'state' => 'Pending'],
            ];
        };

        $projects = [
            [
                'id' => 'CPR-2026-00101',
                'customer' => 'PT Nusantara Energi',
                'name' => 'Pembangunan Fasilitas Workshop Terpadu',
                'contract_number' => 'CTR-2026-00124',
                'po_number' => 'PO-NE-2026-0451',
                'unit' => 'Cabang Balikpapan',
                'location' => 'Balikpapan, Kalimantan Timur',
                'contract_value' => 8450000000,
                'target' => 62,
                'actual' => 58,
                'deadline' => '2026-12-20',
                'start_date' => '2026-02-10',
                'period' => '2026-Q3',
                'status' => 'Delayed',
                'pic' => 'Rizal Pratama',
                'payment_term' => 'Termin 30/40/30',
                'retention' => '5% ditahan 6 bulan',
                'scopes' => [
                    ['scope' => 'Pekerjaan Persiapan', 'weight' => 10, 'target' => 100, 'actual' => 100],
                    ['scope' => 'Struktur Bangunan', 'weight' => 35, 'target' => 75, 'actual' => 68],
                    ['scope' => 'Arsitektur & Finishing', 'weight' => 30, 'target' => 45, 'actual' => 40],
                    ['scope' => 'MEP', 'weight' => 25, 'target' => 40, 'actual' => 36],
                ],
                'weekly' => [
                    ['period' => 'W-32', 'target' => 52, 'actual' => 50, 'note' => 'Pengecoran lantai 2 selesai', 'reported_by' => 'Rizal Pratama'],
                    ['period' => 'W-33', 'target' => 57, 'actual' => 54, 'note' => 'Curah hujan tinggi, 2 hari berhenti', 'reported_by' => 'Rizal Pratama'],
                    ['period' => 'W-34', 'target' => 62, 'actual' => 58, 'note' => 'Mobilisasi material rangka atap', 'reported_by' => 'Rizal Pratama'],
                ],
                'issues' => [
                    ['title' => 'Keterlambatan pengiriman rangka baja', 'severity' => 'High', 'action' => 'Percepat PO alternatif vendor lokal', 'owner' => 'Procurement', 'date' => '2026-08-18', 'status' => 'In Progress'],
                    ['title' => 'Akses jalan proyek rusak', 'severity' => 'Medium', 'action' => 'Perbaikan jalan sementara oleh subkon', 'owner' => 'Site Team', 'date' => '2026-08-21', 'status' => 'Open'],
                ],
                'photos' => [
                    ['caption' => 'Progress struktur lantai 2', 'date' => '2026-08-20'],
                    ['caption' => 'Area workshop sisi timur', 'date' => '2026-08-22'],
                    ['caption' => 'Pemasangan bekisting', 'date' => '2026-08-24'],
                ],
                'approvals' => $approvals('Waiting'),
            ],
            [
                'id' => 'CPR-2026-00102',
                'customer' => 'PT Bahari Logistik',
                'name' => 'Renovasi Gudang Distribusi Zona B',
                'contract_number' => 'CTR-2026-00131',
                'po_number' => 'PO-BL-2026-0122',
                'unit' => 'Cabang Surabaya',
                'location' => 'Surabaya, Jawa Timur',
                'contract_value' => 3120000000,
                'target' => 80,
                'actual' => 82,
                'deadline' => '2026-10-05',
                'start_date' => '2026-03-04',
                'period' => '2026-Q3',
                'status' => 'On Schedule',
                'pic' => 'Dimas Ardiansyah',
                'payment_term' => 'Termin 40/40/20',
                'retention' => '5% ditahan 3 bulan',
                'scopes' => [
                    ['scope' => 'Bongkar & Persiapan', 'weight' => 15, 'target' => 100, 'actual' => 100],
                    ['scope' => 'Perkuatan Struktur', 'weight' => 35, 'target' => 85, 'actual' => 88],
                    ['scope' => 'Atap & Dinding', 'weight' => 30, 'target' => 75, 'actual' => 78],
                    ['scope' => 'Elektrikal', 'weight' => 20, 'target' => 65, 'actual' => 66],
                ],
                'weekly' => [
                    ['period' => 'W-32', 'target' => 70, 'actual' => 72, 'note' => 'Pemasangan rangka atap tuntas', 'reported_by' => 'Dimas Ardiansyah'],
                    ['period' => 'W-33', 'target' => 75, 'actual' => 77, 'note' => 'Instalasi panel listrik utama', 'reported_by' => 'Dimas Ardiansyah'],
                    ['period' => 'W-34', 'target' => 80, 'actual' => 82, 'note' => 'Finishing dinding zona B2', 'reported_by' => 'Dimas Ardiansyah'],
                ],
                'issues' => [
                    ['title' => 'Perubahan layout rak oleh customer', 'severity' => 'Low', 'action' => 'Ajukan variation order', 'owner' => 'Commercial', 'date' => '2026-08-15', 'status' => 'Closed'],
                ],
                'photos' => [
                    ['caption' => 'Rangka atap zona B', 'date' => '2026-08-19'],
                    ['caption' => 'Panel listrik utama', 'date' => '2026-08-23'],
                ],
                'approvals' => $approvals('Approved'),
            ],
            [
                'id' => 'CPR-2026-00103',
                'customer' => 'PT Prima Agro Sentosa',
                'name' => 'Pembangunan Silo Penyimpanan 2x500 Ton',
                'contract_number' => 'CTR-2026-00140',
                'po_number' => 'PO-PA-2026-0088',
                'unit' => 'Unit Samarinda',
                'location' => 'Samarinda, Kalimantan Timur',
                'contract_value' => 5780000000,
                'target' => 45,
                'actual' => 30,
                'deadline' => '2026-11-28',
                'start_date' => '2026-04-15',
                'period' => '2026-Q3',
                'status' => 'Delayed',
                'pic' => 'Nurul Fitriani',
                'payment_term' => 'Termin 25/50/25',
                'retention' => '5% ditahan 6 bulan',
                'scopes' => [
                    ['scope' => 'Pondasi', 'weight' => 30, 'target' => 90, 'actual' => 70],
                    ['scope' => 'Fabrikasi Silo', 'weight' => 40, 'target' => 35, 'actual' => 20],
                    ['scope' => 'Erection & Instalasi', 'weight' => 20, 'target' => 10, 'actual' => 0],
                    ['scope' => 'Commissioning', 'weight' => 10, 'target' => 0, 'actual' => 0],
                ],
                'weekly' => [
                    ['period' => 'W-32', 'target' => 36, 'actual' => 26, 'note' => 'Perbaikan tanah lunak area pondasi', 'reported_by' => 'Nurul Fitriani'],
                    ['period' => 'W-33', 'target' => 40, 'actual' => 28, 'note' => 'Menunggu approval shop drawing', 'reported_by' => 'Nurul Fitriani'],
                    ['period' => 'W-34', 'target' => 45, 'actual' => 30, 'note' => 'Fabrikasi plat silo tahap 1', 'reported_by' => 'Nurul Fitriani'],
                ],
                'issues' => [
                    ['title' => 'Kondisi tanah tidak sesuai soil test awal', 'severity' => 'High', 'action' => 'Re-desain pondasi + klaim biaya tambah', 'owner' => 'Engineering', 'date' => '2026-08-10', 'status' => 'In Progress'],
                ],
                'photos' => [
                    ['caption' => 'Galian pondasi silo 1', 'date' => '2026-08-18'],
                ],
                'approvals' => $approvals('Waiting'),
            ],
            [
                'id' => 'CPR-2026-00104',
                'customer' => 'PT Cakra Mineral Utama',
                'name' => 'Perawatan Conveyor Line 3',
                'contract_number' => 'CTR-2026-00147',
                'po_number' => 'PO-CM-2026-0310',
                'unit' => 'Project Morowali',
                'location' => 'Morowali, Sulawesi Tengah',
                'contract_value' => 2240000000,
                'target' => 95,
                'actual' => 95,
                'deadline' => '2026-09-15',
                'start_date' => '2026-05-02',
                'period' => '2026-Q2',
                'status' => 'Waiting Approval',
                'pic' => 'Bagas Setiawan',
                'payment_term' => 'Termin 50/50',
                'retention' => 'Tanpa retensi',
                'scopes' => [
                    ['scope' => 'Inspeksi Awal', 'weight' => 20, 'target' => 100, 'actual' => 100],
                    ['scope' => 'Penggantian Roller', 'weight' => 45, 'target' => 100, 'actual' => 100],
                    ['scope' => 'Alignment & Testing', 'weight' => 35, 'target' => 88, 'actual' => 86],
                ],
                'weekly' => [
                    ['period' => 'W-32', 'target' => 85, 'actual' => 85, 'note' => 'Penggantian roller selesai', 'reported_by' => 'Bagas Setiawan'],
                    ['period' => 'W-33', 'target' => 90, 'actual' => 90, 'note' => 'Alignment line 3 tahap 1', 'reported_by' => 'Bagas Setiawan'],
                    ['period' => 'W-34', 'target' => 95, 'actual' => 95, 'note' => 'Menunggu BM approval laporan akhir', 'reported_by' => 'Bagas Setiawan'],
                ],
                'issues' => [],
                'photos' => [
                    ['caption' => 'Roller baru terpasang', 'date' => '2026-08-21'],
                    ['caption' => 'Testing conveyor line 3', 'date' => '2026-08-24'],
                ],
                'approvals' => $approvals('Waiting'),
            ],
            [
                'id' => 'CPR-2026-00105',
                'customer' => 'PT Sinar Bangun Jaya',
                'name' => 'Fit Out Kantor Regional Lantai 5',
                'contract_number' => 'CTR-2026-00152',
                'po_number' => 'PO-SB-2026-0019',
                'unit' => 'Head Office',
                'location' => 'Jakarta Selatan, DKI Jakarta',
                'contract_value' => 1680000000,
                'target' => 100,
                'actual' => 100,
                'deadline' => '2026-07-30',
                'start_date' => '2026-01-20',
                'period' => '2026-Q1',
                'status' => 'Completed',
                'pic' => 'Anisa Rahmawati',
                'payment_term' => 'Termin 30/60/10',
                'retention' => '5% ditahan 3 bulan',
                'scopes' => [
                    ['scope' => 'Partisi & Plafond', 'weight' => 40, 'target' => 100, 'actual' => 100],
                    ['scope' => 'Furniture', 'weight' => 30, 'target' => 100, 'actual' => 100],
                    ['scope' => 'Elektrikal & Data', 'weight' => 30, 'target' => 100, 'actual' => 100],
                ],
                'weekly' => [
                    ['period' => 'W-28', 'target' => 92, 'actual' => 92, 'note' => 'Instalasi data point selesai', 'reported_by' => 'Anisa Rahmawati'],
                    ['period' => 'W-29', 'target' => 97, 'actual' => 97, 'note' => 'Pemasangan furniture', 'reported_by' => 'Anisa Rahmawati'],
                    ['period' => 'W-30', 'target' => 100, 'actual' => 100, 'note' => 'Serah terima pekerjaan', 'reported_by' => 'Anisa Rahmawati'],
                ],
                'issues' => [],
                'photos' => [
                    ['caption' => 'Area kerja lantai 5', 'date' => '2026-07-28'],
                ],
                'approvals' => [
                    ['stage' => 'Site Supervisor', 'approver' => 'Anisa Rahmawati', 'date' => '2026-07-29', 'state' => 'Approved'],
                    ['stage' => 'Staff Commercial', 'approver' => 'Andi Sultan', 'date' => '2026-07-30', 'state' => 'Approved'],
                    ['stage' => 'General Manager', 'approver' => 'Lazuardhi Hilman Lathif', 'date' => '2026-07-31', 'state' => 'Approved'],
                ],
            ],
        ];

        foreach ($projects as $item) {
            DB::table('customer_projects')->insert([
                'id' => $item['id'],
                'customer' => $item['customer'],
                'name' => $item['name'],
                'contract_number' => $item['contract_number'],
                'po_number' => $item['po_number'],
                'unit' => $item['unit'],
                'location' => $item['location'],
                'contract_value' => $item['contract_value'],
                'target' => $item['target'],
                'actual' => $item['actual'],
                'deadline' => $item['deadline'],
                'start_date' => $item['start_date'],
                'period' => $item['period'],
                'status' => $item['status'],
                'pic' => $item['pic'],
                'payment_term' => $item['payment_term'],
                'retention' => $item['retention'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($item['scopes'] as $s) {
                DB::table('customer_project_scopes')->insert([
                    'project_id' => $item['id'],
                    'scope' => $s['scope'],
                    'weight' => $s['weight'],
                    'target' => $s['target'],
                    'actual' => $s['actual'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($item['weekly'] as $w) {
                DB::table('customer_project_weekly_progresses')->insert([
                    'project_id' => $item['id'],
                    'period' => $w['period'],
                    'target' => $w['target'],
                    'actual' => $w['actual'],
                    'note' => $w['note'],
                    'reported_by' => $w['reported_by'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($item['issues'] as $iss) {
                DB::table('customer_project_issues')->insert([
                    'project_id' => $item['id'],
                    'title' => $iss['title'],
                    'severity' => $iss['severity'],
                    'action' => $iss['action'],
                    'owner' => $iss['owner'],
                    'date' => $iss['date'],
                    'status' => $iss['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($item['photos'] as $p) {
                DB::table('customer_project_photos')->insert([
                    'project_id' => $item['id'],
                    'caption' => $p['caption'],
                    'date' => $p['date'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($item['approvals'] as $app) {
                DB::table('customer_project_approvals')->insert([
                    'project_id' => $item['id'],
                    'stage' => $app['stage'],
                    'approver' => $app['approver'],
                    'date' => $app['date'],
                    'state' => $app['state'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
