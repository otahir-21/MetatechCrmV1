<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CompanyContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected CompanyContextService $companyContextService;

    public function __construct(CompanyContextService $companyContextService)
    {
        $this->companyContextService = $companyContextService;
    }
    /**
     * Login and get JWT token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');
        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
                'error_code' => 'INVALID_CREDENTIALS',
            ], 401);
        }

        // Verify user can access current subdomain
        if (!$this->companyContextService->verifyUserAccess($user, $request)) {
            $allowedUrl = $this->companyContextService->getAllowedLoginUrl($user, $request);
            return response()->json([
                'message' => 'You cannot login here. Please use: ' . $allowedUrl,
                'error_code' => 'UNAUTHORIZED_SUBDOMAIN',
                'allowed_url' => $allowedUrl,
            ], 403);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
                'subdomain' => $user->subdomain,
                'company_name' => $user->company_name,
            ],
        ], 200);
    }
}
