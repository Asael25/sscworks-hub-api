<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemUnit;
use Illuminate\Http\Request;

class SystemUnitController extends Controller
{
    public function index()
    {
        $units = SystemUnit::orderBy('id', 'asc')->get()->map(function ($u) {
            return $u->toFrontendSummary();
        });

        return response()->json($units);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:system_units,code',
            'name' => 'required|string|max:255|unique:system_units,name',
            'type' => 'required|string|in:Branch,Unit,Project',
            'parent' => 'nullable|string|max:255',
        ]);

        // Generate next UNT-XXX ID
        $allIds = SystemUnit::pluck('id')->toArray();
        $maxNum = 0;
        foreach ($allIds as $existingId) {
            if (preg_match('/UNT-(\d+)/', $existingId, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }
        $nextId = 'UNT-' . str_pad($maxNum + 1, 3, '0', STR_PAD_LEFT);

        $unit = SystemUnit::create([
            'id' => $nextId,
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'type' => $validated['type'],
            'parent' => $validated['parent'] ?: '—',
            'status' => 'Active',
        ]);

        return response()->json($unit->toFrontendSummary(), 201);
    }

    public function update(Request $request, $id)
    {
        $unit = SystemUnit::findOrFail($id);

        if ($unit->type === 'Head Office') {
            return response()->json([
                'message' => 'Head Office adalah unit root permanen dan tidak dapat diubah strukturnya.'
            ], 422);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:system_units,code,' . $id,
            'name' => 'required|string|max:255|unique:system_units,name,' . $id,
            'type' => 'required|string|in:Branch,Unit,Project',
            'parent' => 'nullable|string|max:255',
        ]);

        $unit->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'type' => $validated['type'],
            'parent' => $validated['parent'] ?: '—',
        ]);

        return response()->json($unit->toFrontendSummary());
    }

    public function toggleStatus($id)
    {
        $unit = SystemUnit::findOrFail($id);

        if ($unit->type === 'Head Office') {
            return response()->json([
                'message' => 'Head Office adalah unit permanen dan tidak dapat dinonaktifkan.'
            ], 422);
        }

        $newStatus = $unit->status === 'Active' ? 'Inactive' : 'Active';
        $unit->update(['status' => $newStatus]);

        return response()->json($unit->toFrontendSummary());
    }

    public function destroy($id)
    {
        $unit = SystemUnit::findOrFail($id);

        if ($unit->type === 'Head Office') {
            return response()->json([
                'message' => 'Head Office adalah unit root permanen dan tidak dapat dihapus.'
            ], 422);
        }

        if ($unit->calculateMembersCount() > 0) {
            return response()->json([
                'message' => 'Unit ini masih memiliki akun anggota aktif. Harap pindahkan anggota terlebih dahulu atau gunakan fitur nonaktifkan status.'
            ], 422);
        }

        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unit berhasil dihapus dari sistem.'
        ]);
    }
}
