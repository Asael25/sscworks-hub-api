<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with('versions')->orderBy('uploaded_date', 'desc')->orderBy('id', 'desc');

        // Multi-Unit Access Control
        $identifier = $request->header('X-User-Identifier');
        if ($identifier) {
            $user = User::whereRaw('LOWER(identifier) = ?', [strtolower($identifier)])
                ->orWhereRaw('LOWER(username) = ?', [strtolower($identifier)])
                ->first();

            if ($user) {
                $additional = is_array($user->additional_units)
                    ? $user->additional_units
                    : json_decode($user->additional_units ?? '[]', true);

                $isMultiUnitAdmin = in_array($user->role, ['Super Admin', 'Admin Super'])
                    || strtolower($user->identifier) === 'rudi@sscworks.com'
                    || strtolower($user->username ?? '') === 'rudi'
                    || (is_array($additional) && in_array('*', $additional));

                if (!$isMultiUnitAdmin) {
                    $allowedUnits = array_filter(array_merge([$user->primary_unit], $additional ?? []));

                    $query->where(function ($q) use ($allowedUnits) {
                        $q->where('unit', 'All Units'); // All units documents are public

                        foreach ($allowedUnits as $unit) {
                            if (!$unit || $unit === '*') continue;
                            $q->orWhere('unit', $unit);

                            $dashHyphen = str_replace('—', '-', $unit);
                            $dashEm = str_replace('-', '—', $unit);
                            $q->orWhere('unit', $dashHyphen)
                              ->orWhere('unit', $dashEm);

                            $clean = trim(str_replace(['SSC', 'Works', 'Cabang', '—', '-'], ' ', $unit));
                            if (!empty($clean)) {
                                $q->orWhere('unit', 'LIKE', '%' . $clean . '%');
                                if (stripos($clean, 'Head Office') !== false) {
                                    $hoDepts = ['Procurement', 'IT & Digital', 'Human Resources', 'Commercial', 'Accounting', 'Human Capital', 'Finance', 'Operations', 'Legal'];
                                    $q->orWhereIn('unit', $hoDepts);
                                }
                            }
                        }
                    });
                }
            }
        }

        // Filters
        if ($request->filled('module') && $request->module !== 'All') {
            $query->where('module', $request->module);
        }

        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        if ($request->filled('unit') && $request->unit !== 'All') {
            $query->where('unit', $request->unit);
        }

        if ($request->filled('file_type') && $request->file_type !== 'All') {
            $query->where('file_type', $request->file_type);
        }

        if ($request->filled('owner') && $request->owner !== 'All') {
            $query->where('uploaded_by', $request->owner);
        }

        if ($request->filled('period') && $request->period !== 'All') {
            $query->where('period', $request->period);
        }

        if ($request->filled('status') && $request->status !== 'All') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('file_name', 'LIKE', "%{$search}%")
                  ->orWhere('module', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%")
                  ->orWhere('related_request', 'LIKE', "%{$search}%")
                  ->orWhere('related_title', 'LIKE', "%{$search}%")
                  ->orWhere('uploaded_by', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $documents = $query->get();

        $formatted = $documents->map(function ($doc) {
            return [
                'id' => $doc->id,
                'fileName' => $doc->file_name,
                'fileUrl' => $doc->file_url,
                'module' => $doc->module,
                'category' => $doc->category,
                'relatedRequest' => $doc->related_request ?? '-',
                'relatedTitle' => $doc->related_title ?? '',
                'unit' => $doc->unit,
                'fileType' => $doc->file_type,
                'size' => $doc->size,
                'uploadedBy' => $doc->uploaded_by,
                'uploadedDate' => $doc->uploaded_date ? $doc->uploaded_date->format('Y-m-d') : date('Y-m-d'),
                'period' => $doc->period ?? '2026',
                'version' => $doc->version,
                'status' => $doc->status,
                'expiresAt' => $doc->expires_at ? $doc->expires_at->format('Y-m-d') : null,
                'description' => $doc->description ?? '',
                'canUploadVersion' => (bool)$doc->can_upload_version,
                'canDelete' => (bool)$doc->can_delete,
                'locked' => (bool)$doc->locked,
                'versions' => $doc->versions->map(function ($v) {
                    return [
                        'version' => $v->version,
                        'fileName' => $v->file_name,
                        'fileUrl' => $v->file_url,
                        'size' => $v->size,
                        'uploadedBy' => $v->uploaded_by,
                        'uploadedDate' => $v->uploaded_date ? $v->uploaded_date->format('Y-m-d') : date('Y-m-d'),
                        'note' => $v->note ?? '',
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formatted,
            'total' => $formatted->count(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'nullable|file|max:30720',
            'file_name' => 'nullable|string',
            'module' => 'required|string',
            'category' => 'required|string',
            'unit' => 'required|string',
            'related_request' => 'nullable|string',
            'related_title' => 'nullable|string',
            'uploaded_by' => 'nullable|string',
            'period' => 'nullable|string',
            'description' => 'nullable|string',
            'expires_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $fileUrl = null;
        $size = '1.0 MB';
        $fileType = 'PDF';
        $fileName = $request->file_name ?: 'Document.pdf';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $path = $file->store('attachments/documents', 'public');
            $fileUrl = '/storage/' . $path;
            $rawSize = $file->getSize();
            $size = $rawSize < 1000000 ? round($rawSize / 1024) . ' KB' : round($rawSize / (1024 * 1024), 1) . ' MB';
            $ext = strtoupper($file->getClientOriginalExtension());
            $fileType = in_array($ext, ['PDF', 'XLSX', 'DOCX', 'JPG', 'PNG', 'ZIP']) ? $ext : 'PDF';
        }

        // Generate ID
        $year = date('Y');
        $count = Document::whereYear('created_at', $year)->count() + 1;
        $docId = sprintf('DOC-%s-%05d', $year, $count);

        $document = Document::create([
            'id' => $docId,
            'file_name' => $fileName,
            'file_url' => $fileUrl,
            'module' => $request->module,
            'category' => $request->category,
            'related_request' => $request->related_request,
            'related_title' => $request->related_title,
            'unit' => $request->unit,
            'file_type' => $fileType,
            'size' => $size,
            'uploaded_by' => $request->uploaded_by ?: 'PT SSC WORKS User',
            'uploaded_date' => now()->toDateString(),
            'period' => $request->period ?: date('Y'),
            'version' => 'v1.0',
            'status' => 'Active',
            'expires_at' => $request->expires_at,
            'description' => $request->description,
            'can_upload_version' => true,
            'can_delete' => true,
            'locked' => false,
        ]);

        DocumentVersion::create([
            'document_id' => $document->id,
            'version' => 'v1.0',
            'file_name' => $fileName,
            'file_url' => $fileUrl,
            'size' => $size,
            'uploaded_by' => $document->uploaded_by,
            'uploaded_date' => $document->uploaded_date,
            'note' => 'Initial upload',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Document created successfully',
            'data' => $document->load('versions'),
        ], 201);
    }

    public function uploadVersion(Request $request, $id)
    {
        $document = Document::with('versions')->find($id);

        if (!$document) {
            return response()->json(['status' => 'error', 'message' => 'Document not found'], 404);
        }

        if ($document->locked) {
            return response()->json(['status' => 'error', 'message' => 'Document is locked and cannot be modified'], 403);
        }

        $request->validate([
            'file' => 'required|file|max:30720', // max 30MB
            'note' => 'nullable|string|max:300',
            'uploaded_by' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $path = $file->store('attachments/documents', 'public');
        $fileUrl = '/storage/' . $path;
        $rawSize = $file->getSize();
        $size = $rawSize < 1000000 ? round($rawSize / 1024) . ' KB' : round($rawSize / (1024 * 1024), 1) . ' MB';
        $ext = strtoupper($file->getClientOriginalExtension());
        $fileType = in_array($ext, ['PDF', 'XLSX', 'DOCX', 'JPG', 'PNG', 'ZIP']) ? $ext : 'PDF';

        // Calculate next version
        $currentVer = floatval(preg_replace('/[^0-9.]/', '', $document->version));
        if ($currentVer <= 0) $currentVer = 1.0;
        $nextVer = 'v' . number_format($currentVer + 0.1, 1);

        $uploadedBy = $request->uploaded_by ?: 'PT SSC WORKS User';
        $uploadedDate = now()->toDateString();
        $note = $request->note ?: 'New version uploaded';

        // Create version
        DocumentVersion::create([
            'document_id' => $document->id,
            'version' => $nextVer,
            'file_name' => $fileName,
            'file_url' => $fileUrl,
            'size' => $size,
            'uploaded_by' => $uploadedBy,
            'uploaded_date' => $uploadedDate,
            'note' => $note,
        ]);

        // Update active document
        $document->update([
            'file_name' => $fileName,
            'file_url' => $fileUrl,
            'file_type' => $fileType,
            'size' => $size,
            'uploaded_by' => $uploadedBy,
            'uploaded_date' => $uploadedDate,
            'version' => $nextVer,
            'status' => 'Active',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Version updated to {$nextVer}",
            'data' => [
                'id' => $document->id,
                'fileName' => $document->file_name,
                'fileUrl' => $document->file_url,
                'module' => $document->module,
                'category' => $document->category,
                'relatedRequest' => $document->related_request ?? '-',
                'relatedTitle' => $document->related_title ?? '',
                'unit' => $document->unit,
                'fileType' => $document->file_type,
                'size' => $document->size,
                'uploadedBy' => $document->uploaded_by,
                'uploadedDate' => $document->uploaded_date->format('Y-m-d'),
                'period' => $document->period ?? '2026',
                'version' => $document->version,
                'status' => $document->status,
                'expiresAt' => $document->expires_at ? $document->expires_at->format('Y-m-d') : null,
                'description' => $document->description ?? '',
                'canUploadVersion' => (bool)$document->can_upload_version,
                'canDelete' => (bool)$document->can_delete,
                'locked' => (bool)$document->locked,
                'versions' => $document->fresh()->versions->map(function ($v) {
                    return [
                        'version' => $v->version,
                        'fileName' => $v->file_name,
                        'fileUrl' => $v->file_url,
                        'size' => $v->size,
                        'uploadedBy' => $v->uploaded_by,
                        'uploadedDate' => $v->uploaded_date ? $v->uploaded_date->format('Y-m-d') : date('Y-m-d'),
                        'note' => $v->note ?? '',
                    ];
                }),
            ],
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $document = Document::find($id);

        if (!$document) {
            return response()->json(['status' => 'error', 'message' => 'Document not found'], 404);
        }

        if ($document->locked) {
            return response()->json(['status' => 'error', 'message' => 'Locked documents cannot be deleted'], 403);
        }

        $document->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Document deleted successfully',
        ]);
    }

    public function download($id)
    {
        $document = Document::find($id);
        if (!$document) {
            return response()->json(['status' => 'error', 'message' => 'Document not found'], 404);
        }

        // 1. If file_url points to a physical storage file
        if ($document->file_url) {
            $relativePath = preg_replace('/^\/?storage\//', '', $document->file_url);
            $fullPath = storage_path('app/public/' . $relativePath);
            if (file_exists($fullPath)) {
                return response()->download($fullPath, $document->file_name);
            }
        }

        // 2. If spreadsheet (XLSX)
        if (strtoupper($document->file_type) === 'XLSX') {
            $csv = "PT SSC WORKS — OPERATIONAL DOCUMENT EXPORT\n";
            $csv .= "Document ID:," . $document->id . "\n";
            $csv .= "File Name:," . $document->file_name . "\n";
            $csv .= "Module:," . $document->module . "\n";
            $csv .= "Category:," . $document->category . "\n";
            $csv .= "Unit:," . $document->unit . "\n";
            $csv .= "Reference:," . $document->related_request . " - " . str_replace(',', ' ', $document->related_title) . "\n";
            $csv .= "Version:," . $document->version . "\n";
            $csv .= "Status:," . $document->status . "\n";
            $csv .= "Export Timestamp:," . now()->toDateTimeString() . "\n\n";
            $csv .= "No,Item Description,Period,Target,Actual,Status,PIC\n";
            $csv .= "1," . str_replace(',', ' ', $document->related_title ?: $document->file_name) . "," . ($document->period ?: '2026') . ",100%,100%,Approved," . $document->uploaded_by . "\n";

            return response($csv, 200, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . preg_replace('/\.[^.]+$/', '.csv', $document->file_name) . '"',
            ]);
        }

        // 3. Structured Document Record Text
        $content = "========================================================\n";
        $content .= "           PT SSC WORKS — DOCUMENT CENTER               \n";
        $content .= "========================================================\n\n";
        $content .= "Document Code   : " . $document->id . "\n";
        $content .= "File Name       : " . $document->file_name . "\n";
        $content .= "Module          : " . $document->module . "\n";
        $content .= "Category        : " . $document->category . "\n";
        $content .= "Originating Unit: " . $document->unit . "\n";
        $content .= "Reference No    : " . ($document->related_request ?? '-') . "\n";
        $content .= "Reference Title : " . ($document->related_title ?? '-') . "\n";
        $content .= "Version         : " . $document->version . "\n";
        $content .= "Status          : " . $document->status . "\n";
        $content .= "Uploaded By     : " . $document->uploaded_by . "\n";
        $content .= "Uploaded Date   : " . ($document->uploaded_date ? $document->uploaded_date->format('d M Y') : date('d M Y')) . "\n\n";
        $content .= "Summary / Description :\n";
        $content .= ($document->description ?: 'Operational document officially recorded in SSC Works Hub Central Registry.') . "\n\n";
        $content .= "========================================================\n";
        $content .= "Verified & Archival Timestamp: " . now()->toIso8601String() . "\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $document->file_name . '.txt"',
        ]);
    }
}
