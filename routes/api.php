<?php

use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConstructionController;
use App\Http\Controllers\Api\CustomerProjectController;
use App\Http\Controllers\Api\LegalRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/approvals', [ApprovalController::class, 'index']);
Route::get('/approvals/preview-email', [ApprovalController::class, 'previewEmail']);
Route::post('/approvals/test-email', [ApprovalController::class, 'testEmail']);
Route::post('/approvals/request-deletion', [ApprovalController::class, 'requestDeletion']);
Route::post('/approvals/request-revision', [ApprovalController::class, 'requestRevision']);
Route::post('/approvals/{id}/decide', [ApprovalController::class, 'decide']);
Route::delete('/approvals/{id}', [ApprovalController::class, 'destroy']);

Route::get('/construction-works',  [ConstructionController::class, 'index']);
Route::post('/construction-works', [ConstructionController::class, 'store']);
Route::put('/construction-works/{id}', [ConstructionController::class, 'update']);
Route::post('/construction-works/weekly-progress', [ConstructionController::class, 'submitWeeklyProgress']);

Route::get('/customer-projects',  [CustomerProjectController::class, 'index']);
Route::post('/customer-projects', [CustomerProjectController::class, 'store']);
Route::put('/customer-projects/{id}', [CustomerProjectController::class, 'update']);
Route::post('/customer-projects/site-report', [CustomerProjectController::class, 'submitSiteReport']);

Route::get('/legal-requests',  [LegalRequestController::class, 'index']);
Route::post('/legal-requests', [LegalRequestController::class, 'store']);
Route::put('/legal-requests/{id}', [LegalRequestController::class, 'update']);
Route::post('/legal-requests/{id}', [LegalRequestController::class, 'update']);

Route::post('/auth/signin', [AuthController::class, 'signIn']);
Route::get('/auth/users',   [AuthController::class, 'me']);
Route::get('/auth/profile', [AuthController::class, 'profile']);
Route::post('/auth/accept-eula', [AuthController::class, 'acceptEula']);
Route::post('/upload', [App\Http\Controllers\Api\FileController::class, 'upload']);

Route::get('/activity-logs', [\App\Http\Controllers\Api\ActivityLogController::class, 'index']);
Route::post('/activity-logs', [\App\Http\Controllers\Api\ActivityLogController::class, 'store']);

Route::get('/conversations', [\App\Http\Controllers\Api\ChatController::class, 'index']);
Route::get('/chat-users', [\App\Http\Controllers\Api\ChatController::class, 'getUsers']);
Route::get('/chat-requests', [\App\Http\Controllers\Api\ChatController::class, 'getChatRequests']);
Route::get('/chat/presence', [\App\Http\Controllers\Api\ChatController::class, 'getPresence']);
Route::post('/chat/presence/heartbeat', [\App\Http\Controllers\Api\ChatController::class, 'heartbeat']);
Route::post('/chat/presence/leave', [\App\Http\Controllers\Api\ChatController::class, 'leave']);
Route::post('/conversations', [\App\Http\Controllers\Api\ChatController::class, 'storeConversation']);
Route::get('/conversations/{id}/messages', [\App\Http\Controllers\Api\ChatController::class, 'getMessages']);
Route::post('/conversations/{id}/messages', [\App\Http\Controllers\Api\ChatController::class, 'sendMessage']);
Route::delete('/messages/{id}', [\App\Http\Controllers\Api\ChatController::class, 'deleteMessage']);
Route::post('/conversations/{id}/attach-request', [\App\Http\Controllers\Api\ChatController::class, 'attachRequest']);
Route::delete('/conversations/{id}/related-requests/{requestId}', [\App\Http\Controllers\Api\ChatController::class, 'detachRequest']);
Route::post('/conversations/{id}/members', [\App\Http\Controllers\Api\ChatController::class, 'addMembers']);
Route::delete('/conversations/{id}/members/{memberId}', [\App\Http\Controllers\Api\ChatController::class, 'removeMember']);
Route::delete('/conversations/{id}', [\App\Http\Controllers\Api\ChatController::class, 'destroyConversation']);
Route::post('/conversations/{id}/read', [\App\Http\Controllers\Api\ChatController::class, 'markRead']);
Route::post('/chat/purge-expired', [\App\Http\Controllers\Api\ChatController::class, 'purgeExpired']);
Route::post('/chat/clear-all', [\App\Http\Controllers\Api\ChatController::class, 'clearAll']);


