<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CompanyContextService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class WebLoginController extends Controller
{
    protected CompanyContextService $companyContextService;
    protected AuditLogService $auditLogService;

    public function __construct(CompanyContextService $companyContextService, AuditLogService $auditLogService)
    {
        $this->companyContextService = $companyContextService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * Handle a login request for web routes (session-based).
     * Routes to appropriate dashboard based on user type.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Log failed login attempt
            $this->auditLogService->logLogin(
                'login_failed',
                $request->ip(),
                $request->userAgent(),
                null,
                [
                    'email' => $request->email,
                    'reason' => 'invalid_credentials',
                ]
            );

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Handle subdomain parameter (for company login on main domain)
        // Can come from query string or form input
        $subdomainParam = $request->input('subdomain') ?? $request->query('subdomain');
        if ($subdomainParam && $user->isCompanySuperAdmin()) {
            // Verify subdomain parameter matches user's subdomain
            if ($user->subdomain !== $subdomainParam) {
                $this->auditLogService->logLogin(
                    'login_failed',
                    $request->ip(),
                    $request->userAgent(),
                    $user->id,
                    [
                        'email' => $request->email,
                        'reason' => 'subdomain_mismatch',
                        'provided_subdomain' => $subdomainParam,
                        'user_subdomain' => $user->subdomain,
                    ]
                );

                throw ValidationException::withMessages([
                    'email' => ['Invalid subdomain for this account.'],
                ]);
            }
            // Store subdomain in session for redirect
            $request->session()->put('company_subdomain', $subdomainParam);
        }
        
        // Verify user can access current subdomain (if not using subdomain parameter)
        if (!$subdomainParam && !$this->companyContextService->verifyUserAccess($user, $request)) {
            // Log failed login attempt (wrong subdomain)
            $this->auditLogService->logLogin(
                'login_failed',
                $request->ip(),
                $request->userAgent(),
                $user->id,
                [
                    'email' => $request->email,
                    'reason' => 'subdomain_access_denied',
                ]
            );

            $allowedUrl = $this->companyContextService->getAllowedLoginUrl($user, $request);
            throw ValidationException::withMessages([
                'email' => ['You cannot login here. Please use: ' . $allowedUrl],
            ]);
        }

        // Log the user in using session-based authentication
        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Also generate JWT token for API calls
        $token = JWTAuth::fromUser($user);
        $request->session()->put('api_token', $token);

        // Log successful login
        $this->auditLogService->logLogin(
            'login_success',
            $request->ip(),
            $request->userAgent(),
            $user->id,
            [
                'email' => $user->email,
                'role' => $user->role,
                'is_metatech_employee' => $user->is_metatech_employee,
            ]
        );

        // Redirect based on user type
        $host = $request->getHost();
        
        // If Company Super Admin, redirect to company dashboard
        if ($user->isCompanySuperAdmin()) {
            return redirect()->intended('/company-dashboard')->with('api_token', $token);
        }
        
        // If internal employee (not Product Owner), redirect to internal dashboard
        if ($user->is_metatech_employee && !$user->isProductOwner()) {
            // Redirect to internal dashboard if on crm subdomain or plain localhost
            if ($host === 'crm.metatech.ae' || $host === 'crm.localhost' || 
                ($host === 'localhost' && strpos($host, 'admincrm.') === false)) {
                return redirect()->intended('/internal/dashboard')->with('api_token', $token);
            }
        }
        
        // Product Owner or default - redirect to product owner dashboard
        return redirect()->intended('/dashboard')->with('api_token', $token);
    }

    /**
     * Log the user out of the application.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
