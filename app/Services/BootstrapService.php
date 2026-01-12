<?php

namespace App\Services;

use App\Models\BootstrapAuditLog;
use App\Models\BootstrapState;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class BootstrapService
{
    /**
     * Get current bootstrap status.
     *
     * @return array
     */
    public function getStatus(): array
    {
        $bootstrap = BootstrapState::current();
        $superAdmin = $bootstrap->superAdmin;

        $status = $bootstrap->status;
        $superAdminExists = $superAdmin !== null;
        $superAdminEmail = $superAdmin?->email ?? $bootstrap->super_admin_email;

        return [
            'status' => $status,
            'super_admin_exists' => $superAdminExists,
            'super_admin_email' => $superAdminEmail,
            'created_at' => $superAdmin?->created_at?->toIso8601String(),
            'confirmed_at' => $bootstrap->confirmed_at?->toIso8601String(),
            'can_create' => $status === 'BOOTSTRAP_PENDING',
            'can_confirm' => $status === 'BOOTSTRAP_CONFIRMED',
        ];
    }

    /**
     * Create first Super Admin user.
     *
     * @param array $data
     * @param string $ipAddress
     * @return array
     * @throws \Exception
     */
    public function createSuperAdmin(array $data, string $ipAddress): array
    {
        return DB::transaction(function () use ($data, $ipAddress) {
            // Check if bootstrap is already completed (with lock for race condition prevention)
            $bootstrap = BootstrapState::lockForUpdate()->first();
            if (!$bootstrap) {
                $bootstrap = BootstrapState::current();
            }

            if ($bootstrap->status === 'ACTIVE') {
                $this->logAudit('create', 'failure', $ipAddress, null, $data['email'], $data, 'Bootstrap already completed');
                throw new \Exception('Bootstrap already completed', 403);
            }

        // Check if Super Admin already exists
        $existingSuperAdmin = User::where('role', 'super_admin')->first();
        if ($existingSuperAdmin) {
            if ($existingSuperAdmin->email === strtolower($data['email'])) {
                $this->logAudit('create', 'failure', $ipAddress, null, $data['email'], $data, 'Super Admin already exists');
                throw new \Exception('Super Admin already exists', 409);
            } else {
                $this->logAudit('create', 'failure', $ipAddress, null, $data['email'], $data, 'Super Admin exists with different email');
                throw new \Exception('Bootstrap already completed', 403);
            }
        }

            // Check if user with email already exists
            $existingUser = User::where('email', strtolower($data['email']))->first();
            if ($existingUser) {
                $this->logAudit('create', 'failure', $ipAddress, null, $data['email'], $data, 'Email already taken');
                throw new \Exception('Email already taken', 400);
            }

            // Create Super Admin user
            $user = User::create([
                'email' => strtolower(trim($data['email'])),
                'password' => Hash::make($data['password']),
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'name' => trim($data['first_name']) . ' ' . trim($data['last_name']),
                'role' => 'super_admin',
                'email_verified_at' => null,
            ]);

            // Update bootstrap state
            $bootstrap->update([
                'status' => 'BOOTSTRAP_CONFIRMED',
                'super_admin_email' => $user->email,
                'super_admin_id' => $user->id,
            ]);

            $this->logAudit('create', 'success', $ipAddress, $user->id, $user->email, [
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ]);

            return [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'role' => $user->role,
                    'email_verified_at' => null,
                    'created_at' => $user->created_at->toIso8601String(),
                ],
                'status' => 'BOOTSTRAP_CONFIRMED',
                'requires_confirmation' => true,
                'next_step' => 'Confirm bootstrap completion using /api/v1/bootstrap/confirm',
            ];
        });
    }

    /**
     * Confirm bootstrap completion.
     *
     * @param User $user
     * @param string $ipAddress
     * @return array
     * @throws \Exception
     */
    public function confirmBootstrap(User $user, string $ipAddress): array
    {
        if (!$user->isSuperAdmin()) {
            $this->logAudit('confirm', 'failure', $ipAddress, $user->id, $user->email, [], 'Only Super Admin can confirm bootstrap');
            throw new \Exception('Only Super Admin can confirm bootstrap', 403);
        }

        return DB::transaction(function () use ($user, $ipAddress) {
            // Lock bootstrap state for update (prevent race conditions)
            $bootstrap = BootstrapState::lockForUpdate()->first();
            if (!$bootstrap) {
                $bootstrap = BootstrapState::current();
            }

            if ($bootstrap->status === 'ACTIVE') {
                $this->logAudit('confirm', 'failure', $ipAddress, $user->id, $user->email, [], 'Bootstrap already confirmed');
                throw new \Exception('Bootstrap already confirmed', 403);
            }

            if ($bootstrap->status === 'BOOTSTRAP_PENDING') {
                $this->logAudit('confirm', 'failure', $ipAddress, $user->id, $user->email, [], 'Bootstrap not ready for confirmation');
                throw new \Exception('Bootstrap not ready for confirmation. Super Admin must be created first.', 403);
            }

            $bootstrap->update([
                'status' => 'ACTIVE',
                'confirmed_at' => now(),
            ]);

            $this->logAudit('confirm', 'success', $ipAddress, $user->id, $user->email, []);

            return [
                'status' => 'ACTIVE',
                'confirmed_at' => $bootstrap->fresh()->confirmed_at->toIso8601String(),
                'system_ready' => true,
            ];
        });
    }

    /**
     * Get bootstrap audit logs.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAuditLogs(array $filters = [], int $perPage = 20)
    {
        $query = BootstrapAuditLog::query()->orderBy('created_at', 'desc');

        if (isset($filters['action']) && $filters['action']) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['result']) && $filters['result']) {
            $query->where('result', $filters['result']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Log audit entry.
     *
     * @param string $action
     * @param string $result
     * @param string $ipAddress
     * @param int|null $userId
     * @param string|null $email
     * @param array $requestPayload
     * @param string|null $errorMessage
     * @return void
     */
    public function logAudit(
        string $action,
        string $result,
        string $ipAddress,
        ?int $userId = null,
        ?string $email = null,
        array $requestPayload = [],
        ?string $errorMessage = null
    ): void {
        // Remove password from payload
        $sanitizedPayload = $requestPayload;
        unset($sanitizedPayload['password'], $sanitizedPayload['password_confirmation']);

        BootstrapAuditLog::create([
            'action' => $action,
            'result' => $result,
            'ip_address' => $ipAddress,
            'user_id' => $userId,
            'email' => $email,
            'request_payload' => $sanitizedPayload,
            'error_message' => $errorMessage,
            'created_at' => now(),
        ]);
    }
}

