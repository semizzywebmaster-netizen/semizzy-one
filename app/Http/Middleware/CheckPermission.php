<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        if (!$user->hasAnyPermission(...$permissions)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Insufficient permissions.'], 403);
            }
            abort(403, 'Forbidden. You do not have the required permissions.');
        }

        return $next($request);
    }
}