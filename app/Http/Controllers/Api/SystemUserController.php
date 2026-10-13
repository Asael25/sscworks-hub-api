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
                'eulaAgreed' => (bool) $u->eula_agreed,
                'eulaAgreedAt' => $u->eula_agreed_at ? $u->eula_agreed_at->translatedFormat('d M Y, H:i') : null,
                'eulaVersion' => $u->eula_version ?? '1.0',
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
            'eula_agreed' => false,
            'eula_agreed_at' => null,
            'eula_version' => '1.0',
        ]);

        // Send welcome email with login information and EULA notification
        try {
            \Illuminate\Support\Facades\Mail::to($cleanEmail)->send(
                new \App\Mail\NewAccountWelcomeMail(
                    recipientName: $validated['name'],
                    username: $username,
                    email: $cleanEmail,
                    plainPassword: $validated['password'],
                    role: $validated['role'],
                    unit: $validated['primaryUnit']
                )
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal mengirim welcome email ke {$cleanEmail}: " . $e->getMessage());
        }

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
            'eulaAgreed' => false,
            'eulaAgreedAt' => null,
            'eulaVersion' => '1.0',
            'lastActive' => 'Just now',
        ], 201);
    }

    private function findUserByIdentifier($id, ?string $fallbackEmail = null): ?User
    {
        if (is_numeric($id)) {
            $user = User::find($id);
            if ($user) return $user;
        }

        if (is_string($id) && str_starts_with(strtoupper($id), 'USR-')) {
            $num = (int) preg_replace('/\D/', '', $id);
            if ($num > 0) {
                $user = User::find($num);
                if ($user) return $user;
            }
        }

        if (is_string($id)) {
            $user = User::whereRaw('LOWER(identifier) = ?', [strtolower(trim($id))])
                ->orWhereRaw('LOWER(username) = ?', [strtolower(trim($id))])
                ->first();
            if ($user) return $user;
        }

        if ($fallbackEmail) {
            $cleanEmail = strtolower(trim($fallbackEmail));
            $user = User::whereRaw('LOWER(identifier) = ?', [$cleanEmail])->first();
            if ($user) return $user;
        }

        return null;
    }

    public function update(Request $request, $id)
    {
        $user = $this->findUserByIdentifier($id, $request->input('email'));

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

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

        if (!empty($validated['password']) && trim($validated['password']) !== '') {
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
        $user = $this->findUserByIdentifier($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        $newStatus = $user->hub_access === 'Active' ? 'Suspended' : 'Active';
        $user->update(['hub_access' => $newStatus]);

        return response()->json([
            'id' => (string) $user->id,
            'hubAccess' => $user->hub_access,
            'name' => $user->name,
        ]);
    }

    public function destroy($id)
    {
        $user = $this->findUserByIdentifier($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus.']);
    }

    public function resendWelcomeEmail($id, Request $request)
    {
        $user = $this->findUserByIdentifier($id, $request->input('email'));
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        $plainPassword = $request->input('password') ?: '(Gunakan password yang telah ditentukan sebelumnya)';

        try {
            \Illuminate\Support\Facades\Mail::to($user->identifier)->send(
                new \App\Mail\NewAccountWelcomeMail(
                    recipientName: $user->name,
                    username: $user->username,
                    email: $user->identifier,
                    plainPassword: $plainPassword,
                    role: $user->role,
                    unit: $user->primary_unit
                )
            );

            return response()->json([
                'success' => true,
                'message' => "Email informasi akun dan pemberitahuan EULA berhasil dikirim ulang ke {$user->identifier}.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Gagal mengirim email: " . $e->getMessage(),
            ], 500);
        }
    }

    public function previewWelcomeEmail($id, Request $request)
    {
        $user = $this->findUserByIdentifier($id, $request->input('email'));
        $name = $user ? $user->name : 'Pengguna Baru PT SSC';
        $email = $user ? $user->identifier : 'user@ssc.id';
        $username = $user ? $user->username : 'user123';
        $role = $user ? $user->role : 'Operational Staff';
        $unit = $user ? $user->primary_unit : 'Head Office';
        $password = 'Password123*';

        return view('emails.new-account-welcome', [
            'recipientName' => $name,
            'username' => $username,
            'email' => $email,
            'plainPassword' => $password,
            'role' => $role,
            'unit' => $unit,
            'loginUrl' => env('FRONTEND_URL', 'http://localhost:5173'),
            'createdAt' => date('d M Y, H:i'),
        ]);
    }
}
