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
Route::get('/chat/presence', [\App\Http\Controllers\Api\ChatController::class, 'getPresence']);
Route::post('/chat/presence/heartbeat', [\App\Http\Controllers\Api\ChatController::class, 'heartbeat']);
Route::post('/chat/presence/leave', [\App\Http\Controllers\Api\ChatController::class, 'leave']);
Route::post('/conversations', [\App\Http\Controllers\Api\ChatController::class, 'storeConversation']);
Route::get('/conversations/{id}/messages', [\App\Http\Controllers\Api\ChatController::class, 'getMessages']);
Route::post('/conversations/{id}/messages', [\App\Http\Controllers\Api\ChatController::class, 'sendMessage']);
Route::delete('/messages/{id}', [\App\Http\Controllers\Api\ChatController::class, 'deleteMessage']);
Route::post('/conversations/{id}/attach-request', [\App\Http\Controllers\Api\ChatController::class, 'attachRequest']);
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
