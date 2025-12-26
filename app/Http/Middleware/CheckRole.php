<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error_code' => 'UNAUTHENTICATED',
                ], 401);
            }
            return redirect('/login');
        }

        $user = auth()->user();

        // Check if user has one of the required roles (using Spatie Permission)
        if (!$user->hasAnyRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to access this resource',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }
            abort(403, 'You do not have permission to access this resource');
        }

        return $next($request);
    }
}
