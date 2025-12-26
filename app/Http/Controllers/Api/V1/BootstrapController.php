<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BootstrapAuditRequest;
use App\Http\Requests\BootstrapConfirmRequest;
use App\Http\Requests\BootstrapCreateRequest;
use App\Models\BootstrapState;
use App\Services\BootstrapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class BootstrapController extends Controller
{
    protected BootstrapService $bootstrapService;

    public function __construct(BootstrapService $bootstrapService)
    {
        $this->bootstrapService = $bootstrapService;
    }

    /**
     * Get bootstrap status.
     *
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        try {
            $status = $this->bootstrapService->getStatus();
            return response()->json($status, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal server error',
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Create first Super Admin.
     *
     * @param BootstrapCreateRequest $request
     * @return JsonResponse
     */
    public function create(BootstrapCreateRequest $request): JsonResponse
    {
        $ipAddress = $request->ip();
        $rateLimitKey = 'bootstrap:create:' . $ipAddress;

        // Rate limiting: 5 attempts per hour, 10 per 24 hours
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'message' => 'Too many bootstrap attempts. Please try again later.',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
                'retry_after' => $seconds,
            ], 429)->withHeaders([
                'Retry-After' => $seconds,
            ]);
        }

        // Check 24-hour limit
        $dailyKey = 'bootstrap:create:daily:' . $ipAddress;
        if (RateLimiter::tooManyAttempts($dailyKey, 10)) {
            $seconds = RateLimiter::availableIn($dailyKey);
            return response()->json([
                'message' => 'Too many bootstrap attempts. Please try again later.',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
                'retry_after' => $seconds,
            ], 429)->withHeaders([
                'Retry-After' => $seconds,
            ]);
        }

        RateLimiter::hit($rateLimitKey, 3600); // 1 hour
        RateLimiter::hit($dailyKey, 86400); // 24 hours

        try {
            // Check bootstrap state before processing
            $bootstrap = BootstrapState::current();
            if ($bootstrap->status === 'ACTIVE') {
                return response()->json([
                    'message' => 'Bootstrap already completed',
                    'status' => 'ACTIVE',
                    'error_code' => 'BOOTSTRAP_ALREADY_COMPLETED',
                ], 403);
            }

            $result = $this->bootstrapService->createSuperAdmin($request->validated(), $ipAddress);

            return response()->json([
                'message' => 'Super Admin created successfully',
                ...$result,
            ], 201);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                403 => 403,
                409 => 409,
                400 => 400,
                default => 500,
            };

            $errorCode = match ($statusCode) {
                403 => 'BOOTSTRAP_ALREADY_COMPLETED',
                409 => 'SUPER_ADMIN_EXISTS',
                400 => 'VALIDATION_ERROR',
                default => 'INTERNAL_ERROR',
            };

            $response = [
                'message' => $e->getMessage(),
                'error_code' => $errorCode,
            ];

            // Get bootstrap status for error response if needed
            try {
                $bootstrap = BootstrapState::current();
                if ($bootstrap) {
                    $response['status'] = $bootstrap->status;
                }
            } catch (\Exception $bootstrapError) {
                // Ignore bootstrap state fetch errors in error handler
            }

            return response()->json($response, $statusCode);
        }
    }

    /**
     * Confirm bootstrap completion.
     *
     * @param BootstrapConfirmRequest $request
     * @return JsonResponse
     */
    public function confirm(BootstrapConfirmRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isSuperAdmin()) {
                return response()->json([
                    'message' => 'Only Super Admin can confirm bootstrap',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $rateLimitKey = 'bootstrap:confirm:' . $user->id;
            if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
                $seconds = RateLimiter::availableIn($rateLimitKey);
                return response()->json([
                    'message' => 'Too many confirmation attempts. Please try again later.',
                    'error_code' => 'RATE_LIMIT_EXCEEDED',
                    'retry_after' => $seconds,
                ], 429)->withHeaders([
                    'Retry-After' => $seconds,
                ]);
            }

            RateLimiter::hit($rateLimitKey, 3600); // 1 hour

            $result = $this->bootstrapService->confirmBootstrap($user, $request->ip());

            return response()->json([
                'message' => 'Bootstrap confirmed successfully',
                ...$result,
            ], 200);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                403 => 403,
                default => 500,
            };

            $errorCode = match ($statusCode) {
                403 => $e->getMessage() === 'Bootstrap already confirmed' 
                    ? 'BOOTSTRAP_ALREADY_CONFIRMED' 
                    : ($e->getMessage() === 'Only Super Admin can confirm bootstrap'
                        ? 'INSUFFICIENT_PERMISSIONS'
                        : 'BOOTSTRAP_NOT_READY'),
                default => 'INTERNAL_ERROR',
            };

            $response = [
                'message' => $e->getMessage(),
                'error_code' => $errorCode,
            ];

            $bootstrap = BootstrapState::current();
            if ($bootstrap) {
                $response['status'] = $bootstrap->status;
            }

            return response()->json($response, $statusCode);
        }
    }

    /**
     * Get bootstrap audit logs.
     *
     * @param BootstrapAuditRequest $request
     * @return JsonResponse
     */
    public function audit(BootstrapAuditRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isSuperAdmin()) {
                return response()->json([
                    'message' => 'Only Super Admin can view audit logs',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $perPage = min($request->input('per_page', 20), 100);
            $filters = [
                'action' => $request->input('action'),
                'result' => $request->input('result'),
            ];

            $logs = $this->bootstrapService->getAuditLogs($filters, $perPage);

            return response()->json([
                'data' => $logs->items(),
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                    'last_page' => $logs->lastPage(),
                    'from' => $logs->firstItem(),
                    'to' => $logs->lastItem(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal server error',
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }
}
