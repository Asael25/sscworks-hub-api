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
                'description' => 'Weekly target, actual progress, and scope reconciliation.',
                'can_upload_version' => true,
                'can_delete' => false,
                'locked' => true,
                'versions' => [
                    ['version' => 'v3.0', 'uploaded_by' => 'Rizal Pratama', 'uploaded_date' => '2026-09-12', 'note' => 'Week 37 approved data'],
                    ['version' => 'v2.0', 'uploaded_by' => 'Rizal Pratama', 'uploaded_date' => '2026-09-05', 'note' => 'Week 36 data'],
                ],
            ],
            [
                'id' => 'DOC-2026-00406',
                'file_name' => 'Site_Photo_Report_W36.pdf',
                'module' => 'Customer Projects',
                'category' => 'Customer Projects',
                'related_request' => 'CSR-2026-00001',
                'related_title' => 'Pembangunan Fasilitas Workshop Terpadu',
                'unit' => 'Cabang Balikpapan',
                'file_type' => 'PDF',
                'size' => '7.4 MB',
                'uploaded_by' => 'Rizal Pratama',
                'uploaded_date' => '2026-09-10',
                'period' => 'Sep 2026',
                'version' => 'v1.0',
                'status' => 'Draft',
                'description' => 'Compiled site photos and weekly activity notes.',
                'can_upload_version' => true,
                'can_delete' => true,
                'locked' => false,
                'versions' => [
                    ['version' => 'v1.0', 'uploaded_by' => 'Rizal Pratama', 'uploaded_date' => '2026-09-10', 'note' => 'Draft report'],
                ],
            ],
            [
                'id' => 'DOC-2026-00399',
                'file_name' => 'Cashflow_Recap_August_2026.xlsx',
                'module' => 'Cashflow',
                'category' => 'Cashflow',
                'related_request' => 'CFL-2026-00881',
                'related_title' => 'August cashflow closing',
                'unit' => 'Head Office',
                'file_type' => 'XLSX',
                'size' => '2.1 MB',
                'uploaded_by' => 'Dewi Anggraini',
                'uploaded_date' => '2026-09-08',
                'period' => 'Aug 2026',
                'version' => 'v1.2',
                'status' => 'Active',
                'description' => 'Verified cash-in, cash-out, balance, and attachment recap.',
                'can_upload_version' => true,
                'can_delete' => false,
                'locked' => false,
                'versions' => [
                    ['version' => 'v1.2', 'uploaded_by' => 'Dewi Anggraini', 'uploaded_date' => '2026-09-08', 'note' => 'Closing adjustment'],
                ],
            ],
            [
                'id' => 'DOC-2026-00391',
                'file_name' => 'Budget_Plan_Operations_2027.xlsx',
                'module' => 'Budgeting',
                'category' => 'Budgeting',
                'related_request' => 'BGT-2026-00217',
                'related_title' => 'Operational Budget Plan 2027',
                'unit' => 'Head Office',
                'file_type' => 'XLSX',
                'size' => '3.6 MB',
                'uploaded_by' => 'Andi Sultan',
                'uploaded_date' => '2026-09-05',
                'period' => '2027',
                'version' => 'v4.0',
                'status' => 'Active',
                'description' => 'Approved operational budget plan and cost-center breakdown.',
                'can_upload_version' => true,
                'can_delete' => false,
                'locked' => false,
                'versions' => [
                    ['version' => 'v4.0', 'uploaded_by' => 'Andi Sultan', 'uploaded_date' => '2026-09-05', 'note' => 'GM approved version'],
                ],
            ],
            [
                'id' => 'DOC-2026-00384',
                'file_name' => 'Vendor_Insurance_Certificate.pdf',
                'module' => 'Vendor Management',
                'category' => 'Vendor Documents',
                'related_request' => 'VND-2026-00088',
                'related_title' => 'PT Karya Teknik Utama',
                'unit' => 'Unit Samarinda',
                'file_type' => 'PDF',
                'size' => '980 KB',
                'uploaded_by' => 'Fajar Ramadhan',
                'uploaded_date' => '2026-09-02',
                'period' => '2026',
                'version' => 'v1.0',
                'status' => 'Expiring Soon',
                'expires_at' => '2026-10-05',
                'description' => 'Vendor public liability and project insurance certificate.',
                'can_upload_version' => true,
                'can_delete' => false,
                'locked' => false,
                'versions' => [
                    ['version' => 'v1.0', 'uploaded_by' => 'Fajar Ramadhan', 'uploaded_date' => '2026-09-02', 'note' => 'Valid certificate'],
                ],
            ],
            [
                'id' => 'DOC-2026-00372',
                'file_name' => 'SOP_Document_Control.docx',
                'module' => 'General',
                'category' => 'General Documents',
                'related_request' => 'POL-2026-00019',
                'related_title' => 'Document Control Procedure',
                'unit' => 'All Units',
                'file_type' => 'DOCX',
                'size' => '640 KB',
                'uploaded_by' => 'Virga Damayanti',
                'uploaded_date' => '2026-08-28',
                'period' => '2026',
                'version' => 'v2.3',
                'status' => 'Active',
                'description' => 'SSC Works Hub document naming, approval, and retention procedure.',
                'can_upload_version' => true,
                'can_delete' => true,
                'locked' => false,
                'versions' => [
                    ['version' => 'v2.3', 'uploaded_by' => 'Virga Damayanti', 'uploaded_date' => '2026-08-28', 'note' => 'Policy update'],
                ],
            ],
            [
                'id' => 'DOC-2026-00351',
                'file_name' => 'Vendor_Safety_Certification.pdf',
                'module' => 'Internal Construction',
                'category' => 'Vendor Documents',
                'related_request' => 'VND-2026-00071',
                'related_title' => 'PT Mitra Konstruksi Mandiri',
                'unit' => 'Cabang Surabaya',
                'file_type' => 'PDF',
                'size' => '1.7 MB',
                'uploaded_by' => 'Dimas Ardiansyah',
                'uploaded_date' => '2026-08-20',
                'period' => '2026',
                'version' => 'v1.0',
                'status' => 'Expiring Soon',
                'expires_at' => '2026-09-28',
                'description' => 'Contractor safety certification and competency records.',
                'can_upload_version' => true,
                'can_delete' => false,
                'locked' => false,
                'versions' => [
                    ['version' => 'v1.0', 'uploaded_by' => 'Dimas Ardiansyah', 'uploaded_date' => '2026-08-20', 'note' => 'Issued certificate'],
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
