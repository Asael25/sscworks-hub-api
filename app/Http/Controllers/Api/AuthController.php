<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function signIn(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        $normalized = strtolower(trim($request->identifier));

        $user = User::whereRaw('LOWER(identifier) = ?', [$normalized])
            ->orWhereRaw('LOWER(username) = ?', [$normalized])
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email/username atau password salah. Silakan coba lagi.'
            ], 401);
        }

        if ($user->hub_access === 'Suspended') {
            return response()->json([
                'message' => 'Akun Anda sedang dinonaktifkan (Suspended). Silakan hubungi Super Admin.'
            ], 403);
        }

        $remember = $request->boolean('remember', false);
        $expiresAt = now()->addHours($remember ? 720 : 8)->timestamp * 1000;

        return response()->json([
            'userId' => $user->username,
            'identifier' => $user->identifier,
            'displayName' => $user->name,
            'role' => $user->role,
            'primaryUnit' => $user->primary_unit,
            'additionalUnits' => $user->additional_units ?? [],
            'hubAccess' => $user->hub_access,
            'menuAccess' => $user->menu_access ?? [],
            'authorities' => $user->authorities ?? [],
            'token' => 'bearer.' . bin2hex(random_bytes(16)),
            'expiresAt' => $expiresAt,
        ]);
    }

    public function me(Request $request)
    {
        $users = User::all()->map(function ($user) {
            return [
                'userId' => $user->username,
                'identifier' => $user->identifier,
                'displayName' => $user->name,
                'role' => $user->role,
                'primaryUnit' => $user->primary_unit,
                'additionalUnits' => $user->additional_units ?? [],
                'hubAccess' => $user->hub_access,
                'menuAccess' => $user->menu_access ?? [],
                'authorities' => $user->authorities ?? [],
            ];
        });

        return response()->json($users);
    }
}