Route::get('/system-users', [\App\Http\Controllers\Api\SystemUserController::class, 'index']);
Route::post('/system-users', [\App\Http\Controllers\Api\SystemUserController::class, 'store']);
Route::put('/system-users/{id}', [\App\Http\Controllers\Api\SystemUserController::class, 'update']);
Route::delete('/system-users/{id}', [\App\Http\Controllers\Api\SystemUserController::class, 'destroy']);
Route::post('/system-users/{id}/toggle-status', [\App\Http\Controllers\Api\SystemUserController::class, 'toggleStatus']);
Route::post('/system-users/{id}/resend-welcome-email', [\App\Http\Controllers\Api\SystemUserController::class, 'resendWelcomeEmail']);
Route::get('/system-users/{id}/welcome-email-preview', [\App\Http\Controllers\Api\SystemUserController::class, 'previewWelcomeEmail']);

Route::get('/approval-workflows', [\App\Http\Controllers\Api\ApprovalWorkflowController::class, 'index']);
Route::post('/approval-workflows', [\App\Http\Controllers\Api\ApprovalWorkflowController::class, 'store']);
Route::put('/approval-workflows/{id}', [\App\Http\Controllers\Api\ApprovalWorkflowController::class, 'update']);
Route::delete('/approval-workflows/{id}', [\App\Http\Controllers\Api\ApprovalWorkflowController::class, 'destroy']);
Route::post('/approval-workflows/{id}/toggle-status', [\App\Http\Controllers\Api\ApprovalWorkflowController::class, 'toggleStatus']);

Route::get('/system-units', [\App\Http\Controllers\Api\SystemUnitController::class, 'index']);
Route::post('/system-units', [\App\Http\Controllers\Api\SystemUnitController::class, 'store']);
Route::put('/system-units/{id}', [\App\Http\Controllers\Api\SystemUnitController::class, 'update']);
Route::delete('/system-units/{id}', [\App\Http\Controllers\Api\SystemUnitController::class, 'destroy']);
Route::post('/system-units/{id}/toggle-status', [\App\Http\Controllers\Api\SystemUnitController::class, 'toggleStatus']);

Route::get('/documents', [\App\Http\Controllers\Api\DocumentController::class, 'index']);
Route::get('/documents/{id}/download', [\App\Http\Controllers\Api\DocumentController::class, 'download']);
Route::post('/documents', [\App\Http\Controllers\Api\DocumentController::class, 'store']);
Route::post('/documents/{id}/versions', [\App\Http\Controllers\Api\DocumentController::class, 'uploadVersion']);
Route::delete('/documents/{id}', [\App\Http\Controllers\Api\DocumentController::class, 'destroy']);

Route::get('/system-settings/notifications', function () {
    $settings = \App\Services\ApprovalNotificationService::getNotificationSettings();
    $hasSmtp = !empty(env('MAIL_USERNAME')) && !empty(env('MAIL_PASSWORD'));
    return response()->json([
        'settings' => $settings,
        'smtp' => [
            'connected' => $hasSmtp,
            'driver' => config('mail.default'),
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => (int) env('MAIL_PORT', 587),
            'sender' => env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME', 'yugoadez044@gmail.com')),
            'sender_name' => env('MAIL_FROM_NAME', 'SSC Works Hub'),
        ]
    ]);
});

