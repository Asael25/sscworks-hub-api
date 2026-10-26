<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConstructionWorkSeeder extends Seeder
{
    public function run(): void
    {
        $works = [
            [
                'id' => 'KI-2026-0011',
                'name' => 'Renovasi Gudang Logistik Blok B',
                'unit' => 'SSC Samarinda',
                'location' => 'Kawasan Industri Palaran, Samarinda',
                'vendor' => 'PT Karya Bangun Sejahtera',
                'contract_number' => 'CTR-2026-00124',
                'pic' => 'Virga Ramadhan',
                'start_date' => '2026-06-01',
                'target_date' => '2026-09-15',
                'period' => 'W33-2026',
                'target' => 72,
                'actual' => 68,
                'status' => 'On Schedule',
                'budget' => 1850000000,
                'quality_checked' => 42,
                'quality_passed' => 39,
                'quality_reworks' => 3,
                'quality_note' => 'Rework pada plesteran area utara.',
                'safety_manhours' => 12400,
                'safety_incidents' => 0,
                'safety_near_miss' => 2,
                'safety_toolbox' => 18,
                'scopes' => [
                    ['scope' => 'Pekerjaan Persiapan', 'weight' => 10, 'target' => 100, 'actual' => 100],
                    ['scope' => 'Struktur & Pondasi', 'weight' => 30, 'target' => 95, 'actual' => 92],
                    ['scope' => 'Arsitektur & Dinding', 'weight' => 25, 'target' => 70, 'actual' => 64],
                    ['scope' => 'MEP', 'weight' => 25, 'target' => 45, 'actual' => 40],
                    ['scope' => 'Finishing', 'weight' => 10, 'target' => 10, 'actual' => 5],
                ],
                'weekly' => [
                    ['period' => 'W30-2026', 'target' => 55, 'actual' => 53],
                    ['period' => 'W31-2026', 'target' => 61, 'actual' => 58],
                    ['period' => 'W32-2026', 'target' => 67, 'actual' => 63],
                    ['period' => 'W33-2026', 'target' => 72, 'actual' => 68],
                ],
                'issues' => [
                    ['title' => 'Keterlambatan pengiriman rangka baja', 'severity' => 'Medium', 'raised_by' => 'Site Engineer', 'date' => '2026-08-10', 'status' => 'In Progress'],
                ],
                'photos' => [
                    ['caption' => 'Progress dinding blok utara', 'date' => '2026-08-18', 'tone' => 'site'],
                    ['caption' => 'Inspeksi kualitas pengecoran', 'date' => '2026-08-16', 'tone' => 'quality'],
                    ['caption' => 'Toolbox meeting pagi', 'date' => '2026-08-15', 'tone' => 'safety'],
                ],
                'approvals' => [
                    ['stage' => 'Site Verification', 'approver' => 'Ahmad Fauzi', 'date' => '2026-08-18', 'state' => 'Approved'],
                    ['stage' => 'BM Review', 'approver' => 'Rani Saputri', 'date' => '2026-08-19', 'state' => 'Approved'],
                    ['stage' => 'Head of Operations', 'approver' => 'Dimas Prakoso', 'date' => null, 'state' => 'Waiting'],
                ],
            ],
            [
                'id' => 'KI-2026-0012',
                'name' => 'Pembangunan Kanopi Area Bongkar Muat',
                'unit' => 'SSC Balikpapan',
                'location' => 'Depo Kariangau, Balikpapan',
                'vendor' => 'PT Nusantara Prima Konstruksi',
                'contract_number' => 'CTR-2026-00131',
                'pic' => 'Rani Saputri',
                'start_date' => '2026-05-12',
                'target_date' => '2026-08-10',
                'period' => 'W33-2026',
                'target' => 95,
                'actual' => 78,
                'status' => 'Delayed',
                'budget' => 940000000,
                'quality_checked' => 28,
                'quality_passed' => 24,
                'quality_reworks' => 4,
                'quality_note' => 'Pengelasan rangka perlu perbaikan mutu.',
                'safety_manhours' => 8600,
                'safety_incidents' => 1,
                'safety_near_miss' => 3,
                'safety_toolbox' => 12,
                'scopes' => [
                    ['scope' => 'Pekerjaan Persiapan', 'weight' => 10, 'target' => 100, 'actual' => 100],
                    ['scope' => 'Pondasi Kolom', 'weight' => 25, 'target' => 100, 'actual' => 96],
                    ['scope' => 'Rangka Baja', 'weight' => 35, 'target' => 95, 'actual' => 72],
                    ['scope' => 'Atap & Talang', 'weight' => 20, 'target' => 85, 'actual' => 55],
                    ['scope' => 'Finishing', 'weight' => 10, 'target' => 80, 'actual' => 40],
                ],
                'weekly' => [
                    ['period' => 'W30-2026', 'target' => 80, 'actual' => 66],
                    ['period' => 'W31-2026', 'target' => 85, 'actual' => 70],
                    ['period' => 'W32-2026', 'target' => 90, 'actual' => 74],
                    ['period' => 'W33-2026', 'target' => 95, 'actual' => 78],
                ],
                'issues' => [
                    ['title' => 'Cuaca ekstrem menghentikan pekerjaan ketinggian', 'severity' => 'High', 'raised_by' => 'HSE Officer', 'date' => '2026-08-12', 'status' => 'Open'],
                    ['title' => 'Kekurangan tenaga welder bersertifikat', 'severity' => 'Medium', 'raised_by' => 'Site Manager', 'date' => '2026-08-05', 'status' => 'In Progress'],
                ],
                'photos' => [
                    ['caption' => 'Erection rangka baja bentang 2', 'date' => '2026-08-17', 'tone' => 'site'],
                    ['caption' => 'Uji visual sambungan las', 'date' => '2026-08-14', 'tone' => 'quality'],
                ],
                'approvals' => [
                    ['stage' => 'Site Verification', 'approver' => 'Bagus Wicaksono', 'date' => '2026-08-13', 'state' => 'Approved'],
                    ['stage' => 'BM Review', 'approver' => 'Rani Saputri', 'date' => null, 'state' => 'Waiting'],
                    ['stage' => 'Head of Operations', 'approver' => 'Dimas Prakoso', 'date' => null, 'state' => 'Pending'],
                ],
            ],
            [
                'id' => 'KI-2026-0013',
                'name' => 'Upgrade Instalasi Listrik Workshop',
                'unit' => 'SSC Jakarta',
                'location' => 'Workshop Cakung, Jakarta Timur',
                'vendor' => 'CV Mitra Teknik Mandiri',
                'contract_number' => 'CTR-2026-00140',
                'pic' => 'Dimas Prakoso',
                'start_date' => '2026-07-01',
                'target_date' => '2026-10-01',
                'period' => 'W32-2026',
                'target' => 48,
                'actual' => 47,
                'status' => 'Waiting Approval',
                'budget' => 620000000,
                'quality_checked' => 19,
                'quality_passed' => 19,
                'quality_reworks' => 0,
                'quality_note' => 'Seluruh titik uji lolos standar.',
                'safety_manhours' => 5200,
                'safety_incidents' => 0,
                'safety_near_miss' => 1,
                'safety_toolbox' => 9,
                'scopes' => [
                    ['scope' => 'Survey & Desain', 'weight' => 15, 'target' => 100, 'actual' => 100],
                    ['scope' => 'Panel Utama', 'weight' => 30, 'target' => 60, 'actual' => 58],
                    ['scope' => 'Instalasi Kabel', 'weight' => 35, 'target' => 35, 'actual' => 34],
                    ['scope' => 'Testing & Commissioning', 'weight' => 20, 'target' => 5, 'actual' => 0],
                ],
                'weekly' => [
                    ['period' => 'W30-2026', 'target' => 36, 'actual' => 35],
                    ['period' => 'W31-2026', 'target' => 42, 'actual' => 41],
                    ['period' => 'W32-2026', 'target' => 48, 'actual' => 47],
                ],
                'issues' => [],
                'photos' => [
                    ['caption' => 'Pemasangan panel distribusi', 'date' => '2026-08-12', 'tone' => 'site'],
                ],
                'approvals' => [
                    ['stage' => 'Site Verification', 'approver' => 'Yudha Pratama', 'date' => '2026-08-11', 'state' => 'Approved'],
                    ['stage' => 'BM Review', 'approver' => 'Rani Saputri', 'date' => null, 'state' => 'Waiting'],
                    ['stage' => 'Head of Operations', 'approver' => 'Dimas Prakoso', 'date' => null, 'state' => 'Pending'],
                ],
            ],
            [
                'id' => 'KI-2026-0014',
                'name' => 'Perbaikan Jalan Akses Depo Timur',
                'unit' => 'SSC Surabaya',
                'location' => 'Depo Timur Margomulyo, Surabaya',
                'vendor' => 'PT Delta Sarana Utama',
                'contract_number' => 'CTR-2026-00118',
                'pic' => 'Ahmad Fauzi',
                'start_date' => '2026-04-05',
                'target_date' => '2026-07-20',
                'period' => 'W31-2026',
                'target' => 100,
                'actual' => 100,
                'status' => 'Completed',
                'budget' => 1275000000,
                'quality_checked' => 33,
                'quality_passed' => 33,
                'quality_reworks' => 1,
                'quality_note' => 'Serah terima pekerjaan telah ditandatangani.',
                'safety_manhours' => 14100,
                'safety_incidents' => 0,
                'safety_near_miss' => 0,
                'safety_toolbox' => 22,
                'scopes' => [
                    ['scope' => 'Pekerjaan Tanah', 'weight' => 30, 'target' => 100, 'actual' => 100],
                    ['scope' => 'Lapis Pondasi', 'weight' => 30, 'target' => 100, 'actual' => 100],
                    ['scope' => 'Perkerasan Aspal', 'weight' => 30, 'target' => 100, 'actual' => 100],
                    ['scope' => 'Marka & Drainase', 'weight' => 10, 'target' => 100, 'actual' => 100],
                ],
                'weekly' => [
                    ['period' => 'W30-2026', 'target' => 96, 'actual' => 95],
                    ['period' => 'W31-2026', 'target' => 100, 'actual' => 100],
                ],
                'issues' => [],
                'photos' => [
                    ['caption' => 'Overlay aspal segmen akhir', 'date' => '2026-07-18', 'tone' => 'site'],
                    ['caption' => 'Core drill test kepadatan', 'date' => '2026-07-19', 'tone' => 'quality'],
                ],
                'approvals' => [
                    ['stage' => 'Site Verification', 'approver' => 'Ahmad Fauzi', 'date' => '2026-07-19', 'state' => 'Approved'],
                    ['stage' => 'BM Review', 'approver' => 'Rani Saputri', 'date' => '2026-07-20', 'state' => 'Approved'],
                    ['stage' => 'Head of Operations', 'approver' => 'Dimas Prakoso', 'date' => '2026-07-21', 'state' => 'Approved'],
                ],
            ],
            [
                'id' => 'KI-2026-0015',
                'name' => 'Pemasangan CCTV & Pagar Perimeter',
                'unit' => 'SSC Samarinda',
                'location' => 'Yard Sungai Kunjang, Samarinda',
                'vendor' => 'CV Mitra Teknik Mandiri',
                'contract_number' => 'CTR-2026-00147',
                'pic' => 'Bagus Wicaksono',
                'start_date' => '2026-06-20',
                'target_date' => '2026-08-19',
                'period' => 'W33-2026',
                'target' => 88,
                'actual' => 70,
                'status' => 'Delayed',
                'budget' => 480000000,
                'quality_checked' => 15,
                'quality_passed' => 13,
                'quality_reworks' => 2,
                'quality_note' => 'Sudut pandang kamera area gate perlu penyesuaian.',
                'safety_manhours' => 3800,
                'safety_incidents' => 0,
                'safety_near_miss' => 1,
                'safety_toolbox' => 7,
                'scopes' => [
                    ['scope' => 'Pemasangan Pagar', 'weight' => 40, 'target' => 95, 'actual' => 82],
                    ['scope' => 'Instalasi CCTV', 'weight' => 40, 'target' => 85, 'actual' => 62],
                    ['scope' => 'Integrasi Sistem', 'weight' => 20, 'target' => 75, 'actual' => 55],
                ],
                'weekly' => [
                    ['period' => 'W31-2026', 'target' => 74, 'actual' => 62],
                    ['period' => 'W32-2026', 'target' => 81, 'actual' => 66],
                    ['period' => 'W33-2026', 'target' => 88, 'actual' => 70],
                ],
                'issues' => [
                    ['title' => 'Perizinan galian kabel belum terbit', 'severity' => 'High', 'raised_by' => 'PIC Unit', 'date' => '2026-08-08', 'status' => 'Open'],
                ],
                'photos' => [
                    ['caption' => 'Pemasangan tiang kamera sisi barat', 'date' => '2026-08-16', 'tone' => 'site'],
                ],
                'approvals' => [
                    ['stage' => 'Site Verification', 'approver' => 'Bagus Wicaksono', 'date' => '2026-08-16', 'state' => 'Approved'],
                    ['stage' => 'BM Review', 'approver' => 'Rani Saputri', 'date' => null, 'state' => 'Returned'],
                    ['stage' => 'Head of Operations', 'approver' => 'Dimas Prakoso', 'date' => null, 'state' => 'Pending'],
                ],
            ],
            [
                'id' => 'KI-2026-0016',
                'name' => 'Instalasi Jaringan Fiber Optic & Server Workshop',
                'unit' => 'SSC Head Office',
                'location' => 'Kawasan Industri Margomulyo, Surabaya',
                'vendor' => 'PT Delta Sarana Utama',
                'contract_number' => 'CTR-2026-00155',
                'pic' => 'Fachrudi Putra Pamuji',
                'start_date' => '2026-06-01',
                'target_date' => '2026-11-30',
                'period' => 'W33-2026',
                'target' => 65,
                'actual' => 62,
                'status' => 'On Schedule',
                'budget' => 650000000,
                'quality_checked' => 20,
                'quality_passed' => 20,
                'quality_reworks' => 0,
                'quality_note' => 'Pemasangan rapi dan lolos test redaman FO.',
                'safety_manhours' => 1800,
                'safety_incidents' => 0,
                'safety_near_miss' => 0,
                'safety_toolbox' => 12,
                'scopes' => [
                    ['scope' => 'Pekerjaan Penarikan Kabel FO', 'weight' => 35, 'target' => 100, 'actual' => 95],
                    ['scope' => 'Pemasangan Rack Server & Switch', 'weight' => 35, 'target' => 80, 'actual' => 75],
                    ['scope' => 'Konfigurasi Jaringan & CCTV', 'weight' => 30, 'target' => 40, 'actual' => 30],
                ],
                'weekly' => [
                    ['period' => 'W31-2026', 'target' => 45, 'actual' => 42],
                    ['period' => 'W32-2026', 'target' => 55, 'actual' => 52],
                    ['period' => 'W33-2026', 'target' => 65, 'actual' => 62],
                ],
                'issues' => [],
                'photos' => [
                    ['caption' => 'Terminasi kabel FO rack utama', 'date' => '2026-08-20', 'tone' => 'site'],
                ],
                'approvals' => [
                    ['stage' => 'Site Verification', 'approver' => 'Fachrudi Putra Pamuji', 'date' => '2026-08-20', 'state' => 'Approved'],
                    ['stage' => 'BM Review', 'approver' => 'Budi Hartono', 'date' => '2026-08-21', 'state' => 'Approved'],
                    ['stage' => 'Head of Operations', 'approver' => 'Lazuardhi Hilman Lathif', 'date' => '2026-08-22', 'state' => 'Approved'],
                ],
            ],
        ];

        foreach ($works as $item) {
            DB::table('construction_works')->insert([
                'id' => $item['id'],
                'name' => $item['name'],
                'unit' => $item['unit'],
                'location' => $item['location'],
                'vendor' => $item['vendor'],
                'contract_number' => $item['contract_number'],
                'pic' => $item['pic'],
                'start_date' => $item['start_date'],
                'target_date' => $item['target_date'],
                'period' => $item['period'],
                'target' => $item['target'],
                'actual' => $item['actual'],
                'status' => $item['status'],
                'budget' => $item['budget'],
                'quality_checked' => $item['quality_checked'],
                'quality_passed' => $item['quality_passed'],
                'quality_reworks' => $item['quality_reworks'],
                'quality_note' => $item['quality_note'],
                'safety_manhours' => $item['safety_manhours'],
                'safety_incidents' => $item['safety_incidents'],
                'safety_near_miss' => $item['safety_near_miss'],
                'safety_toolbox' => $item['safety_toolbox'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($item['scopes'] as $s) {
                DB::table('construction_scopes')->insert([
                    'work_id' => $item['id'],
                    'scope' => $s['scope'],
                    'weight' => $s['weight'],
                    'target' => $s['target'],
                    'actual' => $s['actual'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($item['weekly'] as $w) {
                DB::table('construction_weekly_progresses')->insert([
                    'work_id' => $item['id'],
                    'period' => $w['period'],
                    'target' => $w['target'],
                    'actual' => $w['actual'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($item['issues'] as $iss) {
                DB::table('construction_issues')->insert([
                    'work_id' => $item['id'],
                    'title' => $iss['title'],
                    'severity' => $iss['severity'],
                    'raised_by' => $iss['raised_by'],
                    'date' => $iss['date'],
                    'status' => $iss['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($item['photos'] as $p) {
                DB::table('construction_photos')->insert([
                    'work_id' => $item['id'],
                    'caption' => $p['caption'],
                    'date' => $p['date'],
                    'tone' => $p['tone'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($item['approvals'] as $app) {
                DB::table('construction_approvals')->insert([
                    'work_id' => $item['id'],
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
