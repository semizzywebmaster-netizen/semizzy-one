<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\AuditLog;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $data = [
            'stats' => [
                'total_users' => User::count(),
                'active_users' => User::active()->count(),
                'suspended_users' => User::suspended()->count(),
                'active_addons' => Addon::active()->count(),
                'active_providers' => Provider::active()->count(),
            ],
            'system' => [
                'php' => ['status' => 'healthy', 'message' => 'PHP ' . PHP_VERSION],
                'laravel' => ['status' => 'healthy', 'message' => 'Laravel ' . app()->version()],
                'mysql' => $this->checkMysql(),
                'storage' => $this->checkStorage(),
                'cache' => $this->checkCache(),
                'queue' => $this->checkQueue(),
            ],
            'recent_audit' => AuditLog::with('user')->latest()->limit(20)->get(),
            'recent_users' => User::latest()->limit(5)->get(),
        ];

        return view('admin.dashboard', $data);
    }

    private function checkMysql(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Connected'];
        } catch (\Exception $e) {
            return ['status' => 'critical', 'message' => 'Connection failed'];
        }
    }

    private function checkStorage(): array
    {
        $path = storage_path('app');
        if (!is_writable($path)) {
            return ['status' => 'critical', 'message' => 'Not writable'];
        }

        $bytes = disk_free_space($path);
        $gb = round($bytes / (1024 * 1024 * 1024), 2);

        return [
            'status' => $gb < 1 ? 'warning' : 'healthy',
            'message' => "{$gb} GB free",
        ];
    }

    private function checkCache(): array
    {
        try {
            Cache::put('health_check', 'ok', 60);
            $value = Cache::get('health_check');
            return ['status' => $value === 'ok' ? 'healthy' : 'warning', 'message' => 'Operational'];
        } catch (\Exception $e) {
            return ['status' => 'critical', 'message' => 'Cache error'];
        }
    }

    private function checkQueue(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            return [
                'status' => $pending > 100 ? 'warning' : 'healthy',
                'message' => "{$pending} pending jobs",
            ];
        } catch (\Exception $e) {
            return ['status' => 'warning', 'message' => 'Unknown'];
        }
    }
}