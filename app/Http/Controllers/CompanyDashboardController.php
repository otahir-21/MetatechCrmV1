<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyDashboardController extends Controller
{
    /**
     * Show company dashboard for Company Super Admin.
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'Please login to access the dashboard.']);
        }
        
        // Verify user is Company Super Admin
        if (!$user->isCompanySuperAdmin()) {
            // If they're Product Owner, redirect to their dashboard
            if ($user->isProductOwner()) {
                return redirect()->route('dashboard');
            }
            // If they're internal employee, redirect to internal dashboard
            if ($user->is_metatech_employee) {
                return redirect()->route('internal.dashboard');
            }
            abort(403, 'Access denied. Only Company Super Admins can access this page.');
        }

        try {
            // Generate JWT token for API calls
            $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        } catch (\Exception $e) {
            // If JWT fails, still try to show the page (token will be empty)
            $token = '';
        }
        
        return view('company-dashboard.index', ['api_token' => $token]);
    }
}
