<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SharingService;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SharingController extends Controller
{
    protected SharingService $sharingService;

    public function __construct(SharingService $sharingService)
    {
        $this->sharingService = $sharingService;
    }

    /**
     * Share a project resource with a user.
     */
    public function shareProjectResource(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'resource_type' => 'required|in:task,comment,file,milestone',
                'resource_id' => 'required|integer',
                'user_id' => 'required|exists:users,id',
                'permission' => 'nullable|in:view,comment,edit',
                'notes' => 'nullable|string',
                'expires_at' => 'nullable|date',
            ]);

            $user = auth()->user();

            // Only admins/managers can share
            if (!$user->is_metatech_employee) {
                return response()->json([
                    'message' => 'Only internal employees can share resources',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $share = $this->sharingService->shareProjectResource(
                $validated['project_id'],
                $validated['resource_type'],
                $validated['resource_id'],
                $validated['user_id'],
                $user,
                $validated['permission'] ?? 'view',
                $validated['notes'] ?? null,
                $validated['expires_at'] ?? null
            );

            return response()->json([
                'message' => 'Resource shared successfully',
                'data' => $share->load(['sharedWithUser', 'sharedByUser']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => 'SHARING_ERROR',
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Revoke access to a project resource.
     */
    public function revokeProjectResourceAccess(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'resource_type' => 'required|in:task,comment,file,milestone',
                'resource_id' => 'required|integer',
                'user_id' => 'required|exists:users,id',
            ]);

            $user = auth()->user();

            // Only admins/managers can revoke
            if (!$user->is_metatech_employee) {
                return response()->json([
                    'message' => 'Only internal employees can revoke access',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $this->sharingService->revokeProjectResourceAccess(
                $validated['project_id'],
                $validated['resource_type'],
                $validated['resource_id'],
                $validated['user_id'],
                $user
            );

            return response()->json([
                'message' => 'Access revoked successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => 'REVOKE_ERROR',
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Get list of users who have access to a resource.
     */
    public function getResourceShares(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'resource_type' => 'required|in:task,comment,file,milestone',
                'resource_id' => 'required|integer',
            ]);

            $user = auth()->user();

            // Only internal employees can view shares
            if (!$user->is_metatech_employee) {
                return response()->json([
                    'message' => 'Only internal employees can view shares',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $shares = $this->sharingService->getProjectResourceShares(
                $validated['project_id'],
                $validated['resource_type'],
                $validated['resource_id']
            );

            return response()->json([
                'data' => $shares,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => 'FETCH_ERROR',
            ], 500);
        }
    }

    /**
     * Toggle internal-only status of a task.
     */
    public function toggleTaskInternal(Request $request, int $taskId): JsonResponse
    {
        try {
            $task = Task::findOrFail($taskId);
            $user = auth()->user();

            $updatedTask = $this->sharingService->toggleTaskInternalStatus($task, $user);

            return response()->json([
                'message' => 'Task internal status updated',
                'data' => [
                    'task_id' => $updatedTask->id,
                    'is_internal_only' => $updatedTask->is_internal_only,
                ],
            ], 200);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 403 ? 403 : 500;
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => $e->getCode() === 403 ? 'INSUFFICIENT_PERMISSIONS' : 'UPDATE_ERROR',
            ], $statusCode);
        }
    }

    /**
     * Bulk share a resource with multiple users.
     */
    public function bulkShareProjectResource(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'resource_type' => 'required|in:task,comment,file,milestone',
                'resource_id' => 'required|integer',
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
                'permission' => 'nullable|in:view,comment,edit',
            ]);

            $user = auth()->user();

            // Only admins/managers can share
            if (!$user->is_metatech_employee) {
                return response()->json([
                    'message' => 'Only internal employees can share resources',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $count = $this->sharingService->bulkShareProjectResource(
                $validated['project_id'],
                $validated['resource_type'],
                $validated['resource_id'],
                $validated['user_ids'],
                $user,
                $validated['permission'] ?? 'view'
            );

            return response()->json([
                'message' => "Resource shared with {$count} user(s) successfully",
                'shared_count' => $count,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => 'BULK_SHARING_ERROR',
            ], 500);
        }
    }
}
