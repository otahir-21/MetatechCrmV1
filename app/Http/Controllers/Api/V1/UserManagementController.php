<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    protected UserManagementService $userManagementService;

    public function __construct(UserManagementService $userManagementService)
    {
        $this->userManagementService = $userManagementService;
    }

    /**
     * Block a user.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function blockUser(Request $request, int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isProductOwner()) {
                return response()->json([
                    'message' => 'Only Product Owner can block users',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $reason = $request->input('reason');
            $this->userManagementService->blockUser($id, $user, $reason);

            return response()->json([
                'message' => 'User blocked successfully',
                'data' => ['user_id' => $id],
            ], 200);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                403 => 403,
                400 => 400,
                404 => 404,
                default => 500,
            };

            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => match ($statusCode) {
                    403 => 'FORBIDDEN',
                    400 => 'BAD_REQUEST',
                    404 => 'NOT_FOUND',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }

    /**
     * Unblock a user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function unblockUser(int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isProductOwner()) {
                return response()->json([
                    'message' => 'Only Product Owner can unblock users',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $this->userManagementService->unblockUser($id);

            return response()->json([
                'message' => 'User unblocked successfully',
                'data' => ['user_id' => $id],
            ], 200);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;

            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => $statusCode === 404 ? 'NOT_FOUND' : 'INTERNAL_ERROR',
            ], $statusCode);
        }
    }

    /**
     * Block a company.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function blockCompany(Request $request, int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isProductOwner()) {
                return response()->json([
                    'message' => 'Only Product Owner can block companies',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $reason = $request->input('reason');
            $this->userManagementService->blockCompany($id, $user, $reason);

            return response()->json([
                'message' => 'Company blocked successfully',
                'data' => ['company_id' => $id],
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
                    403 => 'FORBIDDEN',
                    404 => 'NOT_FOUND',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }

    /**
     * Unblock a company.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function unblockCompany(int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isProductOwner()) {
                return response()->json([
                    'message' => 'Only Product Owner can unblock companies',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $this->userManagementService->unblockCompany($id);

            return response()->json([
                'message' => 'Company unblocked successfully',
                'data' => ['company_id' => $id],
            ], 200);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;

            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => $statusCode === 404 ? 'NOT_FOUND' : 'INTERNAL_ERROR',
            ], $statusCode);
        }
    }
}
