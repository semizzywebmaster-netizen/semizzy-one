<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    public function handle(Request $request, Closure $next): Response
    {
        $installedFile = storage_path('app/.installed');

        if (!file_exists($installedFile)) {
            // Allow access to the installation wizard and API health check
            if ($request->is('install*') || $request->is('api/*') || $request->is('up')) {
                return $next($request);
            }
            return redirect()->route('install.index');
        }

        // Block access to installation wizard after installation
        if ($request->is('install*')) {
            return redirect('/');
        }

        return $next($request);
    }
}