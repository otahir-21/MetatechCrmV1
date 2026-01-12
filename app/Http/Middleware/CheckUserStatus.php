<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if user is blocked or suspended
            if (in_array($user->status, ['blocked', 'suspended'])) {
                Auth::logout();
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Your account has been ' . $user->status . '. ' . ($user->status_reason ? 'Reason: ' . $user->status_reason : ''),
                        'error_code' => 'ACCOUNT_' . strtoupper($user->status),
                    ], 403);
                }
                
                return redirect('/login')->with('error', 'Your account has been ' . $user->status . '.');
            }
            
            // If user belongs to a company, check company status
            if ($user->subdomain && $user->company_name) {
                try {
                    $company = \App\Models\Company::where('subdomain', $user->subdomain)->first();
                    if ($company && in_array($company->status, ['blocked', 'suspended'])) {
                        Auth::logout();
                        
                        if ($request->expectsJson()) {
                            return response()->json([
                                'message' => 'Your company account has been ' . $company->status . '. ' . ($company->status_reason ? 'Reason: ' . $company->status_reason : ''),
                                'error_code' => 'COMPANY_' . strtoupper($company->status),
                            ], 403);
                        }
                        
                        return redirect('/login')->with('error', 'Your company account has been ' . $company->status . '.');
                    }
                } catch (\Exception $e) {
                    // Company table might not exist yet, skip company check
                }
            }
        }
        
        return $next($request);
    }
}
