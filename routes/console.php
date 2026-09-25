<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Scheduled Tasks ──────────────────────────────

// Process queue every minute (cPanel cron compatible)
Schedule::command('queue:work --stop-when-empty --max-time=60')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Record cron heartbeat for health checks
Schedule::call(function () {
    Cache::put('cron_last_run', now(), 120);
})->everyMinute();

// Clean expired cache entries
Schedule::command('cache:prune-stale-tags')
    ->daily();

// Clean old failed jobs
Schedule::command('queue:prune-failed --hours=168')
    ->daily();

// Clean old audit logs (keep 90 days)
Schedule::call(function () {
    \App\Models\AuditLog::where('created_at', '<', now()->subDays(90))->delete();
})->daily();

// Provider health checks
Schedule::call(function () {
    $providers = \App\Models\Provider::active()->where('health_check_enabled', true)->get();
    foreach ($providers as $provider) {
        try {
            $start = microtime(true);
            // Provider-specific health check would go here
            $responseTime = (int) ((microtime(true) - $start) * 1000);

            $provider->update([
                'health_status' => 'healthy',
                'last_health_check_at' => now(),
            ]);

            $provider->healthLogs()->create([
                'status' => 'healthy',
                'response_time_ms' => $responseTime,
                'checked_at' => now(),
            ]);
        } catch (\Exception $e) {
            $provider->update([
                'health_status' => 'unhealthy',
                'last_health_check_at' => now(),
                'last_error' => $e->getMessage(),
            ]);

            $provider->healthLogs()->create([
                'status' => 'unhealthy',
                'message' => $e->getMessage(),
                'checked_at' => now(),
            ]);
        }
    }
})->everyFifteenMinutes();