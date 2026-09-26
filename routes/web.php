<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\HealthController as AdminHealthController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallController;
use Illuminate\Support\Facades\Route;

// ─── Installation Wizard ──────────────────────
Route::get('/install', [InstallController::class, 'index'])->name('install.index');
Route::post('/install/check-requirements', [InstallController::class, 'checkRequirements'])->name('install.requirements');
Route::post('/install/test-database', [InstallController::class, 'testDatabase'])->name('install.test-database');
Route::post('/install/run', [InstallController::class, 'install'])->name('install.run');

// ─── Guest Routes ─────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect('/login'));
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// ─── Authenticated Routes ─────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ─── Admin Routes ──────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Users
        Route::resource('users', UserController::class);

        // Roles
        Route::resource('roles', RoleController::class);

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings/{group}', [SettingsController::class, 'update'])->name('settings.update');

        // Addons
        Route::get('/addons', [AddonController::class, 'index'])->name('addons.index');
        Route::get('/addons/{addon}', [AddonController::class, 'show'])->name('addons.show');
        Route::post('/addons/install', [AddonController::class, 'install'])->name('addons.install');
        Route::post('/addons/{addon}/activate', [AddonController::class, 'activate'])->name('addons.activate');
        Route::post('/addons/{addon}/deactivate', [AddonController::class, 'deactivate'])->name('addons.deactivate');
        Route::delete('/addons/{addon}', [AddonController::class, 'uninstall'])->name('addons.uninstall');

        // Providers
        Route::resource('providers', ProviderController::class);

        // Audit Logs
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
        Route::get('/audit/{audit}', [AuditController::class, 'show'])->name('audit.show');

        // Security
        Route::get('/security', [SecurityController::class, 'index'])->name('security.index');

        // System Health
        Route::get('/health', [AdminHealthController::class, 'index'])->name('health.index');

        // Backups
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');
    });
});