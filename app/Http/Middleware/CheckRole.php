<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        if (!$user->hasRole(...$roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Insufficient role.'], 403);
            }
            abort(403, 'Forbidden. You do not have the required role.');
        }

        return $next($request);
    }
}