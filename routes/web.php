<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClearanceController;
use App\Http\Controllers\StaffClearanceController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\PasswordController;

Route::get('/', function () {
    return redirect()->route('login');
});

// LOGIN AND REGISTRATION
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

// AUTHENTICATED USERS
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [AuthController::class, 'dashboard'])
        ->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // CHANGE PASSWORD
    Route::get('/change-password', [PasswordController::class, 'edit'])
        ->name('password.edit');

    Route::put('/change-password', [PasswordController::class, 'update'])
        ->name('password.update');

    // STUDENT CLEARANCE
    Route::get('/clearance', [ClearanceController::class, 'index'])
        ->name('clearance.index');

    Route::post('/clearance', [ClearanceController::class, 'store'])
        ->name('clearance.store');

    Route::get('/clearance/completion', [ClearanceController::class, 'completion'])
        ->middleware('role:student')
        ->name('clearance.completion');

    // STAFF AND ADMIN
    Route::middleware('role:staff,admin')->group(function () {
        Route::get('/staff/clearances', [StaffClearanceController::class, 'index'])
            ->name('staff.clearances');

        Route::put('/staff/clearances/{id}', [StaffClearanceController::class, 'update'])
            ->name('staff.clearances.update');
    });

    // ADMIN
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

        Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy'])
            ->name('admin.users.destroy');
    });
});
