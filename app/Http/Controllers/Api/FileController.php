<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // max 50MB
            'module' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $ext = $file->getClientOriginalExtension();

        // 1. Tentukan nama subfolder secara otomatis
        $subfolder = 'general';
        if ($request->filled('module')) {
            $subfolder = Str::slug($request->input('module'));
        } else {
            // Deteksi otomatis dari halaman web yang memanggil (Referer)
            $referer = strtolower($request->header('referer', ''));
            if (str_contains($referer, 'customer-project')) {
                $subfolder = 'customer-projects';
            } elseif (str_contains($referer, 'construction') || str_contains($referer, 'internal-construction') || str_contains($referer, 'weekly')) {
                $subfolder = 'internal-construction';
            } elseif (str_contains($referer, 'legal') || str_contains($referer, 'contract')) {
                $subfolder = 'legal-operations';
            } elseif (str_contains($referer, 'approval')) {
                $subfolder = 'approvals';
            } elseif (str_contains($referer, 'budget')) {
                $subfolder = 'budgeting';
            } elseif (str_contains($referer, 'cashflow')) {
                $subfolder = 'cashflow';
            } elseif (str_contains($referer, 'chat')) {
                $subfolder = 'chat';
            } elseif (str_contains($referer, 'document')) {
                $subfolder = 'documents';
            }
        }

        // 2. Simpan ke subfolder tujuan (misal: attachments/customer-projects)
        $targetDir = 'attachments/' . $subfolder;
        $path = $file->store($targetDir, 'public');
        
        // 3. Buat URL publik yang aman
        $url = '/storage/' . $path;

        return response()->json([
            'url' => $url,
            'name' => $originalName,
            'sizeKb' => round($file->getSize() / 1024),
            'ext' => $ext,
            'folder' => $subfolder
        ]);
    }
}
