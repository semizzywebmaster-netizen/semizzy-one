<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class HealthCheck extends Command
{
    protected $signature = 'health:check {--json : Output as JSON}';
    protected $description = 'Run system health checks';

    public function handle(): int
    {
        $checks = [];

        // PHP
        $checks['php'] = [
            'status' => 'healthy',
            'version' => PHP_VERSION,
        ];

        // MySQL
        try {
            $version = DB::selectOne('SELECT VERSION() as version');
            $checks['mysql'] = [
                'status' => 'healthy',
                'version' => $version->version,
            ];
        } catch (\Exception $e) {
            $checks['mysql'] = [
                'status' => 'critical',
                'message' => $e->getMessage(),
            ];
        }

        // Storage
        $storagePath = storage_path('app');
        $checks['storage'] = [
            'status' => is_writable($storagePath) ? 'healthy' : 'critical',
            'writable' => is_writable($storagePath),
            'free_gb' => round(disk_free_space($storagePath) / (1024 * 1024 * 1024), 2),
        ];

        // Cache
        try {
            Cache::put('_health', 'ok', 10);
            $ok = Cache::get('_health') === 'ok';
            Cache::forget('_health');
            $checks['cache'] = ['status' => $ok ? 'healthy' : 'warning', 'driver' => config('cache.default')];
        } catch (\Exception $e) {
            $checks['cache'] = ['status' => 'critical'];
        }

        // Queue
        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();
            $checks['queue'] = [
                'status' => $failed > 10 ? 'warning' : 'healthy',
                'pending' => $pending,
                'failed' => $failed,
            ];
        } catch (\Exception $e) {
            $checks['queue'] = ['status' => 'warning'];
        }

        // Cron
        $lastCron = Cache::get('cron_last_run');
        $checks['cron'] = [
            'status' => $lastCron ? 'healthy' : 'warning',
            'last_run' => $lastCron ? $lastCron->diffForHumans() : 'never',
        ];

        // PWA
        $checks['pwa'] = [
            'status' => (File::exists(public_path('manifest.webmanifest')) && File::exists(public_path('sw.js'))) ? 'healthy' : 'warning',
            'manifest' => File::exists(public_path('manifest.webmanifest')),
            'service_worker' => File::exists(public_path('sw.js')),
            'offline_page' => File::exists(public_path('offline.html')),
        ];

        // Overall status
        $statuses = array_column($checks, 'status');
        $overall = in_array('critical', $statuses) ? 'CRITICAL' : (in_array('warning', $statuses) ? 'WARNING' : 'HEALTHY');

        if ($this->option('json')) {
            $this->line(json_encode(['status' => $overall, 'checks' => $checks], JSON_PRETTY_PRINT));
        } else {
            $this->info("SEMIZZY ONE Health Check — {$overall}");
            $this->line('');
            foreach ($checks as $name => $check) {
                $status = $check['status'];
                $icon = $status === 'healthy' ? '✓' : ($status === 'warning' ? '⚠' : '✗');
                $detail = $check['version'] ?? $check['message'] ?? ($check['driver'] ?? '');
                $this->line("  {$icon} " . ucfirst($name) . ": {$status}" . ($detail ? " ({$detail})" : ''));
            }
        }

        return in_array('critical', $statuses) ? self::FAILURE : self::SUCCESS;
    }
}