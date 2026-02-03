<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ACRController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\OfficeController;
use App\Http\Controllers\Api\OfficeAdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public authentication routes (no CSRF/session required - token-based)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/password/forgot', [AuthController::class, 'sendPasswordResetLink']);
Route::post('/auth/password/reset', [AuthController::class, 'resetPassword']);
Route::post('/auth/email/resend', [AuthController::class, 'resendVerification']);
Route::get('/auth/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail']);

// Protected routes (requires Bearer token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refreshToken']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ==================== USER MANAGEMENT ROUTES ====================

    Route::prefix('users')->group(function () {
        // User list (admin)
        Route::get('/', [UserController::class, 'index']);

        // My profile (authenticated user)
        Route::get('/my-profile', [UserController::class, 'myProfile']);
        Route::put('/my-profile', [UserController::class, 'updateMyProfile']);

        // User CRUD (admin)
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    // ==================== ACR ROUTES ====================

    // Dashboard & Statistics
    Route::get('/acr/dashboard/stats', [ACRController::class, 'getDashboardStats']);

    // Employee ACR Management
    Route::prefix('acr')->group(function () {
        // My ACRs (as employee)
        Route::get('/my-acrs', [ACRController::class, 'myAcrs']);

        // My PDFs (as employee)
        Route::get('/my-pdfs', [ACRController::class, 'myPdfs']);
        Route::get('/pdf/{pdfId}/download', [ACRController::class, 'downloadPdf']);

        // Get available IOs and COs for selection
        Route::get('/available-ios', [ACRController::class, 'getAvailableIOs']);
        Route::get('/available-cos', [ACRController::class, 'getAvailableCOs']);

        // Employee CRUD operations
        Route::post('/', [ACRController::class, 'store']);
        Route::get('/{id}', [ACRController::class, 'show']);
        Route::put('/{id}', [ACRController::class, 'update']);
        Route::delete('/{id}', [ACRController::class, 'destroy']);

        // Employee submits to IO
        Route::post('/{id}/submit-to-io', [ACRController::class, 'submitToIO']);

        // Workflow history
        Route::get('/{id}/history', [ACRController::class, 'getWorkflowHistory']);
    });

    // Initiating Officer (IO) Routes
    Route::prefix('io')->group(function () {
        // Pending ACRs for IO
        Route::get('/pending', [ACRController::class, 'pendingForIO']);

        // IO actions on ACR
        Route::put('/acr/{id}', [ACRController::class, 'ioUpdate']);
        Route::post('/acr/{id}/return-to-employee', [ACRController::class, 'ioReturnToEmployee']);
        Route::post('/acr/{id}/submit-to-co', [ACRController::class, 'ioSubmitToCO']);
    });

    // Countersigning Officer (CO) Routes
    Route::prefix('co')->group(function () {
        // Pending ACRs for CO
        Route::get('/pending', [ACRController::class, 'pendingForCO']);

        // CO actions on ACR
        Route::put('/acr/{id}', [ACRController::class, 'coUpdate']);
        Route::post('/acr/{id}/return-to-io', [ACRController::class, 'coReturnToIO']);
        Route::post('/acr/{id}/submit-to-dossier', [ACRController::class, 'coSubmitToDossier']);
    });

    // Dossier Keeper Routes
    Route::prefix('dossier')->group(function () {
        // Pending ACRs for Dossier
        Route::get('/pending', [ACRController::class, 'pendingForDossier']);

        // Dossier actions on ACR
        Route::post('/acr/{id}/complete', [ACRController::class, 'dossierComplete']);
    });

    // ==================== EMPLOYEE MANAGEMENT ROUTES ====================

    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::get('/my-profile', [EmployeeController::class, 'myProfile']);
        Route::put('/my-profile', [EmployeeController::class, 'updateMyProfile']);
        Route::get('/{id}', [EmployeeController::class, 'show']);
        Route::put('/{id}', [EmployeeController::class, 'update']);
        Route::delete('/{id}', [EmployeeController::class, 'destroy']);
    });

    // ==================== OFFICE MANAGEMENT ROUTES ====================

    Route::prefix('offices')->group(function () {
        Route::get('/', [OfficeController::class, 'index']);
        Route::post('/', [OfficeController::class, 'store']);
        Route::get('/hierarchy', [OfficeController::class, 'hierarchy']);
        Route::get('/{id}', [OfficeController::class, 'show']);
        Route::put('/{id}', [OfficeController::class, 'update']);
        Route::delete('/{id}', [OfficeController::class, 'destroy']);
        Route::get('/{id}/employees', [OfficeController::class, 'employees']);
        Route::get('/{id}/first-class-officers', [OfficeController::class, 'firstClassOfficers']);
    });

    // ==================== OFFICE ADMIN ROUTES ====================

    Route::prefix('office-admin')->group(function () {
        // Dossier Keeper Management
        Route::get('/dossier-keepers', [OfficeAdminController::class, 'getDossierKeepers']);
        Route::post('/dossier-keepers/{employeeId}/assign', [OfficeAdminController::class, 'assignDossierKeeper']);
        Route::post('/dossier-keepers/{employeeId}/revoke', [OfficeAdminController::class, 'revokeDossierKeeper']);

        // Employee Management in Office
        Route::get('/employees', [OfficeAdminController::class, 'getOfficeEmployees']);
    });
});
