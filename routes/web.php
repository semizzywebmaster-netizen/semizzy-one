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

// ─── Installation Wizard (§30 — 16 steps) ─────────
Route::get('/install', [InstallController::class, 'index'])->name('install.index');

// Step 2-4: requirement checks
Route::post('/install/check/server', [InstallController::class, 'checkServer'])->name('install.check.server');
Route::post('/install/check/php', [InstallController::class, 'checkPhp'])->name('install.check.php');
Route::post('/install/check/mysql', [InstallController::class, 'checkMysql'])->name('install.check.mysql');

// Step 5: environment
Route::post('/install/environment', [InstallController::class, 'environment'])->name('install.environment');

// Step 6-7: MySQL connection + database validation
Route::post('/install/database/connect', [InstallController::class, 'connectDatabase'])->name('install.database.connect');
Route::post('/install/database/validate', [InstallController::class, 'validateDatabase'])->name('install.database.validate');

// Step 8-9: application settings + admin account
Route::post('/install/application', [InstallController::class, 'application'])->name('install.application');
Route::post('/install/admin', [InstallController::class, 'admin'])->name('install.admin');

// Step 10-11: migrations + core data
Route::post('/install/migrate', [InstallController::class, 'migrate'])->name('install.migrate');
Route::post('/install/seed', [InstallController::class, 'seed'])->name('install.seed');

// Step 12-15: infrastructure & security checks
Route::post('/install/check/storage', [InstallController::class, 'checkStorage'])->name('install.check.storage');
Route::post('/install/check/cache', [InstallController::class, 'checkCache'])->name('install.check.cache');
Route::post('/install/check/pwa', [InstallController::class, 'checkPwa'])->name('install.check.pwa');
Route::post('/install/check/security', [InstallController::class, 'checkSecurity'])->name('install.check.security');

// Step 16: finalization
Route::post('/install/finalize', [InstallController::class, 'finalize'])->name('install.finalize');

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