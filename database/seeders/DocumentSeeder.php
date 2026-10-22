<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\CustomerProject;
use App\Models\ConstructionWork;
use App\Models\LegalRequest;
use App\Services\DocumentSyncService;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $baseDocuments = [
            // 1. Arsip Historis (Masa Simpan > 5 Tahun / 60 Bulan)
            [
                'id' => 'DOC-2018-00021',
                'file_name' => 'Arsip_Faktur_Pajak_2018.pdf',
                'module' => 'Cashflow',
                'category' => 'Cashflow',
                'related_request' => 'TAX-2018-00015',
                'related_title' => 'Rekap Faktur Pajak Tahunan 2018',
                'unit' => 'Head Office',
                'file_type' => 'PDF',
                'size' => '1.8 MB',
                'uploaded_by' => 'Andi Sultan',
                'uploaded_date' => '2018-04-10',
                'period' => '2018',
                'version' => 'v1.0',
                'status' => 'Archived',
                'description' => 'Rekapitulasi berkas faktur pajak masukan & keluaran yang sudah masuk arsip digital permanen.',
                'can_upload_version' => false,
                'can_delete' => false,
                'locked' => true,
                'versions' => [
                    ['version' => 'v1.0', 'uploaded_by' => 'Andi Sultan', 'uploaded_date' => '2018-04-10', 'note' => 'Archived ledger scan'],
                ],
            ],
            [
                'id' => 'DOC-2019-00088',
                'file_name' => 'BAST_Proyek_Pabrik_Palu_2019.pdf',
                'module' => 'Customer Projects',
                'category' => 'Contracts',
                'related_request' => 'CPR-2019-00054',
                'related_title' => 'Pembangunan Pabrik Pengolahan Palu',
                'unit' => 'Cabang Surabaya',
                'file_type' => 'PDF',
                'size' => '3.5 MB',
                'uploaded_by' => 'Dewi Anggraini',
                'uploaded_date' => '2019-12-28',
                'period' => '2019',
                'version' => 'v1.0',
                'status' => 'Archived',
                'description' => 'Berita Acara Serah Terima Akhir pekerjaan konstruksi pabrik pengolahan Palu.',
                'can_upload_version' => false,
                'can_delete' => false,
                'locked' => true,
                'versions' => [
                    ['version' => 'v1.0', 'uploaded_by' => 'Dewi Anggraini', 'uploaded_date' => '2019-12-28', 'note' => 'BAST final signed'],
                ],
            ],
            [
                'id' => 'DOC-2020-00142',
                'file_name' => 'Kontrak_Pondasi_Borepile_2020.pdf',
                'module' => 'Customer Projects',
                'category' => 'Contracts',
                'related_request' => 'CPR-2020-00089',
                'related_title' => 'Pekerjaan Pondasi Borepile Workshop',
                'unit' => 'Cabang Balikpapan',
                'file_type' => 'PDF',
                'size' => '5.4 MB',
                'uploaded_by' => 'Rizal Pratama',
                'uploaded_date' => '2020-05-18',
                'period' => '2020',
                'version' => 'v2.0',
                'status' => 'Archived',
                'description' => 'Dokumen kontrak fisik dan berita acara serah terima selesai masa garansi konstruksi.',
                'can_upload_version' => false,
                'can_delete' => false,
                'locked' => true,
                'versions' => [
                    ['version' => 'v2.0', 'uploaded_by' => 'Rizal Pratama', 'uploaded_date' => '2020-05-18', 'note' => 'Final closed project contract'],
                ],
            ],
            [
                'id' => 'DOC-2021-00198',
                'file_name' => 'Laporan_Audit_Konstruksi_Q2_2021.xlsx',
                'module' => 'Internal Construction',
                'category' => 'Construction',
                'related_request' => 'CTR-2021-00045',
                'related_title' => 'Audit Struktur & K3 Konstruksi Q2 2021',
                'unit' => 'Cabang Balikpapan',
                'file_type' => 'XLSX',
                'size' => '2.3 MB',
                'uploaded_by' => 'Dimas Ardiansyah',
                'uploaded_date' => '2021-06-15',
                'period' => 'Jun 2021',
                'version' => 'v1.0',
                'status' => 'Archived',
                'description' => 'Laporan audit teknis dan kepatuhan standar keselamatan kerja konstruksi Q2 2021.',
                'can_upload_version' => false,
                'can_delete' => false,
                'locked' => true,
                'versions' => [
                    ['version' => 'v1.0', 'uploaded_by' => 'Dimas Ardiansyah', 'uploaded_date' => '2021-06-15', 'note' => 'Audit report Q2'],
                ],
            ],
            [
                'id' => 'DOC-2021-00210',
                'file_name' => 'Dokumentasi_Foto_Progress_Site_2021.zip',
                'module' => 'Customer Projects',
                'category' => 'Customer Projects',
                'related_request' => 'CPR-2021-00072',
                'related_title' => 'Dokumentasi Proyek Smelter Morowali 2021',
                'unit' => 'Project Morowali',
                'file_type' => 'ZIP',
                'size' => '14.2 MB',
                'uploaded_by' => 'Bagas Setiawan',
                'uploaded_date' => '2021-09-20',
                'period' => 'Sep 2021',
                'version' => 'v1.0',
                'status' => 'Archived',
                'description' => 'Kompilasi arsip foto drone dan milestone serah terima sipil tahap 1.',
                'can_upload_version' => false,
                'can_delete' => false,
                'locked' => true,
                'versions' => [
                    ['version' => 'v1.0', 'uploaded_by' => 'Bagas Setiawan', 'uploaded_date' => '2021-09-20', 'note' => 'Zip archive progress photo'],
                ],
            ],

            // 2. Antrean Pengarsipan Bulan-demi-Bulan (Mencapai 60 bulan berurutan tiap 1 menit simulasi)
            [
                'id' => 'DOC-2021-00234',
                'file_name' => 'Legal_Review_Perjanjian_Sewa_2021.pdf',
                'module' => 'Legal Operations',
                'category' => 'Legal',
                'related_request' => 'LGL-2021-00038',
                'related_title' => 'Perjanjian Sewa Alat Berat Excavator 2021',
                'unit' => 'Head Office',
                'file_type' => 'PDF',
                'size' => '1.4 MB',
                'uploaded_by' => 'Nadia Puspita',
                'uploaded_date' => '2021-11-15',
                'period' => 'Nov 2021',
                'version' => 'v1.1',
                'status' => 'Active',
                'description' => 'Opini legal dan draft perjanjian sewa armada alat berat tahun 2021.',
                'can_upload_version' => true,
                'can_delete' => false,
                'locked' => false,
                'versions' => [
                    ['version' => 'v1.1', 'uploaded_by' => 'Nadia Puspita', 'uploaded_date' => '2021-11-15', 'note' => 'Signed lease review'],
                ],
            ],
            [
                'id' => 'DOC-2021-00255',
                'file_name' => 'BAST_Subkontraktor_Pondasi_2021.pdf',
                'module' => 'Internal Construction',
                'category' => 'Construction',
                'related_request' => 'CTR-2021-00091',
                'related_title' => 'Berita Acara Subkon Pondasi Workshop',
                'unit' => 'Cabang Balikpapan',
                'file_type' => 'PDF',
                'size' => '2.8 MB',
                'uploaded_by' => 'Rizal Pratama',
                'uploaded_date' => '2021-12-20',
                'period' => 'Dec 2021',
                'version' => 'v1.0',
                'status' => 'Active',
                'description' => 'Berita acara serah terima pekerjaan tiang pancang subkontraktor lokal.',
                'can_upload_version' => true,
                'can_delete' => false,
                'locked' => false,
                'versions' => [
                    ['version' => 'v1.0', 'uploaded_by' => 'Rizal Pratama', 'uploaded_date' => '2021-12-20', 'note' => 'BAST subkon'],
                ],
            ],
            [
                'id' => 'DOC-2022-00278',
                'file_name' => 'Laporan_Struktur_Gudang_Baja_2022.pdf',
                'module' => 'Customer Projects',
                'category' => 'Customer Projects',
                'related_request' => 'CPR-2022-00012',
                'related_title' => 'Pembangunan Gudang Logistik Baja Ringan',
                'unit' => 'Cabang Surabaya',
                'file_type' => 'PDF',
                'size' => '6.1 MB',
                'uploaded_by' => 'Dimas Ardiansyah',
                'uploaded_date' => '2022-01-18',
                'period' => 'Jan 2022',
                'version' => 'v1.0',
                'status' => 'Active',
                'description' => 'Laporan perhitungan analisa pembebanan struktur bentang baja gudang logistik.',
                'can_upload_version' => true,
                'can_delete' => false,
                'locked' => false,
                'versions' => [
                    ['version' => 'v1.0', 'uploaded_by' => 'Dimas Ardiansyah', 'uploaded_date' => '2022-01-18', 'note' => 'Struktur calculation report'],
                ],
            ],
            [
                'id' => 'DOC-2022-00302',
                'file_name' => 'SPK_Pengadaan_Material_Batch_2022.xlsx',
                'module' => 'Vendor Management',
                'category' => 'Vendor Documents',
                'related_request' => 'VND-2022-00041',
                'related_title' => 'Surat Perintah Kerja Pengadaan Semen & Baja',
                'unit' => 'Unit Samarinda',
                'file_type' => 'XLSX',
                'size' => '940 KB',
                'uploaded_by' => 'Fajar Ramadhan',
                'uploaded_date' => '2022-02-25',
                'period' => 'Feb 2022',
                'version' => 'v1.0',
                'status' => 'Active',
                'description' => 'Surat perintah kerja pengadaan material semen curah dan besi tulangan batch 2022.',
                'can_upload_version' => true,
                'can_delete' => false,
                'locked' => false,
                'versions' => [
                    ['version' => 'v1.0', 'uploaded_by' => 'Fajar Ramadhan', 'uploaded_date' => '2022-02-25', 'note' => 'Approved SPK'],
                ],
            ],

            // 3. Dokumen Mendekati Jatuh Tempo
            [
                'id' => 'DOC-2024-00340',
                'file_name' => 'Sertifikat_Kalibrasi_Alat_Berat_2024.pdf',
                'module' => 'Internal Construction',
                'category' => 'Construction',
                'related_request' => 'CTR-2024-00105',
                'related_title' => 'Kalibrasi Load Cell Crane Workshop',
                'unit' => 'Cabang Balikpapan',
                'file_type' => 'PDF',
                'size' => '1.1 MB',
                'uploaded_by' => 'Rizal Pratama',
                'uploaded_date' => '2024-08-20',
                'period' => '2024',
                'version' => 'v1.0',
                'status' => 'Expiring Soon',
                'expires_at' => '2026-11-20',
                'description' => 'Sertifikasi kalibrasi tahunan alat angkat dan beban dinamis bengkel fabrikasi.',
                'can_upload_version' => true,
                'can_delete' => false,
                'locked' => false,
                'versions' => [
                    ['version' => 'v1.0', 'uploaded_by' => 'Rizal Pratama', 'uploaded_date' => '2024-08-20', 'note' => 'Certified calibration'],
                ],
            ],
            [
                'id' => 'DOC-2025-00375',
                'file_name' => 'Polis_Asuransi_Konstruksi_Tahap2.pdf',
                'module' => 'Vendor Management',
                'category' => 'Vendor Documents',
                'related_request' => 'VND-2025-00062',
                'related_title' => 'PT Asuransi Bangun Bersama',
                'unit' => 'Unit Samarinda',
                'file_type' => 'PDF',
                'size' => '2.1 MB',
                'uploaded_by' => 'Fajar Ramadhan',
                'uploaded_date' => '2025-06-14',
                'period' => '2025',
                'version' => 'v1.0',
                'status' => 'Expiring Soon',
                'expires_at' => '2026-12-31',
                'description' => 'Polis asuransi CAR (Contractor All Risk) untuk pekerjaan konstruksi tahap dua.',
                'can_upload_version' => true,
                'can_delete' => false,
                'locked' => false,
                'versions' => [
                    ['version' => 'v1.0', 'uploaded_by' => 'Fajar Ramadhan', 'uploaded_date' => '2025-06-14', 'note' => 'CAR Insurance policy'],
                ],
            ],

            // 4. Dokumen Operasional Aktif Berjalan (Tahun Berjalan 2026)
            [
                'id' => 'DOC-2026-00412',
                'file_name' => 'Weekly_Progress_W37.xlsx',
                'module' => 'Internal Construction',
                'category' => 'Construction',
                'related_request' => 'CTR-2026-00124',
                'related_title' => 'Workshop Terpadu — Weekly Progress',
                'unit' => 'Cabang Balikpapan',
                'file_type' => 'XLSX',
                'size' => '860 KB',
                'uploaded_by' => 'Rizal Pratama',
                'uploaded_date' => '2026-09-12',
                'period' => 'Sep 2026',
                'version' => 'v3.0',
                'status' => 'Active',
                'description' => 'Target mingguan, progres aktual, dan rekonsiliasi volume pekerjaan lapangan.',
                'can_upload_version' => true,
                'can_delete' => false,
                'locked' => true,
                'versions' => [
                    ['version' => 'v3.0', 'uploaded_by' => 'Rizal Pratama', 'uploaded_date' => '2026-09-12', 'note' => 'Week 37 approved data'],
                    ['version' => 'v2.0', 'uploaded_by' => 'Rizal Pratama', 'uploaded_date' => '2026-09-05', 'note' => 'Week 36 data'],
                ],
            ],
            [
                'id' => 'DOC-2026-00417',
                'file_name' => 'Legal_Opinion_Vendor_Agreement.pdf',
                'module' => 'Legal Operations',
                'category' => 'Legal',
                'related_request' => 'LGL-2026-00142',
                'related_title' => 'Vendor agreement legal review',
                'unit' => 'Head Office',
                'file_type' => 'PDF',
                'size' => '1.2 MB',
                'uploaded_by' => 'Nadia Puspita',
                'uploaded_date' => '2026-09-13',
                'period' => '2026',
                'version' => 'v1.0',
                'status' => 'Active',
                'description' => 'Legal opinion issued for the vendor framework agreement.',
                'can_upload_version' => true,
                'can_delete' => true,
                'locked' => false,
                'versions' => [
                    ['version' => 'v1.0', 'uploaded_by' => 'Nadia Puspita', 'uploaded_date' => '2026-09-13', 'note' => 'Initial issue'],
                ],
            ],
            [
                'id' => 'DOC-2026-00418',
                'file_name' => 'Contract_Conveyor_Line_4.pdf',
                'module' => 'Customer Projects',
                'category' => 'Contracts',
                'related_request' => 'CPR-2026-00106',
                'related_title' => 'Ekspansi Conveyor Line 4',
                'unit' => 'Project Morowali',
                'file_type' => 'PDF',
                'size' => '4.8 MB',
                'uploaded_by' => 'Bagas Setiawan',
                'uploaded_date' => '2026-09-14',
                'period' => '2026',
                'version' => 'v2.1',
                'status' => 'Active',
                'expires_at' => '2027-01-31',
                'description' => 'Signed customer contract and commercial appendices.',
                'can_upload_version' => true,
                'can_delete' => false,
                'locked' => true,
                'versions' => [
                    ['version' => 'v2.1', 'uploaded_by' => 'Bagas Setiawan', 'uploaded_date' => '2026-09-14', 'note' => 'Final signed copy'],
                    ['version' => 'v2.0', 'uploaded_by' => 'Andi Sultan', 'uploaded_date' => '2026-09-11', 'note' => 'Commercial revision'],
                ],
            ],
            [
                'id' => 'DOC-2026-00420',
                'file_name' => 'Berita_Acara_Inspeksi_K3_September.pdf',
                'module' => 'Internal Construction',
                'category' => 'Construction',
                'related_request' => 'CTR-2026-00139',
                'related_title' => 'Inspeksi K3 Berkala Bulanan September',
                'unit' => 'Cabang Balikpapan',
                'file_type' => 'PDF',
                'size' => '1.9 MB',
                'uploaded_by' => 'Rizal Pratama',
                'uploaded_date' => '2026-09-15',
                'period' => 'Sep 2026',
                'version' => 'v1.0',
                'status' => 'Active',
                'description' => 'Laporan hasil inspeksi kepatuhan K3 dan checklist keselamatan kerja alat pelindung diri.',
                'can_upload_version' => true,
                'can_delete' => true,
                'locked' => false,
                'versions' => [
                    ['version' => 'v1.0', 'uploaded_by' => 'Rizal Pratama', 'uploaded_date' => '2026-09-15', 'note' => 'Approved inspection report'],
                ],
            ],
            [
                'id' => 'DOC-2026-00425',
                'file_name' => 'Design_Drawing_Workshop_Civil.pdf',
                'module' => 'Customer Projects',
                'category' => 'Customer Projects',
                'related_request' => 'CPR-2026-00122',
                'related_title' => 'Gambar Teknis Perluasan Area Fabrikasi',
                'unit' => 'Cabang Balikpapan',
                'file_type' => 'PDF',
                'size' => '8.6 MB',
                'uploaded_by' => 'Virga Damayanti',
                'uploaded_date' => '2026-09-18',
                'period' => 'Sep 2026',
                'version' => 'v1.2',
                'status' => 'Active',
                'description' => 'Gambar teknis arsitektur dan potongan sipil perluasan workshop fabrikasi.',
                'can_upload_version' => true,
                'can_delete' => false,
                'locked' => true,
                'versions' => [
                    ['version' => 'v1.2', 'uploaded_by' => 'Virga Damayanti', 'uploaded_date' => '2026-09-18', 'note' => 'As-built drawing updates'],
                    ['version' => 'v1.0', 'uploaded_by' => 'Virga Damayanti', 'uploaded_date' => '2026-09-01', 'note' => 'Initial design'],
                ],
            ],
        ];

        foreach ($baseDocuments as $docData) {
            $versions = $docData['versions'] ?? [];
            unset($docData['versions']);

            $document = Document::updateOrCreate(['id' => $docData['id']], $docData);

            foreach ($versions as $vData) {
                DocumentVersion::updateOrCreate([
                    'document_id' => $document->id,
                    'version' => $vData['version'],
                ], [
                    'file_name' => $document->file_name,
                    'file_url' => $document->file_url,
                    'size' => $document->size,
                    'uploaded_by' => $vData['uploaded_by'] ?? $document->uploaded_by,
                    'uploaded_date' => $vData['uploaded_date'] ?? $document->uploaded_date,
                    'note' => $vData['note'] ?? 'Version archive',
                ]);
            }
        }

        // ==========================================
        // AUTO INGEST FROM EXISTING MODULES
        // ==========================================

        $docCounter = 500;

        // 1. Customer Projects: Attachments, Photos & Weekly Progresses
        $projects = CustomerProject::with(['attachments', 'photos', 'weeklyProgresses'])->get();
        foreach ($projects as $project) {
            // Attachments
            foreach ($project->attachments as $att) {
                $docId = 'DOC-CPR-' . str_pad($att->id, 5, '0', STR_PAD_LEFT);
                $ext = strtoupper(pathinfo($att->file_name, PATHINFO_EXTENSION) ?: 'PDF');
                $sizeMb = $att->file_size_kb ? round($att->file_size_kb / 1024, 1) . ' MB' : '1.5 MB';

                $doc = Document::updateOrCreate(['id' => $docId], [
                    'file_name' => $att->file_name,
                    'file_url' => $att->file_url,
                    'module' => 'Customer Projects',
                    'category' => 'Contracts',
                    'related_request' => $project->id,
                    'related_title' => $project->name,
                    'unit' => $project->unit,
                    'file_type' => in_array($ext, ['PDF', 'XLSX', 'DOCX', 'JPG', 'PNG']) ? $ext : 'PDF',
                    'size' => $sizeMb,
                    'uploaded_by' => $att->uploaded_by ?? $project->pic ?? 'Admin Project',
                    'uploaded_date' => $att->uploaded_at ? substr($att->uploaded_at, 0, 10) : now()->toDateString(),
                    'period' => $project->period ?? '2026',
                    'version' => 'v1.0',
                    'status' => 'Active',
                    'description' => "Lampiran kontrak/dokumen pendukung untuk proyek {$project->name}",
                    'can_upload_version' => true,
                    'can_delete' => true,
                    'locked' => false,
                ]);

                DocumentVersion::firstOrCreate([
                    'document_id' => $doc->id,
                    'version' => 'v1.0',
                ], [
                    'file_name' => $att->file_name,
                    'file_url' => $att->file_url,
                    'size' => $sizeMb,
                    'uploaded_by' => $doc->uploaded_by,
                    'uploaded_date' => $doc->uploaded_date,
                    'note' => 'Original project attachment',
                ]);
            }

            // Photos
            foreach ($project->photos as $photo) {
                $docId = 'DOC-CPRP-' . str_pad($photo->id, 5, '0', STR_PAD_LEFT);
                $fileName = 'Foto_Site_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', substr($photo->caption, 0, 30)) . '.jpg';

                $doc = Document::updateOrCreate(['id' => $docId], [
                    'file_name' => $fileName,
                    'file_url' => null,
                    'module' => 'Customer Projects',
                    'category' => 'Customer Projects',
                    'related_request' => $project->id,
                    'related_title' => $project->name,
                    'unit' => $project->unit,
                    'file_type' => 'JPG',
                    'size' => '2.4 MB',
                    'uploaded_by' => $project->pic ?? 'Site Inspector',
                    'uploaded_date' => $photo->date ?? now()->toDateString(),
                    'period' => $project->period ?? '2026',
                    'version' => 'v1.0',
                    'status' => 'Active',
                    'description' => "Dokumentasi visual site: {$photo->caption}",
                    'can_upload_version' => true,
                    'can_delete' => true,
                    'locked' => false,
                ]);

                DocumentVersion::firstOrCreate([
                    'document_id' => $doc->id,
                    'version' => 'v1.0',
                ], [
                    'file_name' => $fileName,
                    'size' => '2.4 MB',
                    'uploaded_by' => $doc->uploaded_by,
                    'uploaded_date' => $doc->uploaded_date,
                    'note' => 'Site progress photo',
                ]);
            }

            // Weekly Progresses (Site Reports)
            foreach ($project->weeklyProgresses as $wp) {
                $docId = 'DOC-CPRW-' . str_pad($wp->id, 5, '0', STR_PAD_LEFT);
                $cleanPeriod = preg_replace('/[^a-zA-Z0-9_-]/', '_', $wp->period);
                $fileName = "Site_Report_{$cleanPeriod}.pdf";

                $doc = Document::updateOrCreate(['id' => $docId], [
                    'file_name' => $fileName,
                    'file_url' => null,
                    'module' => 'Customer Projects',
                    'category' => 'Customer Projects',
                    'related_request' => $project->id,
                    'related_title' => $project->name,
                    'unit' => DocumentSyncService::normalizeUnit($project->unit),
                    'file_type' => 'PDF',
                    'size' => '3.1 MB',
                    'uploaded_by' => $wp->reported_by ?? $project->pic ?? 'Site Engineer',
                    'uploaded_date' => now()->toDateString(),
                    'period' => $wp->period ?? '2026',
                    'version' => 'v1.0',
                    'status' => 'Active',
                    'description' => "Laporan progress mingguan periode {$wp->period}. Target: {$wp->target}%, Aktual: {$wp->actual}%. Catatan: " . ($wp->note ?: '-'),
                    'can_upload_version' => true,
                    'can_delete' => false,
                    'locked' => false,
                ]);

                DocumentVersion::firstOrCreate([
                    'document_id' => $doc->id,
                    'version' => 'v1.0',
                ], [
                    'file_name' => $fileName,
                    'size' => '3.1 MB',
                    'uploaded_by' => $doc->uploaded_by,
                    'uploaded_date' => $doc->uploaded_date,
                    'note' => 'Weekly site report submission',
                ]);
            }
        }

        // 2. Construction Works: Attachments, Photos & Weekly Progresses
        $constructions = ConstructionWork::with(['attachments', 'photos', 'weeklyProgresses'])->get();
        foreach ($constructions as $cw) {
            // Attachments
            foreach ($cw->attachments as $att) {
                $docId = 'DOC-CW-' . str_pad($att->id, 5, '0', STR_PAD_LEFT);
                $ext = strtoupper(pathinfo($att->file_name, PATHINFO_EXTENSION) ?: 'PDF');
                $sizeMb = $att->file_size_kb ? round($att->file_size_kb / 1024, 1) . ' MB' : '2.1 MB';

                $doc = Document::updateOrCreate(['id' => $docId], [
                    'file_name' => $att->file_name,
                    'file_url' => $att->file_url,
                    'module' => 'Internal Construction',
                    'category' => 'Construction',
                    'related_request' => $cw->id,
                    'related_title' => $cw->title,
                    'unit' => DocumentSyncService::normalizeUnit($cw->unit),
                    'file_type' => in_array($ext, ['PDF', 'XLSX', 'DOCX', 'JPG', 'PNG']) ? $ext : 'PDF',
                    'size' => $sizeMb,
                    'uploaded_by' => $att->uploaded_by ?? $cw->pic ?? 'Project Engineer',
                    'uploaded_date' => $att->uploaded_at ? substr($att->uploaded_at, 0, 10) : now()->toDateString(),
                    'period' => $cw->period ?? '2026',
                    'version' => 'v1.0',
                    'status' => 'Active',
                    'description' => "Berkas teknis / BoQ / S-Curve untuk {$cw->title}",
                    'can_upload_version' => true,
                    'can_delete' => true,
                    'locked' => false,
                ]);

                DocumentVersion::firstOrCreate([
                    'document_id' => $doc->id,
                    'version' => 'v1.0',
                ], [
                    'file_name' => $att->file_name,
                    'file_url' => $att->file_url,
                    'size' => $sizeMb,
                    'uploaded_by' => $doc->uploaded_by,
                    'uploaded_date' => $doc->uploaded_date,
                    'note' => 'Original construction attachment',
                ]);
            }

            // Photos
            foreach ($cw->photos as $photo) {
                $docId = 'DOC-CWP-' . str_pad($photo->id, 5, '0', STR_PAD_LEFT);
                $fileName = 'Foto_Konstruksi_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', substr($photo->caption, 0, 30)) . '.jpg';

                $doc = Document::updateOrCreate(['id' => $docId], [
                    'file_name' => $fileName,
                    'file_url' => null,
                    'module' => 'Internal Construction',
                    'category' => 'Construction',
                    'related_request' => $cw->id,
                    'related_title' => $cw->title,
                    'unit' => DocumentSyncService::normalizeUnit($cw->unit),
                    'file_type' => 'JPG',
                    'size' => '2.8 MB',
                    'uploaded_by' => $cw->pic ?? 'Site Supervisor',
                    'uploaded_date' => $photo->date ?? now()->toDateString(),
                    'period' => $cw->period ?? '2026',
                    'version' => 'v1.0',
                    'status' => 'Active',
                    'description' => "Dokumentasi visual pekerjaan konstruksi: {$photo->caption}",
                    'can_upload_version' => true,
                    'can_delete' => true,
                    'locked' => false,
                ]);

                DocumentVersion::firstOrCreate([
                    'document_id' => $doc->id,
                    'version' => 'v1.0',
                ], [
                    'file_name' => $fileName,
                    'size' => '2.8 MB',
                    'uploaded_by' => $doc->uploaded_by,
                    'uploaded_date' => $doc->uploaded_date,
                    'note' => 'Construction photo log',
                ]);
            }

            // Weekly Progress
            foreach ($cw->weeklyProgresses as $wp) {
                $docId = 'DOC-CWW-' . str_pad($wp->id, 5, '0', STR_PAD_LEFT);
                $cleanPeriod = preg_replace('/[^a-zA-Z0-9_-]/', '_', $wp->period);
                $fileName = "Weekly_Progress_{$cleanPeriod}.xlsx";

                $doc = Document::updateOrCreate(['id' => $docId], [
                    'file_name' => $fileName,
                    'file_url' => null,
                    'module' => 'Internal Construction',
                    'category' => 'Construction',
                    'related_request' => $cw->id,
                    'related_title' => $cw->title,
                    'unit' => DocumentSyncService::normalizeUnit($cw->unit),
                    'file_type' => 'XLSX',
                    'size' => '950 KB',
                    'uploaded_by' => $wp->reported_by ?? $cw->pic ?? 'Project Engineer',
                    'uploaded_date' => now()->toDateString(),
                    'period' => $wp->period ?? '2026',
                    'version' => 'v1.0',
                    'status' => 'Active',
                    'description' => "Laporan progres konstruksi mingguan {$wp->period}. Target: {$wp->target}%, Aktual: {$wp->actual}%.",
                    'can_upload_version' => true,
                    'can_delete' => false,
                    'locked' => false,
                ]);

                DocumentVersion::firstOrCreate([
                    'document_id' => $doc->id,
                    'version' => 'v1.0',
                ], [
                    'file_name' => $fileName,
                    'size' => '950 KB',
                    'uploaded_by' => $doc->uploaded_by,
                    'uploaded_date' => $doc->uploaded_date,
                    'note' => 'Weekly progress entry',
                ]);
            }
        }

        // 3. Legal Requests: Attachments & Output Documents
        $legals = LegalRequest::with(['attachments', 'outputDocuments'])->get();
        foreach ($legals as $lgl) {
            foreach ($lgl->attachments as $att) {
                $docId = 'DOC-LGL-' . str_pad($att->id, 5, '0', STR_PAD_LEFT);
                $ext = strtoupper(pathinfo($att->file_name, PATHINFO_EXTENSION) ?: 'PDF');

                $doc = Document::updateOrCreate(['id' => $docId], [
                    'file_name' => $att->file_name,
                    'file_url' => $att->file_url,
                    'module' => 'Legal Operations',
                    'category' => 'Legal',
                    'related_request' => $lgl->request_code ?: $lgl->id,
                    'related_title' => $lgl->title,
                    'unit' => DocumentSyncService::normalizeUnit($lgl->unit),
                    'file_type' => in_array($ext, ['PDF', 'XLSX', 'DOCX', 'JPG', 'PNG']) ? $ext : 'PDF',
                    'size' => '1.8 MB',
                    'uploaded_by' => $lgl->pic ?? 'Legal Officer',
                    'uploaded_date' => $lgl->submitted_at ? substr($lgl->submitted_at, 0, 10) : now()->toDateString(),
                    'period' => '2026',
                    'version' => 'v1.0',
                    'status' => 'Active',
                    'description' => "Berkas permohonan legal review: {$lgl->title}",
                    'can_upload_version' => true,
                    'can_delete' => true,
                    'locked' => false,
                ]);

                DocumentVersion::firstOrCreate([
                    'document_id' => $doc->id,
                    'version' => 'v1.0',
                ], [
                    'file_name' => $att->file_name,
                    'file_url' => $att->file_url,
                    'size' => '1.8 MB',
                    'uploaded_by' => $doc->uploaded_by,
                    'uploaded_date' => $doc->uploaded_date,
                    'note' => 'Initial legal document attachment',
                ]);
            }

            foreach ($lgl->outputDocuments as $out) {
                $docId = 'DOC-LGLO-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $out->id);
                $cleanTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $out->title ?: $out->doc_number ?: 'Legal_Doc');
                $fileName = $cleanTitle . '.pdf';

                $doc = Document::updateOrCreate(['id' => $docId], [
                    'file_name' => $fileName,
                    'file_url' => null,
                    'module' => 'Legal Operations',
                    'category' => 'Legal',
                    'related_request' => $lgl->request_code ?: $lgl->id,
                    'related_title' => $lgl->title,
                    'unit' => DocumentSyncService::normalizeUnit($lgl->unit),
                    'file_type' => 'PDF',
                    'size' => '1.4 MB',
                    'uploaded_by' => 'Legal Dept',
                    'uploaded_date' => $out->issued_at ? substr($out->issued_at, 0, 10) : now()->toDateString(),
                    'period' => '2026',
                    'version' => 'v1.0',
                    'status' => 'Active',
                    'description' => "Official legal output: {$out->title} (No: {$out->doc_number})",
                    'can_upload_version' => true,
                    'can_delete' => false,
                    'locked' => true,
                ]);

                DocumentVersion::firstOrCreate([
                    'document_id' => $doc->id,
                    'version' => 'v1.0',
                ], [
                    'file_name' => $fileName,
                    'size' => '1.4 MB',
                    'uploaded_by' => $doc->uploaded_by,
                    'uploaded_date' => $doc->uploaded_date,
                    'note' => 'Official issued legal output',
                ]);
            }
        }

        // 4. Ensure EVERY seeded document has a valid, playable & downloadable file_url
        $sampleJpgs = [
            '/storage/attachments/8AhWzcMAPjKIyIWnw2X8vYRYwjUy9F0hIsPEdBst.jpg',
            '/storage/attachments/ADTNSoen02PydizRmCdaCQbcbyy72MtY6F4KSm1S.jpg',
            '/storage/attachments/kYkwt4GgH7NTxYQ9zG1jD6PH2nE2MmHxxA9N4bat.jpg',
            '/storage/attachments/hdNkvEmYG8K1EnO928xOPz6p8G8cusig9C1qItiy.jpg',
            '/storage/attachments/yOB08y8Iwfq9Xej5n8rQhDVr1Mm55ORG1yvtiGnH.jpg',
        ];
        $samplePdfs = [
            '/storage/attachments/UBIaAkrbonkArne1AeX1g3rDAwlbDBQddeY3YaxM.pdf',
            '/storage/attachments/3jIR7WnQiwFVh7Do2y4BklyQj768bZskVlg7ucbJ.pdf',
            '/storage/attachments/Z3BCYVZTHBCDj5vShkjL3QuV1HpRBdCXBQly5cbr.pdf',
            '/storage/attachments/25NSwiYbLNSFv0n0hlcxE8UnTFZ6vYcbBS5EGMf1.pdf',
            '/storage/attachments/GaNHobN39vYCcUCja95pWC6TPFJWnBLdHp858WN5.pdf',
        ];

        $allDocs = Document::all();
        $idx = 0;
        foreach ($allDocs as $d) {
            if (!$d->file_url) {
                if (in_array(strtoupper($d->file_type), ['JPG', 'PNG'])) {
                    $d->file_url = $sampleJpgs[$idx % count($sampleJpgs)];
                } else {
                    $d->file_url = $samplePdfs[$idx % count($samplePdfs)];
                }
                $d->save();
                $d->versions()->whereNull('file_url')->update(['file_url' => $d->file_url]);
                $idx++;
            }
        }
    }
}
