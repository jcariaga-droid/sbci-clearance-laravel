
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClearanceController;
use App\Http\Controllers\StaffClearanceController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\EmailVerificationController;

Route::get('/', function () {
    return redirect()->route('login');
});

// LOGIN AND REGISTRATION
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

// FORGOT PASSWORD
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])
    ->name('password.request');

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
    ->name('password.email');

// RESET PASSWORD
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])
    ->name('password.store');

// EMAIL VERIFICATION
Route::middleware('auth')->group(function () {

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->name('verification.send');
});

// SIGNED VERIFICATION LINK FROM EMAIL
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

// AUTHENTICATED USERS
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [AuthController::class, 'dashboard'])
        ->middleware('verified.email')
        ->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/change-password', [PasswordController::class, 'edit'])
        ->name('password.edit');

    Route::put('/change-password', [PasswordController::class, 'update'])
        ->name('password.update');

    Route::middleware('verified.email')->group(function () {

        Route::get('/clearance', [ClearanceController::class, 'index'])
            ->name('clearance.index');

        Route::post('/clearance', [ClearanceController::class, 'store'])
            ->name('clearance.store');
    });

    Route::middleware('role:staff,admin')->group(function () {

        Route::get('/staff/clearances', [StaffClearanceController::class, 'index'])
            ->name('staff.clearances');

        Route::put('/staff/clearances/{id}', [StaffClearanceController::class, 'update'])
            ->name('staff.clearances.update');
    });

    Route::middleware('role:admin')->group(function () {

        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
            ->name('admin.dashboard');

        Route::get('/admin/users', [AdminUserController::class, 'index'])
            ->name('admin.users');

        Route::post('/admin/users/staff', [AdminUserController::class, 'storeStaff'])
            ->name('admin.users.storeStaff');

        Route::post('/admin/users/student', [AdminUserController::class, 'storeStudent'])
            ->name('admin.users.storeStudent');

        Route::patch('/admin/users/{id}/student', [AdminUserController::class, 'updateStudent'])
            ->name('admin.users.updateStudent');

        Route::patch('/admin/users/{id}/staff', [AdminUserController::class, 'updateStaff'])
            ->name('admin.users.updateStaff');

        Route::patch('/admin/users/{id}/role', [AdminUserController::class, 'updateRole'])
            ->name('admin.users.updateRole');
    });
});

// TEMPORARY SESSION DIAGNOSTIC
Route::get('/test-session', function (Request $request) {

    $request->session()->put('test_value', 'SBCI_SESSION_WORKING');

    return response()->json([
        'session_driver' => config('session.driver'),
        'session_started' => $request->hasSession(),
        'session_id' => $request->session()->getId(),
        'session_cookie_name' => config('session.cookie'),
        'session_secure' => config('session.secure'),
    ]);
});
