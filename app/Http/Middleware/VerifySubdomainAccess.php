<?php

namespace App\Http\Middleware;

use App\Services\CompanyContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySubdomainAccess
{
    protected CompanyContextService $companyContextService;

    public function __construct(CompanyContextService $companyContextService)
    {
        $this->companyContextService = $companyContextService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $context = $this->companyContextService->getCurrentContext($request);
        
        // Check if context is valid
        if ($context === null) {
            // Invalid subdomain - company doesn't exist
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Invalid subdomain or company not found',
                    'error_code' => 'INVALID_SUBDOMAIN',
                ], 404);
            }
            
            abort(404, 'Company not found');
        }

        // Verify user can access this subdomain
        if (!$this->companyContextService->verifyUserAccess($user, $request)) {
            $allowedUrl = $this->companyContextService->getAllowedLoginUrl($user, $request);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You are not authorized to access this system. Please login at: ' . $allowedUrl,
                    'error_code' => 'UNAUTHORIZED_SUBDOMAIN',
                    'allowed_url' => $allowedUrl,
                ], 403);
            }
            
            return response()->view('errors.access-denied', [
                'message' => 'You are not authorized to access this system.',
                'allowed_url' => $allowedUrl,
            ], 403);
        }

        return $next($request);
    }
}
