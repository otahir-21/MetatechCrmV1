<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ProjectAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectAccessController extends Controller
{
    protected ProjectAccessService $projectAccessService;

    public function __construct(ProjectAccessService $projectAccessService)
    {
        $this->projectAccessService = $projectAccessService;
    }

    /**
     * Grant project access to a user.
     *
     * @param Request $request
     * @param int $id Project ID
     * @return JsonResponse
     */
    public function grant(Request $request, int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error_code' => 'UNAUTHENTICATED',
                ], 401);
            }

            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'access_level' => 'required|string|in:viewer,editor,admin',
            ]);

            $this->projectAccessService->grantProjectAccess(
                $id,
                $request->input('user_id'),
                $request->input('access_level'),
                $user
            );

            return response()->json([
                'message' => 'Project access granted successfully',
            ], 200);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                403 => 403,
                404 => 404,
                default => 500,
            };

            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => match ($statusCode) {
                    403 => 'INSUFFICIENT_PERMISSIONS',
                    404 => 'NOT_FOUND',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }

    /**
     * Revoke project access from a user.
     *
     * @param int $id Project ID
     * @param int $userId
     * @return JsonResponse
     */
    public function revoke(int $id, int $userId): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error_code' => 'UNAUTHENTICATED',
                ], 401);
            }

            $this->projectAccessService->revokeProjectAccess($id, $userId, $user);

            return response()->json([
                'message' => 'Project access revoked successfully',
            ], 200);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                403 => 403,
                404 => 404,
                default => 500,
            };

            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => match ($statusCode) {
                    403 => 'INSUFFICIENT_PERMISSIONS',
                    404 => 'NOT_FOUND',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }
}
