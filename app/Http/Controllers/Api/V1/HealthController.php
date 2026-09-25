<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends ApiController
{
    public function check(): JsonResponse
    {
        $status = 'healthy';
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
            $checks['mysql'] = ['status' => 'critical', 'message' => 'Connection failed'];
            $status = 'critical';
        }

        // Cache
        try {
            Cache::put('_health', 'ok', 10);
            $checks['cache'] = ['status' => Cache::get('_health') === 'ok' ? 'healthy' : 'warning'];
            Cache::forget('_health');
        } catch (\Exception $e) {
            $checks['cache'] = ['status' => 'warning'];
        }

        return $this->success([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], 'Health check completed.');
    }
}