<?php

use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/approvals', [ApprovalController::class, 'index']);
Route::post('/api/auth/signin', [AuthController::class, 'signIn']);
Route::get('/api/auth/users', [AuthController::class, 'me']);
