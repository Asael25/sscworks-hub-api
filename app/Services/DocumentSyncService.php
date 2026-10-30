<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\LegalRequest;
use App\Models\LegalAttachment;
use App\Models\LegalOutputDocument;
use App\Models\CustomerProject;
use App\Models\CustomerProjectAttachment;
use App\Models\ConstructionWork;
use App\Models\ConstructionAttachment;

class DocumentSyncService
{
    /**
     * Standardize / Normalize unit names across all modules
     */
    public static function normalizeUnit(?string $unit): string
    {
        if (!$unit) {
            return 'All Units';
        }

        $trimmed = trim($unit);

        $unitMap = [
            'Unit Samarinda' => 'SSC Samarinda',
            'Cabang Samarinda' => 'SSC Samarinda',
            'SSC Works - Samarinda' => 'SSC Samarinda',
            'SSC Works - Samarinda' => 'SSC Samarinda',
            
            'Cabang Balikpapan' => 'SSC Balikpapan',
            'Unit Balikpapan' => 'SSC Balikpapan',
            'SSC Works - Balikpapan' => 'SSC Balikpapan',
            'SSC Works - Balikpapan' => 'SSC Balikpapan',

            'Cabang Surabaya' => 'SSC Surabaya',
            'Unit Surabaya' => 'SSC Surabaya',
            'SSC Works - Surabaya' => 'SSC Surabaya',
            'SSC Works - Surabaya' => 'SSC Surabaya',

            'Cabang Jakarta' => 'SSC Jakarta',
            'Unit Jakarta' => 'SSC Jakarta',
            'SSC Works - Jakarta' => 'SSC Jakarta',
            'SSC Works - Jakarta' => 'SSC Jakarta',

            'Cabang Palu' => 'SSC Palu',
            'Unit Palu' => 'SSC Palu',
            'SSC Works - Palu' => 'SSC Palu',
            'SSC Works - Palu' => 'SSC Palu',

            'SSC Works - Head Office' => 'Head Office',
            'SSC Works - Head Office' => 'Head Office',
            'SSC Head Office' => 'Head Office',
        ];

        return $unitMap[$trimmed] ?? $trimmed;
    }

    /**
     * Auto-sync Legal Attachment to Document Center
     */
    public static function syncLegalAttachment(LegalRequest $request, LegalAttachment $attachment): Document
    {
        $docId = 'DOC-LGL-' . str_pad($attachment->id, 5, '0', STR_PAD_LEFT);
        $ext = strtoupper(pathinfo($attachment->file_name, PATHINFO_EXTENSION) ?: 'PDF');
        $unit = self::normalizeUnit($request->unit);

        $size = ($attachment->file_size_kb && $attachment->file_size_kb > 0)
            ? ($attachment->file_size_kb < 1000 ? $attachment->file_size_kb . ' KB' : round($attachment->file_size_kb / 1024, 1) . ' MB')
            : '1.5 MB';

        $doc = Document::updateOrCreate(['id' => $docId], [
            'file_name' => $attachment->file_name,
            'file_url' => $attachment->file_url,
            'module' => 'Legal Operations',
            'category' => 'Legal',
            'related_request' => $request->request_code ?: $request->id,
            'related_title' => $request->title,
            'unit' => $unit,
            'file_type' => in_array($ext, ['PDF', 'XLSX', 'DOCX', 'JPG', 'PNG', 'ZIP']) ? $ext : 'PDF',
            'size' => $size,
            'uploaded_by' => $attachment->uploaded_by ?: $request->applicant ?: 'Legal Officer',
            'uploaded_date' => $attachment->uploaded_at ? substr($attachment->uploaded_at, 0, 10) : now()->toDateString(),
            'period' => date('Y'),
            'version' => 'v1.0',
            'status' => 'Active',
            'description' => "Berkas permohonan legal review: {$request->title}",
            'can_upload_version' => true,
            'can_delete' => true,
            'locked' => false,
        ]);

        DocumentVersion::firstOrCreate([
            'document_id' => $doc->id,
            'version' => 'v1.0',
        ], [
            'file_name' => $attachment->file_name,
            'file_url' => $attachment->file_url,
            'size' => $size,
            'uploaded_by' => $doc->uploaded_by,
            'uploaded_date' => $doc->uploaded_date,
            'note' => 'Initial legal document attachment',
        ]);

        return $doc;
    }

