<?php

namespace App\Services;

use App\Models\DocumentShare;
use App\Models\ProjectShare;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Support\Facades\DB;

class SharingService
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Share a document with a user.
     */
    public function shareDocument(
        string $documentType,
        int $documentId,
        int $sharedWithUserId,
        User $sharedBy,
        string $permission = 'view',
        ?string $expiresAt = null
    ): DocumentShare {
        $share = DocumentShare::create([
            'document_type' => $documentType,
            'document_id' => $documentId,
            'shared_with_user_id' => $sharedWithUserId,
            'shared_by_user_id' => $sharedBy->id,
            'permission' => $permission,
            'expires_at' => $expiresAt,
        ]);

        // Log the sharing action
        $this->auditLogService->logInvitation(
            'document_shared',
            $sharedBy->id,
            $sharedWithUserId,
            request()->ip(),
            request()->userAgent(),
            [
                'document_type' => $documentType,
                'document_id' => $documentId,
                'permission' => $permission,
                'share_id' => $share->id,
            ]
        );

        return $share;
    }

    /**
     * Share a project resource with a user.
     */
    public function shareProjectResource(
        int $projectId,
        string $resourceType,
        int $resourceId,
        int $sharedWithUserId,
        User $sharedBy,
        string $permission = 'view',
        ?string $notes = null,
        ?string $expiresAt = null
    ): ProjectShare {
        $share = ProjectShare::create([
            'project_id' => $projectId,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'shared_with_user_id' => $sharedWithUserId,
            'shared_by_user_id' => $sharedBy->id,
            'permission' => $permission,
            'notes' => $notes,
            'expires_at' => $expiresAt,
        ]);

        // Log the sharing action
        $this->auditLogService->logInvitation(
            'resource_shared',
            $sharedBy->id,
            $sharedWithUserId,
            request()->ip(),
            request()->userAgent(),
            [
                'project_id' => $projectId,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'permission' => $permission,
                'share_id' => $share->id,
            ]
        );

        return $share;
    }

    /**
     * Revoke document access from a user.
     */
    public function revokeDocumentAccess(
        string $documentType,
        int $documentId,
        int $userId,
        User $revokedBy
    ): bool {
        $share = DocumentShare::forDocument($documentType, $documentId)
            ->forUser($userId)
            ->first();

        if (!$share) {
            throw new \Exception('Share not found', 404);
        }

        $deleted = $share->delete();

        if ($deleted) {
            // Log the revocation
            $this->auditLogService->logInvitation(
                'document_access_revoked',
                $revokedBy->id,
                $userId,
                request()->ip(),
                request()->userAgent(),
                [
                    'document_type' => $documentType,
                    'document_id' => $documentId,
                    'share_id' => $share->id,
                ]
            );
        }

        return $deleted;
    }

    /**
     * Revoke project resource access from a user.
     */
    public function revokeProjectResourceAccess(
        int $projectId,
        string $resourceType,
        int $resourceId,
        int $userId,
        User $revokedBy
    ): bool {
        $share = ProjectShare::forProject($projectId)
            ->forResource($resourceType, $resourceId)
            ->forUser($userId)
            ->first();

        if (!$share) {
            throw new \Exception('Share not found', 404);
        }

        $deleted = $share->delete();

        if ($deleted) {
            // Log the revocation
            $this->auditLogService->logInvitation(
                'resource_access_revoked',
                $revokedBy->id,
                $userId,
                request()->ip(),
                request()->userAgent(),
                [
                    'project_id' => $projectId,
                    'resource_type' => $resourceType,
                    'resource_id' => $resourceId,
                    'share_id' => $share->id,
                ]
            );
        }

        return $deleted;
    }

    /**
     * Get users who have access to a document.
     */
    public function getDocumentShares(string $documentType, int $documentId)
    {
        return DocumentShare::with(['sharedWithUser', 'sharedByUser'])
            ->forDocument($documentType, $documentId)
            ->active()
            ->get();
    }

    /**
     * Get users who have access to a project resource.
     */
    public function getProjectResourceShares(int $projectId, string $resourceType, int $resourceId)
    {
        return ProjectShare::with(['sharedWithUser', 'sharedByUser'])
            ->forProject($projectId)
            ->forResource($resourceType, $resourceId)
            ->active()
            ->get();
    }

    /**
     * Check if a user has access to a document.
     */
    public function hasDocumentAccess(string $documentType, int $documentId, User $user): bool
    {
        // Internal employees have access to everything
        if ($user->is_metatech_employee) {
            return true;
        }

        // Check if explicitly shared
        return DocumentShare::forDocument($documentType, $documentId)
            ->forUser($user->id)
            ->active()
            ->exists();
    }

    /**
     * Check if a user has access to a project resource.
     */
    public function hasProjectResourceAccess(int $projectId, string $resourceType, int $resourceId, User $user): bool
    {
        // Internal employees have access to everything
        if ($user->is_metatech_employee) {
            return true;
        }

        // Check if resource is internal-only
        if ($resourceType === 'task') {
            $task = Task::find($resourceId);
            if ($task && $task->is_internal_only) {
                return false;
            }
        } elseif ($resourceType === 'comment') {
            $comment = TaskComment::find($resourceId);
            if ($comment && $comment->is_internal_only) {
                return false;
            }
        }

        // Check if explicitly shared
        return ProjectShare::forProject($projectId)
            ->forResource($resourceType, $resourceId)
            ->forUser($user->id)
            ->active()
            ->exists();
    }

    /**
     * Toggle internal-only status of a task.
     */
    public function toggleTaskInternalStatus(Task $task, User $user): Task
    {
        // Only internal employees can toggle
        if (!$user->is_metatech_employee) {
            throw new \Exception('Only internal employees can mark tasks as internal', 403);
        }

        $task->is_internal_only = !$task->is_internal_only;
        $task->save();

        // Log the action
        $this->auditLogService->logInvitation(
            'task_internal_status_changed',
            $user->id,
            null,
            request()->ip(),
            request()->userAgent(),
            [
                'task_id' => $task->id,
                'is_internal_only' => $task->is_internal_only,
            ]
        );

        return $task;
    }

    /**
     * Bulk share a resource with multiple users.
     */
    public function bulkShareProjectResource(
        int $projectId,
        string $resourceType,
        int $resourceId,
        array $userIds,
        User $sharedBy,
        string $permission = 'view'
    ): int {
        $count = 0;

        foreach ($userIds as $userId) {
            try {
                $this->shareProjectResource(
                    $projectId,
                    $resourceType,
                    $resourceId,
                    $userId,
                    $sharedBy,
                    $permission
                );
                $count++;
            } catch (\Exception $e) {
                // Log error but continue with other users
                \Log::warning("Failed to share with user {$userId}: " . $e->getMessage());
            }
        }

        return $count;
    }
}
