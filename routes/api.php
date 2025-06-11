<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserManagementController;

Route::get('/', function () {
    return view('welcome');
});

// User Management API Routes
Route::prefix('users')->group(function () {
    // Public routes (no authentication required)
    Route::post('/create', [UserManagementController::class, 'create']);
    Route::get('/fetch', [UserManagementController::class, 'fetch']);
    Route::post('/authenticate', [UserManagementController::class, 'authenticate']);
    
    // Protected routes (superadmin only)
    Route::middleware(['auth', 'superadmin'])->group(function () {
        Route::post('/promote', [UserManagementController::class, 'promoteUser']);
        Route::post('/demote', [UserManagementController::class, 'demoteUser']);
    });
});
    