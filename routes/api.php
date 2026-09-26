<?php

use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/approvals', [ApprovalController::class, 'index']);
Route::post('/auth/signin', [AuthController::class, 'signIn']);
Route::get('/auth/users', [AuthController::class, 'me']);
