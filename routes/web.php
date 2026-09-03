<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.email');
    Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify-otp');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.store');
});

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/lock-screen', [AuthController::class, 'showLockScreen'])->name('lock.screen');
    Route::post('/lock-screen/lock', [AuthController::class, 'lockScreen'])->name('lock.lock');
    Route::post('/lock-screen/unlock', [AuthController::class, 'unlockScreen'])->name('lock.unlock');

    Route::get('/password/check-expiry', [PasswordController::class, 'checkPasswordExpiry'])->name('password.check-expiry');
    Route::post('/password/update', [PasswordController::class, 'update'])->name('password.update');
    Route::post('/password/generate-captcha', [PasswordController::class, 'generateCaptcha'])->name('password.captcha');

    // Notification routes
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // common Routes for retrive condition response of data
    Route::get('/get-sub-divisions/{division}', [CommonController::class, 'getDivision']);
    Route::get('/get-property-types/{category}', [CommonController::class, 'getPropertyType']);
    Route::get('/get-property-sub-types/{typeId}', [CommonController::class, 'getPropertySubType']);
    Route::get('/districts/{stateId}', [CommonController::class, 'getDistrict']);
    Route::post('/scheme-list', [CommonController::class, 'getSchemeList']);
    Route::get('/get-scheme-details/{id}', [CommonController::class, 'getSchemeDetails']);
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/document-requests/upload', [\App\Http\Controllers\DashboardController::class, 'uploadDocumentRequest'])->name('allottee.document-requests.upload');
    Route::post('/apply-application', [\App\Http\Controllers\DashboardController::class, 'applyForApplication'])->name('allottee.apply.application');
    Route::post('/applications/{application}/upload-signed-agreement', [\App\Http\Controllers\DashboardController::class, 'uploadSignedAgreement'])->name('allottee.applications.upload-signed-agreement');
    
    // Allottee Payment and Document Routes
    Route::post('/initial-payment/pay', [\App\Http\Controllers\Admin\AllotteePaymentController::class, 'payInitialPayment'])->name('allottee.initial-payment.pay');
    Route::post('/one-time-pay', [\App\Http\Controllers\Admin\AllotteePaymentController::class, 'payOnetimePayment'])->name('allottee.one-time-payment.pay');
    Route::post('/signed/document/uploads', [\App\Http\Controllers\Admin\AllotteeController::class, 'signedDocumentUploads'])->name('allottee.signed.document.uploads');
    Route::post('/allottees/{allottee}/payment-option', [\App\Http\Controllers\Admin\AllotteeController::class, 'updatePaymentOption'])->name('allottees.payment-option');
    Route::get('/payment/success/{id}', [\App\Http\Controllers\Admin\AllotteePaymentController::class, 'paymentSuccess'])->name('modules.payment.success');

    // Allottee EMI Routes (Portal)
    Route::prefix('allottee')->name('allottee.')->group(function () {
        Route::get('{allottee}/emi-dashboard', [\App\Http\Controllers\Admin\AllotteeEmiController::class, 'dashboard'])->name('emi.dashboard');
        Route::get('{allottee}/emi-schedule', [\App\Http\Controllers\Admin\AllotteeEmiController::class, 'schedule'])->name('emi.schedule');
        Route::get('{allottee}/pay-emi', [\App\Http\Controllers\Admin\AllotteeEmiController::class, 'payEmi'])->name('emi.pay');
        Route::get('{allottee}/emi-history', [\App\Http\Controllers\Admin\AllotteeEmiController::class, 'history'])->name('emi.history');
        Route::post('{allottee}/process-emi-payment', [\App\Http\Controllers\Admin\AllotteeEmiController::class, 'processPayment'])->name('emi.process-payment');
        Route::post('{allottee}/pre-payment', [\App\Http\Controllers\Admin\AllotteeEmiController::class, 'prePayment'])->name('emi.pre-payment');
        Route::post('{allottee}/close-loan', [\App\Http\Controllers\Admin\AllotteeEmiController::class, 'closeLoan'])->name('emi.close');
        Route::get('{allottee}/emi-statement', [\App\Http\Controllers\Admin\AllotteeEmiController::class, 'downloadStatement'])->name('emi.statement');
        Route::post('refresh-penalties', [\App\Http\Controllers\Admin\AllotteeEmiController::class, 'refreshPenalties'])->name('emi.refresh-penalties');
    });

    Route::get('/{blade}', [\App\Http\Controllers\DashboardController::class, 'section'])->name('dashboard.section');
});

// Media Fallback Routes
Route::get('/media/profile/{filename}', [\App\Http\Controllers\MediaController::class, 'profileImage'])->name('media.profile');
Route::get('/media/document', [\App\Http\Controllers\MediaController::class, 'document'])->name('media.document');
Route::get('/media/image', [\App\Http\Controllers\MediaController::class, 'genericImage'])->name('media.image');

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

