<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditAction
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log significant actions (POST, PUT, PATCH, DELETE)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $user = $request->user();

            AuditLog::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? 'Guest',
                'action' => $this->resolveAction($request),
                'target_type' => $request->route('target_type'),
                'target_id' => $request->route('target_id'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'result' => $response->isSuccessful() ? 'success' : 'failure',
                'metadata' => [
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'status_code' => $response->getStatusCode(),
                ],
            ]);
        }

        return $response;
    }

    private function resolveAction(Request $request): string
    {
        $route = $request->route();
        $routeName = $route?->getName() ?? $request->path();

        return str_replace('.', '_', $routeName);
    }
}