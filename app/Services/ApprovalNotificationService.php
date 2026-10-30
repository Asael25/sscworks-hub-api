<?php

namespace App\Services;

use App\Mail\ApprovalNotificationMail;
use App\Models\Approval;
use App\Models\ApprovalStage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApprovalNotificationService
{
    /**
     * Cari objek user (email/identifier, nama) berdasarkan target nama/username/email atau role.
     */
    public static function resolveUser(?string $target, ?string $role = null, ?string $unitScope = null): ?User
    {
        $target = trim((string) $target);
        $role = trim((string) $role);

        if (!empty($target)) {
            $lower = strtolower($target);

            // 1. Direct match identifier / email
            $user = User::whereRaw('LOWER(identifier) = ?', [$lower])->first();
            if ($user) return $user;

            // 2. Direct match username
            $user = User::whereRaw('LOWER(username) = ?', [$lower])->first();
            if ($user) return $user;

            // 3. Direct match name
            $user = User::whereRaw('LOWER(name) = ?', [$lower])->first();
            if ($user) return $user;

            // 4. Fuzzy match name
            $user = User::where('name', 'LIKE', "%{$target}%")
                ->orWhere('identifier', 'LIKE', "%{$lower}%")
                ->first();
            if ($user) return $user;

            // 4b. Jika target berupa format email valid langsung
            if (filter_var($target, FILTER_VALIDATE_EMAIL)) {
                $user = new User();
                $user->identifier = $target;
                $user->name = explode('@', $target)[0];
                return $user;
            }
        }

        // 5. Match by Role jika target approver spesifik tidak ditemukan
        if (!empty($role)) {
            $lowerRole = strtolower($role);

            // Normalisasi sinonim role
            $roleSynonyms = [$role];
            if (in_array($lowerRole, ['super admin', 'superadmin', 'admin super'])) {
                $roleSynonyms = ['Super Admin', 'Admin Super', 'Superadmin', 'IT & Bussiness System Support'];
            } elseif (in_array($lowerRole, ['general manager', 'gm'])) {
                $roleSynonyms = ['General Manager', 'GM', 'Unit Head'];
            } elseif (in_array($lowerRole, ['staff commercial', 'commercial staff', 'commercial'])) {
                $roleSynonyms = ['Staff Commercial', 'Commercial'];
            }

            $userQuery = User::whereIn('role', $roleSynonyms);
            if ($unitScope && $unitScope !== 'All units' && $unitScope !== '*') {
                $userQuery->where(function ($q) use ($unitScope) {
                    $q->where('primary_unit', 'LIKE', "%{$unitScope}%")
                      ->orWhereJsonContains('additional_units', $unitScope)
                      ->orWhereJsonContains('additional_units', '*');
                });
            }

            $user = $userQuery->orderByRaw("CASE WHEN identifier LIKE '%@gmail.com' OR identifier LIKE '%@yahoo%' OR identifier LIKE '%@outlook%' THEN 0 ELSE 1 END")->first();
            if ($user) return $user;

            // Jika filter unit tidak membuahkan hasil, coba ambil user role tanpa filter unit
            $user = User::whereIn('role', $roleSynonyms)->orderByRaw("CASE WHEN identifier LIKE '%@gmail.com' OR identifier LIKE '%@yahoo%' OR identifier LIKE '%@outlook%' THEN 0 ELSE 1 END")->first();
            if ($user) return $user;
        }

        return null;
    }

    /**
     * Ambil pengaturan preferensi notifikasi sistem (disimpan di storage/app/notification_settings.json)
     */
    public static function getNotificationSettings(): array
    {
        $path = storage_path('app/notification_settings.json');
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data)) return $data;
        }
        return [
            'approval_assigned'    => true,
            'submission_returned'   => true,
            'submission_approved'   => true,
            'deadline_approaching'  => true,
        ];
    }

    /**
     * Periksa apakah jenis notifikasi tertentu diaktifkan di System Settings.
     */
    public static function isNotificationEnabled(string $key): bool
    {
        $settings = self::getNotificationSettings();
        return (bool) ($settings[$key] ?? true);
    }

    /**
     * Kirim email notifikasi ke approver aktif yang menunggu tindakan.
     */
    public static function notifyPendingApprover($approvalOrId, ?ApprovalStage $stage = null): bool
    {
        if (!env('APPROVAL_EMAIL_NOTIFICATIONS_ENABLED', true)) {
            Log::info('[ApprovalNotification] Notifikasi email dinonaktifkan di .env (APPROVAL_EMAIL_NOTIFICATIONS_ENABLED=false)');
            return false;
        }

        if (!self::isNotificationEnabled('approval_assigned')) {
            Log::info('[ApprovalNotification] Notifikasi penugasan approval (approval_assigned) dinonaktifkan di System Settings.');
            return false;
        }

        try {
            $approval = is_object($approvalOrId) ? $approvalOrId : Approval::with('stages')->find($approvalOrId);
            if (!$approval) {
                Log::warning("[ApprovalNotification] Approval tiket tidak ditemukan: {$approvalOrId}");
                return false;
            }

            $stages = $approval->stages()->orderBy('order')->get();
            $totalStages = $stages->count() ?: 1;

            if (!$stage) {
                $stage = $stages->firstWhere('order', $approval->current_stage) ?? $stages->first();
            }

            $stageName = $stage ? ($stage->name ?: ($stage->role . ' (Tahap ' . ($stage->order ?? 1) . ')')) : 'Review Tahap Aktif';
            $approverTarget = $stage ? ($stage->approver ?: $stage->role) : null;
            $approverRole = $stage ? $stage->role : null;
            $unitScope = $stage ? $stage->unit_scope : $approval->unit;

            $recipientUser = self::resolveUser($approverTarget, $approverRole, $unitScope);

            // Jika approver spesifik tidak ditemukan, fallback ke Superadmin atau Rudi
            if (!$recipientUser) {
                $recipientUser = User::where('role', 'Super Admin')
                    ->orWhere('identifier', 'rudi@sscworks.com')
                    ->orWhere('identifier', 'superadmin@sscworks.com')
                    ->first();
            }

            if (!$recipientUser || empty($recipientUser->identifier)) {
                Log::warning("[ApprovalNotification] Tidak ditemukan email penerima yang valid untuk approval {$approval->id}");
                return false;
            }

            $targetEmail = $recipientUser->identifier;
            $recipientEmail = self::normalizeRecipientEmail($targetEmail);
            $recipientName = $recipientUser->name ?: $recipientUser->username ?: 'Reviewer SSC';

            $mailable = new ApprovalNotificationMail(
                type: 'pending',
                recipientName: $recipientName,
                ticketId: $approval->id,
                module: $approval->module,
                title: $approval->request,
                summary: $approval->summary ?? '',
                unit: $approval->unit ?? 'Head Office',
                submitterName: $approval->submitted_by ?? 'User SSC',
                submittedDate: $approval->submitted_date ?? date('d M Y'),
                stageName: $stageName,
                currentStage: (int) ($approval->current_stage ?: 1),
                totalStages: $totalStages,
                priority: $approval->priority ?? 'Medium',
                dueDate: $approval->due_date,
                amountLabel: $approval->amount_label ?? '-',
                status: $approval->status ?? 'Waiting',
                actionLabel: null,
                decisionNote: null,
                actorName: null,
                actorRole: null,
                actionUrl: env('FRONTEND_URL', 'https://tes.sscworks.com') . '/approval-center'
            );

            $hasSmtpCreds = !empty(env('MAIL_USERNAME')) && !empty(env('MAIL_PASSWORD'));
            if ($hasSmtpCreds) {
                try {
                    Mail::to($recipientEmail)->send($mailable);
                    Log::info("[ApprovalNotification] Email pending approval {$approval->id} terkirim via SMTP ke {$recipientEmail}");
                } catch (\Throwable $smtpErr) {
                    Log::warning("[ApprovalNotification] SMTP gagal ({$smtpErr->getMessage()}), fallback ke log mailer.");
                    Mail::mailer('log')->to($recipientEmail)->send($mailable);
                }
            } else {
                Mail::mailer('log')->to($recipientEmail)->send($mailable);
                Log::info("[ApprovalNotification] Email pending approval {$approval->id} dicatat via log mailer (Kredensial SMTP belum diisi).");
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning("[ApprovalNotification] Gagal memproses email pending approval: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim email konfirmasi tanda terima pengajuan kepada akun yang submit request (pemohon).
     */
    public static function notifySubmissionReceipt($approvalOrId): bool
    {
        if (!env('APPROVAL_EMAIL_NOTIFICATIONS_ENABLED', true)) {
            Log::info('[ApprovalNotification] Notifikasi email dinonaktifkan di .env (APPROVAL_EMAIL_NOTIFICATIONS_ENABLED=false)');
            return false;
        }

        try {
            $approval = is_object($approvalOrId) ? $approvalOrId : Approval::with('stages')->find($approvalOrId);
            if (!$approval) {
                Log::warning("[ApprovalNotification] Approval tiket tidak ditemukan untuk tanda terima: {$approvalOrId}");
                return false;
            }

            $stages = $approval->stages()->orderBy('order')->get();
            $totalStages = $stages->count() ?: 1;
            $firstStage = $stages->firstWhere('order', $approval->current_stage) ?? $stages->first();
            $stageName = $firstStage ? ($firstStage->name ?: ($firstStage->role . ' (Tahap ' . ($firstStage->order ?? 1) . ')')) : 'Review Tahap 1';

            // Resolusi akun pemohon (submitter)
            $submitterTarget = $approval->submitted_by_id ?: $approval->submitted_by;
            $submitterUser = self::resolveUser($submitterTarget);

            if (!$submitterUser && !empty($approval->submitted_by)) {
                $submitterUser = self::resolveUser($approval->submitted_by);
            }

            if (!$submitterUser && filter_var($submitterTarget, FILTER_VALIDATE_EMAIL)) {
                $submitterUser = new User();
                $submitterUser->identifier = $submitterTarget;
                $submitterUser->name = $approval->submitted_by ?: 'Pemohon SSC';
            }

            if (!$submitterUser || empty($submitterUser->identifier)) {
                Log::warning("[ApprovalNotification] Email pemohon tidak ditemukan untuk pengajuan {$approval->id} (target: {$submitterTarget})");
                return false;
            }

            $targetEmail = $submitterUser->identifier;
            $recipientEmail = self::normalizeRecipientEmail($targetEmail);
            $recipientName = $submitterUser->name ?: $submitterUser->username ?: ($approval->submitted_by ?: 'Pemohon SSC');

            $mailable = new ApprovalNotificationMail(
                type: 'submitted',
                recipientName: $recipientName,
                ticketId: $approval->id,
                module: $approval->module,
                title: $approval->request,
                summary: $approval->summary ?? '',
                unit: $approval->unit ?? 'Head Office',
                submitterName: $approval->submitted_by ?? $recipientName,
                submittedDate: $approval->submitted_date ?? date('d M Y'),
                stageName: $stageName,
                currentStage: (int) ($approval->current_stage ?: 1),
                totalStages: $totalStages,
                priority: $approval->priority ?? 'Medium',
                dueDate: $approval->due_date,
                amountLabel: $approval->amount_label ?? '-',
                status: $approval->status ?? 'Waiting',
                actionLabel: null,
                decisionNote: null,
                actorName: null,
                actorRole: null,
                actionUrl: env('FRONTEND_URL', 'https://tes.sscworks.com') . '/approval-center'
            );

            $hasSmtpCreds = !empty(env('MAIL_USERNAME')) && !empty(env('MAIL_PASSWORD'));
            if ($hasSmtpCreds) {
                try {
                    Mail::to($recipientEmail)->send($mailable);
                    Log::info("[ApprovalNotification] Email tanda terima pengajuan {$approval->id} terkirim via SMTP ke {$recipientEmail}");
                } catch (\Throwable $smtpErr) {
                    Log::warning("[ApprovalNotification] SMTP gagal ({$smtpErr->getMessage()}), fallback ke log mailer.");
                    Mail::mailer('log')->to($recipientEmail)->send($mailable);
                }
            } else {
                Mail::mailer('log')->to($recipientEmail)->send($mailable);
                Log::info("[ApprovalNotification] Email tanda terima pengajuan {$approval->id} dicatat via log mailer (Kredensial SMTP belum diisi).");
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning("[ApprovalNotification] Gagal memproses email tanda terima pengajuan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim email notifikasi hasil keputusan (Approved/Rejected/Returned) kepada pemohon (submitter).
     */
    public static function notifyDecision(
        Approval $approval,
        string $decision,
        ?string $reason,
        ?string $actorName,
        ?string $actorRole
    ): bool {
        if (!env('APPROVAL_EMAIL_NOTIFICATIONS_ENABLED', true)) {
            Log::info('[ApprovalNotification] Notifikasi email dinonaktifkan di .env (APPROVAL_EMAIL_NOTIFICATIONS_ENABLED=false)');
            return false;
        }

        try {
            $stages = $approval->stages()->orderBy('order')->get();
            $totalStages = $stages->count() ?: 1;
            $currentStage = $stages->firstWhere('order', $approval->current_stage) ?? $stages->first();
            $stageName = $currentStage ? ($currentStage->name ?: $currentStage->role) : 'Review Tahap Aktif';

            $actionMap = [
                'approve' => 'Approved',
                'reject' => 'Rejected',
                'return' => 'Returned',
            ];
            $actionLabel = $actionMap[strtolower($decision)] ?? ucfirst($decision);

            // 1. Notifikasi ke pemohon (submitter)
            $isDecisionNotifEnabled = true;
            if (strtolower($decision) === 'return') {
                $isDecisionNotifEnabled = self::isNotificationEnabled('submission_returned');
            } elseif (strtolower($decision) === 'approve') {
                $isDecisionNotifEnabled = self::isNotificationEnabled('submission_approved');
            }

            $submitterTarget = $approval->submitted_by_id ?: $approval->submitted_by;
            $submitterUser = self::resolveUser($submitterTarget);

            if (!$submitterUser && !empty($approval->submitted_by)) {
                $submitterUser = self::resolveUser($approval->submitted_by);
            }

            if ($isDecisionNotifEnabled && $submitterUser && !empty($submitterUser->identifier)) {
                $targetEmail = $submitterUser->identifier;
                $recipientEmail = self::normalizeRecipientEmail($targetEmail);
                $recipientName = $submitterUser->name ?: $submitterUser->username ?: $approval->submitted_by;

                $mailable = new ApprovalNotificationMail(
                    type: 'decision',
                    recipientName: $recipientName,
                    ticketId: $approval->id,
                    module: $approval->module,
                    title: $approval->request,
                    summary: $approval->summary ?? '',
                    unit: $approval->unit ?? 'Head Office',
                    submitterName: $approval->submitted_by ?? 'User SSC',
                    submittedDate: $approval->submitted_date ?? date('d M Y'),
                    stageName: $stageName,
                    currentStage: (int) ($approval->current_stage ?: 1),
                    totalStages: $totalStages,
                    priority: $approval->priority ?? 'Medium',
                    dueDate: $approval->due_date,
                    amountLabel: $approval->amount_label ?? '-',
                    status: $approval->status ?? $actionLabel,
                    actionLabel: $actionLabel,
                    decisionNote: $reason,
                    actorName: $actorName,
                    actorRole: $actorRole,
                    actionUrl: env('FRONTEND_URL', 'https://tes.sscworks.com') . '/approval-center'
                );

                $hasSmtpCreds = !empty(env('MAIL_USERNAME')) && !empty(env('MAIL_PASSWORD'));
                if ($hasSmtpCreds) {
                    try {
                        Mail::to($recipientEmail)->send($mailable);
                        Log::info("[ApprovalNotification] Email keputusan {$actionLabel} tiket {$approval->id} terkirim via SMTP ke {$recipientEmail}");
                    } catch (\Throwable $smtpErr) {
                        Log::warning("[ApprovalNotification] SMTP gagal ({$smtpErr->getMessage()}), fallback ke log mailer.");
                        Mail::mailer('log')->to($recipientEmail)->send($mailable);
                    }
                } else {
                    Mail::mailer('log')->to($recipientEmail)->send($mailable);
                    Log::info("[ApprovalNotification] Email keputusan {$actionLabel} tiket {$approval->id} dicatat via log mailer (Kredensial SMTP belum diisi).");
                }
            }

            // 2. Jika disetujui dan masih ada tahap berikutnya (status = In Review), kirim notifikasi ke reviewer tahap selanjutnya
            if (strtolower($decision) === 'approve' && $approval->status === 'In Review') {
                if (self::isNotificationEnabled('approval_assigned')) {
                    $nextStage = $stages->firstWhere('order', $approval->current_stage);
                    self::notifyPendingApprover($approval, $nextStage);
                }
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning("[ApprovalNotification] Gagal mengirim email hasil keputusan approval: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Normalisasi email penerima: Pastikan email valid dan trim spasi.
     * Tidak ada redirect otomatis ke MAIL_USERNAME; email dikirim murni ke akun yang bersangkutan.
     */
    public static function normalizeRecipientEmail(string $email): string
    {
        $forward = env('APPROVAL_TEST_FORWARD_EMAIL');
        if (!empty($forward) && filter_var($forward, FILTER_VALIDATE_EMAIL)) {
            Log::info("[ApprovalNotification] Mengalihkan email tujuan ({$email}) ke forward override ({$forward})");
            return trim($forward);
        }
        return trim($email);
    }
}
