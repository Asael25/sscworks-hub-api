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

        $raw = trim($request->identifier);
        $normalized = strtolower($raw);
        $cleanAlnum = preg_replace('/[^a-zA-Z0-9]/', '', $normalized);

        // Cari user berdasarkan:
        // 1. Email (identifier) lowercase
        // 2. Username lowercase
        // 3. Nama lengkap lowercase
        // 4. Prefix sebelum tanda @ pada email (misal ketik "sultan" cocok dengan "sultan@sscworks.com")
        // 5. Karakter alfanumerik (misal ketik "budi.eula" cocok dengan username "budieula")
        $user = User::where(function ($q) use ($normalized, $cleanAlnum) {
            $q->whereRaw('LOWER(identifier) = ?', [$normalized])
              ->orWhereRaw('LOWER(username) = ?', [$normalized])
              ->orWhereRaw('LOWER(name) = ?', [$normalized]);

            // Pencocokan prefix sebelum @ pada email
            $q->orWhereRaw('LOWER(SUBSTRING_INDEX(identifier, "@", 1)) = ?', [$normalized]);

            if (!empty($cleanAlnum)) {
                $q->orWhereRaw('LOWER(username) = ?', [$cleanAlnum])
                  ->orWhereRaw('REPLACE(REPLACE(LOWER(username), ".", ""), "_", "") = ?', [$cleanAlnum])
                  ->orWhereRaw('REPLACE(REPLACE(LOWER(SUBSTRING_INDEX(identifier, "@", 1)), ".", ""), "_", "") = ?', [$cleanAlnum]);
            }
        })->first();

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

        $user->touchActive();

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
            'eulaAgreed' => (bool) $user->eula_agreed,
            'eulaAgreedAt' => $user->eula_agreed_at ? $user->eula_agreed_at->toIso8601String() : null,
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
                'eulaAgreed' => (bool) $user->eula_agreed,
                'eulaAgreedAt' => $user->eula_agreed_at ? $user->eula_agreed_at->toIso8601String() : null,
            ];
        });

        return response()->json($users);
    }

    public function profile(Request $request)
    {
        $identifier = $request->header('X-User-Identifier') ?: $request->query('identifier');
        if (!$identifier) {
            return response()->json(['message' => 'Identifier pengguna diperlukan.'], 400);
        }

        $normalized = strtolower(trim($identifier));
        $user = User::whereRaw('LOWER(identifier) = ?', [$normalized])
            ->orWhereRaw('LOWER(username) = ?', [$normalized])
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

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
            'eulaAgreed' => (bool) $user->eula_agreed,
            'eulaAgreedAt' => $user->eula_agreed_at ? $user->eula_agreed_at->toIso8601String() : null,
        ]);
    }

    public function acceptEula(Request $request)
    {
        $identifier = $request->header('X-User-Identifier') ?: $request->input('identifier');
        if (!$identifier) {
            return response()->json(['message' => 'Identifier pengguna diperlukan.'], 400);
        }

        $normalized = strtolower(trim($identifier));
        $user = User::whereRaw('LOWER(identifier) = ?', [$normalized])
            ->orWhereRaw('LOWER(username) = ?', [$normalized])
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        $user->eula_agreed = true;
        $user->eula_agreed_at = now();
        $user->eula_version = $request->input('version', '1.0');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Persetujuan EULA & Kebijakan Monitoring Privasi berhasil dicatat.',
            'eulaAgreed' => true,
            'eulaAgreedAt' => $user->eula_agreed_at->toIso8601String(),
        ]);
    }
}
