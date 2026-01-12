<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CompanyContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class InternalLoginController extends Controller
{
    protected CompanyContextService $companyContextService;

    public function __construct(CompanyContextService $companyContextService)
    {
        $this->companyContextService = $companyContextService;
    }

    /**
     * Show internal login page (for Metatech employees).
     *
     * @return \Illuminate\View\View
     */
    public function showLogin()
    {
        return view('auth.internal-login');
    }

    /**
     * Handle a login request for internal employees (session-based).
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
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Verify user is a Metatech employee and can access this subdomain
        if (!$user->is_metatech_employee) {
            throw ValidationException::withMessages([
                'email' => ['This login is only for Metatech employees.'],
            ]);
        }

        // Verify user can access current subdomain (crm.metatech.ae)
        if (!$this->companyContextService->verifyUserAccess($user, $request)) {
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

        return redirect()->intended('/internal/dashboard')->with('api_token', $token);
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
