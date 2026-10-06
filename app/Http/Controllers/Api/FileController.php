<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:30720', // max 30MB
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $ext = $file->getClientOriginalExtension();

        $path = $file->store('attachments', 'public');
        
        // Generate a public URL for the file
        $url = asset(Storage::disk('public')->url($path));

        return response()->json([
            'url' => $url,
            'name' => $originalName,
            'sizeKb' => round($file->getSize() / 1024),
            'ext' => $ext
        ]);
    }
}
