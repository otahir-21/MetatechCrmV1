<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuditLogViewController extends Controller
{
    /**
     * Show audit logs page (Product Owner only).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();

        // Only Product Owner can view audit logs
        if (!$user || !$user->isProductOwner()) {
            abort(403, 'Only Product Owner can view audit logs.');
        }

        // Get or generate API token for API calls
        $apiToken = session('api_token');
        if (!$apiToken) {
            // Generate new JWT token if not in session
            $apiToken = JWTAuth::fromUser($user);
            session(['api_token' => $apiToken]);
        }

        return view('audit-logs.index', ['api_token' => $apiToken]);
    }
}
