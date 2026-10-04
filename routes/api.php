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

Route::get('/construction-works',  [ConstructionController::class, 'index']);
Route::post('/construction-works', [ConstructionController::class, 'store']);
Route::put('/construction-works/{id}', [ConstructionController::class, 'update']);

Route::get('/customer-projects',  [CustomerProjectController::class, 'index']);
Route::post('/customer-projects', [CustomerProjectController::class, 'store']);
Route::put('/customer-projects/{id}', [CustomerProjectController::class, 'update']);

Route::get('/legal-requests',  [LegalRequestController::class, 'index']);
Route::post('/legal-requests', [LegalRequestController::class, 'store']);
Route::put('/legal-requests/{id}', [LegalRequestController::class, 'update']);
Route::post('/legal-requests/{id}', [LegalRequestController::class, 'update']);

Route::post('/auth/signin', [AuthController::class, 'signIn']);
Route::get('/auth/users',   [AuthController::class, 'me']);
Route::post('/upload', [App\Http\Controllers\Api\FileController::class, 'upload']);

Route::get('/activity-logs', [\App\Http\Controllers\Api\ActivityLogController::class, 'index']);
Route::post('/activity-logs', [\App\Http\Controllers\Api\ActivityLogController::class, 'store']);

Route::get('/conversations', [\App\Http\Controllers\Api\ChatController::class, 'index']);
Route::post('/conversations', [\App\Http\Controllers\Api\ChatController::class, 'storeConversation']);
Route::get('/conversations/{id}/messages', [\App\Http\Controllers\Api\ChatController::class, 'getMessages']);
Route::post('/conversations/{id}/messages', [\App\Http\Controllers\Api\ChatController::class, 'sendMessage']);
Route::delete('/messages/{id}', [\App\Http\Controllers\Api\ChatController::class, 'deleteMessage']);
Route::post('/conversations/{id}/attach-request', [\App\Http\Controllers\Api\ChatController::class, 'attachRequest']);