    /**
     * Auto-sync Legal Output Document to Document Center
     */
    public static function syncLegalOutput(LegalRequest $request, LegalOutputDocument $output): Document
    {
        $docId = 'DOC-LGLO-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $output->id);
        $cleanTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $output->title ?: $output->doc_number ?: 'Legal_Doc');
        $fileName = $cleanTitle . '.pdf';
        $unit = self::normalizeUnit($request->unit);

        $doc = Document::updateOrCreate(['id' => $docId], [
            'file_name' => $fileName,
            'file_url' => null,
            'module' => 'Legal Operations',
            'category' => 'Legal',
            'related_request' => $request->request_code ?: $request->id,
            'related_title' => $request->title,
            'unit' => $unit,
            'file_type' => 'PDF',
            'size' => '1.4 MB',
            'uploaded_by' => 'Legal Dept',
            'uploaded_date' => $output->issued_at ? substr($output->issued_at, 0, 10) : now()->toDateString(),
            'period' => date('Y'),
            'version' => 'v1.0',
            'status' => 'Active',
            'description' => "Official legal output: {$output->title} (No: {$output->doc_number})",
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

        return $doc;
    }

    /**
     * Auto-sync Customer Project Attachment to Document Center
     */
    public static function syncCustomerProjectAttachment(CustomerProject $project, CustomerProjectAttachment $attachment): Document
    {
        $docId = 'DOC-CPA-' . str_pad($attachment->id, 5, '0', STR_PAD_LEFT);
        $ext = strtoupper(pathinfo($attachment->file_name, PATHINFO_EXTENSION) ?: 'PDF');
        $unit = self::normalizeUnit($project->unit);

        $size = ($attachment->file_size_kb && $attachment->file_size_kb > 0)
            ? ($attachment->file_size_kb < 1000 ? $attachment->file_size_kb . ' KB' : round($attachment->file_size_kb / 1024, 1) . ' MB')
            : '2.0 MB';

        $doc = Document::updateOrCreate(['id' => $docId], [
            'file_name' => $attachment->file_name,
            'file_url' => $attachment->file_url,
            'module' => 'Customer Projects',
            'category' => 'Customer Projects',
            'related_request' => $project->id,
            'related_title' => $project->name,
            'unit' => $unit,
            'file_type' => in_array($ext, ['PDF', 'XLSX', 'DOCX', 'JPG', 'PNG', 'ZIP']) ? $ext : 'PDF',
            'size' => $size,
            'uploaded_by' => $attachment->uploaded_by ?: $project->pic ?: 'Project Manager',
            'uploaded_date' => $attachment->uploaded_at ? substr($attachment->uploaded_at, 0, 10) : now()->toDateString(),
            'period' => $project->period ?? date('Y'),
            'version' => 'v1.0',
            'status' => 'Active',
            'description' => "Berkas proyek pelanggan: {$project->name} ({$project->customer})",
            'can_upload_version' => true,
            'can_delete' => true,
            'locked' => false,
        ]);

        DocumentVersion::firstOrCreate([
            'document_id' => $doc->id,
            'version' => 'v1.0',
        ], [
            'file_name' => $attachment->file_name,
            'file_url' => $attachment->file_url,
            'size' => $size,
            'uploaded_by' => $doc->uploaded_by,
            'uploaded_date' => $doc->uploaded_date,
            'note' => 'Customer project attachment',
        ]);

        return $doc;
    }

    /**
     * Auto-sync Construction Attachment to Document Center
     */
    public static function syncConstructionAttachment(ConstructionWork $work, ConstructionAttachment $attachment): Document
    {
        $docId = 'DOC-CWA-' . str_pad($attachment->id, 5, '0', STR_PAD_LEFT);
        $ext = strtoupper(pathinfo($attachment->file_name, PATHINFO_EXTENSION) ?: 'PDF');
        $unit = self::normalizeUnit($work->unit);

        $size = ($attachment->file_size_kb && $attachment->file_size_kb > 0)
            ? ($attachment->file_size_kb < 1000 ? $attachment->file_size_kb . ' KB' : round($attachment->file_size_kb / 1024, 1) . ' MB')
            : '2.5 MB';

        $relatedCode = $work->contract_number ?: $work->id;

        $doc = Document::updateOrCreate(['id' => $docId], [
            'file_name' => $attachment->file_name,
            'file_url' => $attachment->file_url,
            'module' => 'Internal Construction',
            'category' => 'Construction',
            'related_request' => $relatedCode,
            'related_title' => $work->name,
            'unit' => $unit,
            'file_type' => in_array($ext, ['PDF', 'XLSX', 'DOCX', 'JPG', 'PNG', 'ZIP']) ? $ext : 'PDF',
            'size' => $size,
            'uploaded_by' => $attachment->uploaded_by ?: $work->pic ?: 'Site Supervisor',
            'uploaded_date' => $attachment->uploaded_at ? substr($attachment->uploaded_at, 0, 10) : now()->toDateString(),
            'period' => $work->period ?? date('Y'),
            'version' => 'v1.0',
            'status' => 'Active',
            'description' => "Berkas pekerjaan konstruksi internal: {$work->name}",
            'can_upload_version' => true,
            'can_delete' => true,
            'locked' => false,
        ]);

        DocumentVersion::firstOrCreate([
            'document_id' => $doc->id,
            'version' => 'v1.0',
        ], [
            'file_name' => $attachment->file_name,
            'file_url' => $attachment->file_url,
            'size' => $size,
            'uploaded_by' => $doc->uploaded_by,
            'uploaded_date' => $doc->uploaded_date,
            'note' => 'Construction work attachment',
        ]);

        return $doc;
    }

    /**
     * Auto-sync Weekly Progress Attachment to Document Center
     */
    public static function syncWeeklyProgressAttachment(?ConstructionWork $work, string $reportId, string $period, array $att, ?string $pic): ?Document
    {
        if (!$work) {
            return null;
        }

        $seed = $reportId . ($att['name'] ?? 'file');
        $docId = 'DOC-PRG-' . substr(md5($seed), 0, 8);
        $fileName = $att['name'] ?? 'Laporan_Progres.pdf';
        $ext = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION) ?: 'PDF');
        $unit = self::normalizeUnit($work->unit);

