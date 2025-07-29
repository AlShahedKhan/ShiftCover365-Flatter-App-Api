<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LogoutController::class, 'logout']);

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp']);
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);


// Plan Routes
Route::get('/plans', [PlanController::class, 'index']);
Route::get('/plans/{plan}', [PlanController::class, 'show']);

// Office Routes

// Shift Routes

Route::middleware(['auth:api'])->group(function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('/user-update', [UserController::class, 'saveUserAndOffice']);

    // Verification API
    Route::get('/verification/status', [\App\Http\Controllers\VerificationController::class, 'status']);
    Route::post('/verification/profile', [\App\Http\Controllers\VerificationController::class, 'saveProfile']);
    Route::get('/verification/agreement', [\App\Http\Controllers\VerificationController::class, 'getAgreement']);
    Route::post('/verification/agreement/sign', [\App\Http\Controllers\VerificationController::class, 'signAgreement']);
    Route::post('/verification/staff-code', [\App\Http\Controllers\VerificationController::class, 'validateStaffCode']);

    // Consent Routes
    Route::get('/consent/terms', [\App\Http\Controllers\ConsentController::class, 'getConsentTerms']);
    Route::post('/consent/give', [\App\Http\Controllers\ConsentController::class, 'giveConsent']);
    Route::get('/consent/status', [\App\Http\Controllers\ConsentController::class, 'getConsentStatus']);

    // Professional: View all shifts
    Route::get('/shifts/all', [\App\Http\Controllers\ShiftController::class, 'allShiftsForProfessionals']);
    // Professional: Apply for a shift
    Route::post('/shifts/{shift}/apply', [\App\Http\Controllers\ShiftController::class, 'applyForShift']);
    // Professional: Search for shifts
    Route::get('/shifts/search', [\App\Http\Controllers\ShiftController::class, 'search']);
    // Professional: View a single shift
    Route::get('/shifts/{shift}/view', [\App\Http\Controllers\ShiftController::class, 'showForProfessional']);
    // Manager: Get shifts by date
    Route::get('/shifts/date/{date}', [\App\Http\Controllers\ShiftController::class, 'getShiftsByDateForManagers']);
    // Manager: View all applications for their created shifts
    Route::get('/manager/applications', [\App\Http\Controllers\ShiftController::class, 'applicationsForMyShifts']);
    // Manager: Accept or reject a shift application
    Route::post('/manager/applications/{application}/status', [\App\Http\Controllers\ShiftController::class, 'updateApplicationStatus']);
});
Route::apiResource('shifts', ShiftController::class);
