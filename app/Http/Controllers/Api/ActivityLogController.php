<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Seed default seed logs if table is completely empty
        if (ActivityLog::count() === 0) {
            $this->seedInitialLogs();
        }

        $query = ActivityLog::query();

        if ($request->has('module') && $request->module !== 'ALL' && !empty($request->module)) {
            $query->where('module', $request->module);
        }

        $logs = $query->orderBy('created_at', 'desc')
                      ->orderBy('id', 'desc')
                      ->limit(100)
                      ->get();

        return response()->json($logs->map(fn($l) => $l->toFrontendFormat()));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|string',
            'action' => 'required|string',
            'target' => 'required|string',
            'targetCode' => 'nullable|string',
            'targetTitle' => 'nullable|string',
            'detail' => 'nullable|string',
            'statusType' => 'nullable|string',
            'href' => 'nullable|string',
            'actor' => 'nullable|array',
            'actor.name' => 'nullable|string',
            'actor.role' => 'nullable|string',
            'actor.email' => 'nullable|string',
            'actor.unit' => 'nullable|string',
            'actor.avatar' => 'nullable|string',
        ]);

        $actor = $validated['actor'] ?? [];

        $log = ActivityLog::create([
            'user_id' => $actor['email'] ?? null,
            'user_name' => $actor['name'] ?? 'Admin HO',
            'user_unit' => $actor['unit'] ?? 'SSC Samarinda',
            'user_role' => $actor['role'] ?? 'Staff',
            'module' => $validated['module'],
            'action' => $validated['action'],
            'action_type' => $validated['statusType'] ?? 'info',
            'entity_type' => $validated['module'],
            'entity_id' => $validated['targetCode'] ?? null,
            'entity_title' => $validated['targetTitle'] ?? $validated['target'],
            'description' => $validated['detail'] ?? null,
            'metadata' => [
                'email' => $actor['email'] ?? null,
                'avatar' => $actor['avatar'] ?? null,
                'href' => $validated['href'] ?? null,
            ],
        ]);

        return response()->json($log->toFrontendFormat(), 201);
    }

    private function seedInitialLogs()
    {
        $seeds = [
            [
                'user_name' => 'Andi Pratama',
                'user_role' => 'Legal Officer',
                'user_unit' => 'SSC Samarinda',
                'module' => 'LO',
                'action' => 'Simpan Draft',
                'action_type' => 'draft',
                'entity_type' => 'LegalRequest',
                'entity_id' => 'REQ-2026-0042',
                'entity_title' => 'Perjanjian Kerja Sama Sewa Lahan Workshop',
                'description' => 'Menyimpan draf permohonan legal baru dengan kelengkapan 3 lampiran dokumen.',
                'metadata' => ['email' => 'andi.pratama@sscworks.com', 'href' => '/legal'],
                'created_at' => Carbon::now()->subMinutes(12),
            ],
            [
                'user_name' => 'Budi Santoso',
                'user_role' => 'Site Engineer',
                'user_unit' => 'SSC Balikpapan',
                'module' => 'IC',
                'action' => 'Update Progress Lapangan',
                'action_type' => 'updated',
                'entity_type' => 'ConstructionWork',
                'entity_id' => 'CW-2026-0018',
                'entity_title' => 'Pembangunan Gedung Logistik Tahap 2',
                'description' => 'Deviasi progres +3.2% dengan status On Track. Pengecoran balok lantai 2 rampung.',
                'metadata' => ['email' => 'budi.santoso@sscworks.com', 'href' => '/internal-construction'],
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'user_name' => 'Rina Wijaya',
                'user_role' => 'Project Manager',
                'user_unit' => 'SSC Samarinda',
                'module' => 'CP',
                'action' => 'Submit Weekly Report',
                'action_type' => 'submitted',
                'entity_type' => 'CustomerProject',
                'entity_id' => 'CP-2026-0091',
                'entity_title' => 'Konstruksi Storage Tank 2000 m3 - PT Kaltim Prima',
                'description' => 'Laporan mingguan ke-6 diajukan untuk proses GM Review.',
                'metadata' => ['email' => 'rina.wijaya@sscworks.com', 'href' => '/customer-project'],
                'created_at' => Carbon::now()->subHours(4),
            ],
            [
                'user_name' => 'Virga Ramadhan',
                'user_role' => 'Admin HO',
                'user_unit' => 'SSC Samarinda',
                'module' => 'IC',
                'action' => 'Simpan Draft Pekerjaan',
                'action_type' => 'draft',
                'entity_type' => 'ConstructionWork',
                'entity_id' => 'CW-2026-0021',
                'entity_title' => 'Pemeliharaan Jaringan Pipa & Drainase Workshop',
                'description' => 'Menyimpan draf RAB dan rincian bobot scope pekerjaan struktur sipil.',
                'metadata' => ['email' => 'virga@sscworks.com', 'href' => '/internal-construction'],
                'created_at' => Carbon::now()->subHours(6),
            ],
            [
                'user_name' => 'Virga Damayanti',
                'user_role' => 'Admin HO',
                'user_unit' => 'SSC Works - Head Office',
                'module' => 'Approval',
                'action' => 'Approve Permintaan',
                'action_type' => 'approved',
                'entity_type' => 'Approval',
                'entity_id' => 'APV-2026-0112',
                'entity_title' => 'Cashflow Operasional Mingguan Palu',
                'description' => 'Disetujui oleh Operation Manager level 2 (Nilai: Rp 45.000.000).',
                'metadata' => ['email' => 'virga.damayanti@sscworks.com', 'href' => '/approval-center'],
                'created_at' => Carbon::now()->subHours(8),
            ],
        ];

        foreach ($seeds as $item) {
            ActivityLog::create($item);
        }
    }
}
