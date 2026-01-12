<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class AuditLogService
{
    /**
     * Log a login event.
     *
     * @param string $action 'login_success' or 'login_failed'
     * @param string $ipAddress
     * @param string|null $userAgent
     * @param int|null $userId
     * @param array $details Additional details (email, reason, etc.)
     * @return void
     */
    public function logLogin(string $action, string $ipAddress, ?string $userAgent = null, ?int $userId = null, array $details = []): void
    {
        AuditLog::create([
            'event_type' => 'login',
            'action' => $action,
            'user_id' => $userId,
            'target_user_id' => null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'details' => $details,
            'created_at' => now(),
        ]);
    }

    /**
     * Log an invitation event.
     *
     * @param string $action 'invitation_sent', 'invitation_accepted', 'invitation_cancelled'
     * @param int|null $userId Who performed the action
     * @param int|null $targetUserId Target user (for accepted invitations)
     * @param string $ipAddress
     * @param string|null $userAgent
     * @param array $details Additional details (invitee_email, role, department, invitation_id, etc.)
     * @return void
     */
    public function logInvitation(string $action, ?int $userId, ?int $targetUserId, string $ipAddress, ?string $userAgent = null, array $details = []): void
    {
        AuditLog::create([
            'event_type' => 'invitation',
            'action' => $action,
            'user_id' => $userId,
            'target_user_id' => $targetUserId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'details' => $details,
            'created_at' => now(),
        ]);
    }

    /**
     * Log a role change event.
     *
     * @param int $userId Who made the change
     * @param int $targetUserId Whose role was changed
     * @param string $oldRole
     * @param string $newRole
     * @param string $ipAddress
     * @param string|null $userAgent
     * @param array $additionalDetails Additional details
     * @return void
     */
    public function logRoleChange(int $userId, int $targetUserId, string $oldRole, string $newRole, string $ipAddress, ?string $userAgent = null, array $additionalDetails = []): void
    {
        AuditLog::create([
            'event_type' => 'role_change',
            'action' => 'role_updated',
            'user_id' => $userId,
            'target_user_id' => $targetUserId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'details' => array_merge([
                'old_role' => $oldRole,
                'new_role' => $newRole,
            ], $additionalDetails),
            'created_at' => now(),
        ]);
    }

    /**
     * Get audit logs with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAuditLogs(array $filters = [], int $perPage = 20)
    {
        $query = AuditLog::with(['user', 'targetUser'])
            ->orderBy('created_at', 'desc');

        // Filter by event type
        if (isset($filters['event_type']) && $filters['event_type']) {
            $query->where('event_type', $filters['event_type']);
        }

        // Filter by action
        if (isset($filters['action']) && $filters['action']) {
            $query->where('action', $filters['action']);
        }

        // Filter by user (who performed the action)
        if (isset($filters['user_id']) && $filters['user_id']) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by target user
        if (isset($filters['target_user_id']) && $filters['target_user_id']) {
            $query->where('target_user_id', $filters['target_user_id']);
        }

        // Filter by date range
        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }
}

