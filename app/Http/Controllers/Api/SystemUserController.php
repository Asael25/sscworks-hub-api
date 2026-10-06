<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SystemUserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'asc')->get()->map(function ($u) {
            return [
                'id' => (string) $u->id,
                'name' => $u->name,
                'email' => $u->identifier,
                'username' => $u->username,
                'role' => $u->role,
                'primaryUnit' => $u->primary_unit,
                'additionalUnits' => $u->additional_units ?? [],
                'hubAccess' => $u->hub_access,
                'menuAccess' => $u->menu_access ?? [],
                'authorities' => $u->authorities ?? [],
                'lastActive' => $u->updated_at ? $u->updated_at->diffForHumans() : 'Never',
            ];
        });

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'password' => 'required|string|min:4',
            'role' => 'required|string',
            'primaryUnit' => 'required|string',
            'additionalUnits' => 'nullable|array',
            'hubAccess' => 'nullable|string|in:Active,Suspended',
            'menuAccess' => 'required|array|min:1',
            'authorities' => 'nullable|array',
        ]);

        $cleanEmail = strtolower(trim($validated['email']));
        if (User::whereRaw('LOWER(identifier) = ?', [$cleanEmail])->exists()) {
            return response()->json(['message' => 'Email atau identifier sudah terdaftar di sistem.'], 422);
        }

        $baseUsername = explode('@', $cleanEmail)[0];
        $baseUsername = preg_replace('/[^a-zA-Z0-9]/', '', $baseUsername) ?: 'user';
        $username = $baseUsername;
        $counter = 1;
        while (User::whereRaw('LOWER(username) = ?', [strtolower($username)])->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        $user = User::create([
            'username' => $username,
            'identifier' => $cleanEmail,
            'name' => $validated['name'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'primary_unit' => $validated['primaryUnit'],
            'additional_units' => $validated['additionalUnits'] ?? [],
            'hub_access' => $validated['hubAccess'] ?? 'Active',
            'menu_access' => $validated['menuAccess'],
            'authorities' => $validated['authorities'] ?? [],
        ]);

        return response()->json([
            'id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->identifier,
            'username' => $user->username,
            'role' => $user->role,
            'primaryUnit' => $user->primary_unit,
            'additionalUnits' => $user->additional_units ?? [],
            'hubAccess' => $user->hub_access,
            'menuAccess' => $user->menu_access ?? [],
            'authorities' => $user->authorities ?? [],
            'lastActive' => 'Just now',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'password' => 'nullable|string',
            'role' => 'required|string',
            'primaryUnit' => 'required|string',
            'additionalUnits' => 'nullable|array',
            'hubAccess' => 'nullable|string|in:Active,Suspended',
            'menuAccess' => 'required|array|min:1',
            'authorities' => 'nullable|array',
        ]);

        $cleanEmail = strtolower(trim($validated['email']));
        $existing = User::whereRaw('LOWER(identifier) = ?', [$cleanEmail])
                        ->where('id', '!=', $user->id)
                        ->first();

        if ($existing) {
            return response()->json(['message' => 'Email ini sudah digunakan oleh akun lain.'], 422);
        }

        $updateData = [
            'name' => $validated['name'],
            'identifier' => $cleanEmail,
            'role' => $validated['role'],
            'primary_unit' => $validated['primaryUnit'],
            'additional_units' => $validated['additionalUnits'] ?? [],
            'hub_access' => $validated['hubAccess'] ?? $user->hub_access,
            'menu_access' => $validated['menuAccess'],
            'authorities' => $validated['authorities'] ?? [],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return response()->json([
            'id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->identifier,
            'username' => $user->username,
            'role' => $user->role,
            'primaryUnit' => $user->primary_unit,
            'additionalUnits' => $user->additional_units ?? [],
            'hubAccess' => $user->hub_access,
            'menuAccess' => $user->menu_access ?? [],
            'authorities' => $user->authorities ?? [],
            'lastActive' => 'Just now',
        ]);
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $newStatus = $user->hub_access === 'Active' ? 'Suspended' : 'Active';
        $user->update(['hub_access' => $newStatus]);

        return response()->json([
            'id' => (string) $user->id,
            'hubAccess' => $user->hub_access,
            'name' => $user->name,
        ]);
    }
}
