<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class InternalDashboardController extends Controller
{
    /**
     * Show internal dashboard (for Metatech employees).
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        
        // Verify user is a Metatech employee or Product Owner
        if (!$user || (!$user->is_metatech_employee && !$user->isProductOwner())) {
            abort(403, 'Access denied. This dashboard is only for Metatech employees.');
        }

        // Generate JWT token for API calls if user is authenticated
        $token = null;
        if ($user) {
            $token = JWTAuth::fromUser($user);
        }

        return view('internal.dashboard', ['api_token' => $token]);
    }
}
