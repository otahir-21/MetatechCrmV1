<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEmployeeStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Only check for Metatech employees
        if ($user && $user->is_metatech_employee) {
            if ($user->status === 'suspended') {
                // Allow access to logout route
                if ($request->routeIs('logout')) {
                    return $next($request);
                }
                
                return redirect()->route('internal.suspended');
            }

            if ($user->status === 'blocked') {
                // Allow access to logout route
                if ($request->routeIs('logout')) {
                    return $next($request);
                }
                
                auth()->logout();
                return redirect()->route('login')->with('error', 'Your account has been blocked.');
            }
        }

        return $next($request);
    }
}
