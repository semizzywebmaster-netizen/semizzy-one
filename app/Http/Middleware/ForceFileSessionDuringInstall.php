<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Forces the "file" session driver while the installation wizard is running.
 *
 * On a truly fresh install the `sessions` table does not exist yet, and the
 * default SESSION_DRIVER is "database". Without this, the CSRF token cannot be
 * persisted and every POST to /install/* fails with HTTP 419 — making the
 * wizard unreachable exactly when it is needed most.
 *
 * This must be prepended to the "web" group so it runs before StartSession.
 */
class ForceFileSessionDuringInstall
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isInstalling($request)) {
            config([
                'session.driver' => 'file',
                // The cache store is also not migrated yet on a fresh install.
                'cache.default' => 'file',
            ]);
        }

        return $next($request);
    }

    private function isInstalling(Request $request): bool
    {
        if (!file_exists(storage_path('app/.installed')) && $request->is('install*')) {
            return true;
        }

        return false;
    }
}
