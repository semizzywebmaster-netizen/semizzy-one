<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Provider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class HealthController extends Controller
{
    public function index()
    {
        $health = [
            'application' => $this->checkApplication(),
            'php' => $this->checkPhp(),
            'mysql' => $this->checkMysql(),
            'storage' => $this->checkStorage(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'cron' => $this->checkCron(),
            'pwa' => $this->checkPwa(),
            'addons' => $this->checkAddons(),
            'providers' => $this->checkProviders(),
        ];

        return view('admin.health.index', compact('health'));
    }

    private function checkApplication(): array
    {
        return [
            'status' => 'healthy',
            'message' => 'Laravel ' . app()->version(),
            'details' => [
                'name' => config('app.name'),
                'env' => app()->environment(),
                'debug' => config('app.debug'),
                'version' => '2.0.0',
            ],
        ];
    }

    private function checkPhp(): array
    {
        $required = ['bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'json', 'mbstring', 'openssl', 'pdo_mysql', 'tokenizer', 'xml', 'zip', 'gd', 'intl'];
        $loaded = get_loaded_extensions();
        $missing = array_diff($required, $loaded);

        return [
            'status' => empty($missing) ? 'healthy' : 'warning',
            'message' => 'PHP ' . PHP_VERSION,
            'details' => [
                'version' => PHP_VERSION,
                'loaded_extensions' => count($loaded),
                'missing_extensions' => $missing,
            ],
        ];
    }

    private function checkMysql(): array
    {
        try {
            $version = DB::selectOne('SELECT VERSION() as version');
            return [
                'status' => 'healthy',
                'message' => 'MySQL ' . $version->version,
                'details' => ['version' => $version->version],
            ];
        } catch (\Exception $e) {
            return ['status' => 'critical', 'message' => 'Connection failed'];
        }
    }

    private function checkStorage(): array
    {
        $path = storage_path('app');
        $writable = is_writable($path);
        $bytes = disk_free_space($path);
        $gb = round($bytes / (1024 * 1024 * 1024), 2);

        return [
            'status' => $writable ? ($gb < 1 ? 'warning' : 'healthy') : 'critical',
            'message' => $writable ? "{$gb} GB free" : 'Not writable',
        ];
    }

    private function checkCache(): array
    {
        try {
            Cache::put('_health_check', 'ok', 10);
            $val = Cache::get('_health_check');
            Cache::forget('_health_check');
            return [
                'status' => $val === 'ok' ? 'healthy' : 'warning',
                'message' => config('cache.default') . ' driver',
            ];
        } catch (\Exception $e) {
            return ['status' => 'critical', 'message' => 'Cache error'];
        }
    }

    private function checkQueue(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count() ?? 0;
            return [
                'status' => $failed > 0 ? 'warning' : 'healthy',
                'message' => "{$pending} pending, {$failed} failed",
                'details' => ['pending' => $pending, 'failed' => $failed],
            ];
        } catch (\Exception $e) {
            return ['status' => 'warning', 'message' => 'Unknown'];
        }
    }

    private function checkCron(): array
    {
        $lastRun = Cache::get('cron_last_run');
        if (!$lastRun) {
            return ['status' => 'warning', 'message' => 'No cron run detected'];
        }
        $diff = now()->diffInMinutes($lastRun);
        return [
            'status' => $diff > 60 ? 'warning' : 'healthy',
            'message' => "Last run: {$diff} minutes ago",
        ];
    }

    private function checkPwa(): array
    {
        $manifestExists = File::exists(public_path('manifest.webmanifest'));
        $swExists = File::exists(public_path('sw.js'));
        $offlineExists = File::exists(public_path('offline.html'));

        $allGood = $manifestExists && $swExists && $offlineExists;

        return [
            'status' => $allGood ? 'healthy' : 'warning',
            'message' => $allGood ? 'Configured' : 'Missing PWA files',
            'details' => compact('manifestExists', 'swExists', 'offlineExists'),
        ];
    }

    private function checkAddons(): array
    {
        $total = Addon::count();
        $active = Addon::active()->count();
        $errors = Addon::where('status', 'error')->count();

        return [
            'status' => $errors > 0 ? 'warning' : 'healthy',
            'message' => "{$active} active / {$total} total",
        ];
    }

    private function checkProviders(): array
    {
        $total = Provider::count();
        $active = Provider::active()->count();
        $unhealthy = Provider::where('health_status', 'unhealthy')->count();

        return [
            'status' => $unhealthy > 0 ? 'warning' : 'healthy',
            'message' => "{$active} active / {$total} total",
        ];
    }
}