        $size = isset($att['sizeKb'])
            ? ($att['sizeKb'] < 1000 ? $att['sizeKb'] . ' KB' : round($att['sizeKb'] / 1024, 1) . ' MB')
            : '1.5 MB';

        $relatedCode = $work->contract_number ?: $work->id;

        $doc = Document::updateOrCreate(['id' => $docId], [
            'file_name' => $fileName,
            'file_url' => $att['url'] ?? null,
            'module' => 'Internal Construction',
            'category' => 'Construction',
            'related_request' => $relatedCode,
            'related_title' => "Laporan Progres {$period} - {$work->name}",
            'unit' => $unit,
            'file_type' => in_array($ext, ['PDF', 'XLSX', 'DOCX', 'JPG', 'PNG', 'ZIP']) ? $ext : 'PDF',
            'size' => $size,
            'uploaded_by' => $pic ?: $work->pic ?: 'Project Engineer',
            'uploaded_date' => now()->toDateString(),
            'period' => $period ?: date('Y'),
            'version' => 'v1.0',
            'status' => 'Active',
            'description' => "Dokumentasi / lampiran laporan progres mingguan {$period} untuk pekerjaan {$work->name}",
            'can_upload_version' => true,
            'can_delete' => true,
            'locked' => false,
        ]);

        DocumentVersion::firstOrCreate([
            'document_id' => $doc->id,
            'version' => 'v1.0',
        ], [
            'file_name' => $fileName,
            'file_url' => $att['url'] ?? null,
            'size' => $size,
            'uploaded_by' => $doc->uploaded_by,
            'uploaded_date' => $doc->uploaded_date,
            'note' => 'Weekly progress attachment',
        ]);

        return $doc;
    }

    /**
     * Fix & Normalize all existing documents in database
     */
    public static function fixAllExistingDocuments(): array
    {
        $updatedCodes = 0;
        $normalizedUnits = 0;

        // 1. Fix Legal Request codes
        $legalDocs = Document::where('module', 'Legal Operations')->get();
        foreach ($legalDocs as $doc) {
            $req = null;
            if (is_numeric($doc->related_request)) {
                $req = LegalRequest::find((int) $doc->related_request);
            } elseif ($doc->related_title) {
                $req = LegalRequest::where('title', $doc->related_title)->first();
            }

            if ($req && !empty($req->request_code) && $doc->related_request !== $req->request_code) {
                $doc->related_request = $req->request_code;
                $doc->save();
                $updatedCodes++;
            }
        }

        // 2. Normalize all units
        $allDocs = Document::all();
        foreach ($allDocs as $doc) {
            $norm = self::normalizeUnit($doc->unit);
            if ($norm !== $doc->unit) {
                $doc->unit = $norm;
                $doc->save();
                $normalizedUnits++;
            }
        }

        return [
            'updatedCodes' => $updatedCodes,
            'normalizedUnits' => $normalizedUnits,
            'totalDocuments' => $allDocs->count(),
        ];
    }
}