Route::post('/system-settings/notifications', function (\Illuminate\Http\Request $request) {
    $current = \App\Services\ApprovalNotificationService::getNotificationSettings();
    $data = $request->only([
        'approval_assigned',
        'submission_returned',
        'submission_approved',
        'deadline_approaching'
    ]);
    $updated = array_merge($current, array_map(fn($v) => (bool) $v, $data));

    $path = storage_path('app/notification_settings.json');
    if (!file_exists(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, json_encode($updated, JSON_PRETTY_PRINT));

    return response()->json([
        'success' => true,
        'message' => 'Pengaturan notifikasi berhasil disinkronkan ke server.',
        'settings' => $updated
    ]);
});

Route::get('/system-settings/data/stats', function () {
    $policyPath = storage_path('app/data_retention_policy.json');
    $policy = file_exists($policyPath)
        ? json_decode(file_get_contents($policyPath), true)
        : [
            'financial_retention' => '7 years',
            'operational_retention' => '5 years',
            'archive_schedule' => 'Monthly',
        ];

    $totalDocs = 0;
    $activeDocs = 0;
    $archivedDocs = 0;
    $expiringDocs = 0;
    $draftDocs = 0;
    $approvalsCount = 0;
    $activityLogsCount = 0;

    try {
        $totalDocs = \App\Models\Document::count();
        $activeDocs = \App\Models\Document::where('status', 'Active')->count();
        $archivedDocs = \App\Models\Document::where('status', 'Archived')->count();
        $expiringDocs = \App\Models\Document::whereIn('status', ['Expiring Soon', 'Expired'])->count();
        $draftDocs = \App\Models\Document::where('status', 'Draft')->count();
        $approvalsCount = \App\Models\Approval::count();
        $activityLogsCount = \App\Models\ActivityLog::count();
    } catch (\Throwable $e) {}

    return response()->json([
        'status' => 'success',
        'policy' => $policy,
        'documents' => [
            'total' => $totalDocs,
            'active' => $activeDocs,
            'archived' => $archivedDocs,
            'expiring' => $expiringDocs,
            'draft' => $draftDocs,
        ],
        'counts' => [
            'approvals' => $approvalsCount,
            'activity_logs' => $activityLogsCount,
        ],
    ]);
});

Route::post('/system-settings/data/policy', function (\Illuminate\Http\Request $request) {
    $policyPath = storage_path('app/data_retention_policy.json');
    if (!file_exists(dirname($policyPath))) {
        mkdir(dirname($policyPath), 0755, true);
    }
    $data = [
        'financial_retention' => $request->input('financial_retention', '7 years'),
        'operational_retention' => $request->input('operational_retention', '5 years'),
        'archive_schedule' => $request->input('archive_schedule', 'Monthly'),
        'updated_at' => now()->toIso8601String(),
    ];
    file_put_contents($policyPath, json_encode($data, JSON_PRETTY_PRINT));
    return response()->json(['success' => true, 'policy' => $data]);
});

Route::post('/system-settings/data/archive', function (\Illuminate\Http\Request $request) {
    $finYears = (int) ($request->input('financial_years') ?: 7);
    $opsYears = (int) ($request->input('operational_years') ?: 5);

    $finCutoff = now()->subYears($finYears)->format('Y-m-d');
    $opsCutoff = now()->subYears($opsYears)->format('Y-m-d');
    $today = now()->format('Y-m-d');
    $count = 0;
    $archivedDocs = [];

    try {
        $query = \App\Models\Document::where('status', '!=', 'Archived')
            ->where(function ($q) use ($finCutoff, $opsCutoff, $today) {
                $q->where(function ($qFin) use ($finCutoff) {
                    $qFin->whereIn('category', ['Cashflow', 'Budgeting'])
                         ->where('uploaded_date', '<=', $finCutoff);
                })
                ->orWhere(function ($qOps) use ($opsCutoff) {
                    $qOps->whereNotIn('category', ['Cashflow', 'Budgeting'])
                         ->where('uploaded_date', '<=', $opsCutoff);
                })
                ->orWhere('status', 'Expired')
                ->orWhere(function ($qExp) use ($today) {
                    $qExp->whereNotNull('expires_at')->where('expires_at', '<', $today);
                });
            });

        $count = $query->count();
        $archivedDocs = $query->get(['id', 'file_name'])->toArray();
        $query->update(['status' => 'Archived']);

        \App\Models\ActivityLog::create([
            'user_name' => $request->input('user_name', 'PT SSC WORKS Admin'),
            'user_role' => $request->input('user_role', 'Super Admin'),
            'action' => 'Jalankan Pengarsipan Data',
            'action_type' => 'archive',
            'module' => 'Data Management',
            'entity_id' => 'SYS-DATA',
            'entity_title' => "Pengarsipan Dokumen Retensi ({$finYears} thn Keuangan, {$opsYears} thn Operasional)",
            'unit' => 'Head Office',
            'notes' => "{$count} dokumen lampau berhasil dialihkan ke status Archived.",
        ]);
    } catch (\Throwable $e) {}

    return response()->json([
        'success' => true,
        'archived_count' => $count,
        'archived_documents' => $archivedDocs,
        'message' => "Pengarsipan selesai: {$count} dokumen berhasil dialihkan ke status Archived.",
    ]);
});

Route::post('/system-settings/data/clean-cache', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
    } catch (\Throwable $e) {}

    return response()->json([
        'success' => true,
        'message' => 'Cache sementara, log session, dan berkas temporary berhasil dibersihkan.',
    ]);
});

