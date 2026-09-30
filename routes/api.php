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

Route::get('/customer-projects',  [CustomerProjectController::class, 'index']);
Route::post('/customer-projects', [CustomerProjectController::class, 'store']);

Route::get('/legal-requests',  [LegalRequestController::class, 'index']);
Route::post('/legal-requests', [LegalRequestController::class, 'store']);
Route::put('/legal-requests/{id}', [LegalRequestController::class, 'update']);
Route::post('/legal-requests/{id}', [LegalRequestController::class, 'update']);

Route::post('/auth/signin', [AuthController::class, 'signIn']);
Route::get('/auth/users',   [AuthController::class, 'me']);
