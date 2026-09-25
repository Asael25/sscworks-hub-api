<?php

use App\Http\Controllers\Api\ApprovalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/approvals', [ApprovalController::class, 'index']);
