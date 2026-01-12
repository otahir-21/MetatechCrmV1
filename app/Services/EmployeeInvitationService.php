<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmployeeInvitation;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeInvitationService
{
    /**
     * Invitation expiration time in days (default: 7 days).
     */
    protected int $expirationDays = 7;

    /**
     * Create an employee invitation.
     *
     * @param array $data
     * @param User $inviter
     * @param string $ipAddress
     * @return EmployeeInvitation
     * @throws \Exception
     */
    public function createInvitation(array $data, User $inviter, string $ipAddress): EmployeeInvitation
    {
        if (!$inviter->canManageInternalEmployees()) {
            throw new \Exception('Only Product Owner, Internal Super Admin, or Internal Admin can send invitations', 403);
        }

        return DB::transaction(function () use ($data, $inviter, $ipAddress) {
            $email = strtolower(trim($data['email']));

            // Check if user already exists
            $existingUser = User::where('email', $email)->first();
            if ($existingUser) {
                throw new \Exception('A user with this email already exists.', 400);
            }

            // Check if there's a pending invitation for this email
            $existingInvitation = EmployeeInvitation::where('email', $email)
                ->where('accepted', false)
                ->where('expires_at', '>', now())
                ->first();
            
            if ($existingInvitation) {
                throw new \Exception('An active invitation already exists for this email.', 409);
            }

            // Generate secure token
            $token = Str::random(64);
            $expiresAt = now()->addDays($this->expirationDays);

            // Create invitation
            $invitation = EmployeeInvitation::create([
                'email' => $email,
                'token' => hash('sha256', $token), // Hash token for storage
                'invited_by' => $inviter->id,
                'role' => $data['role'] ?? 'user',
                'department' => $data['department'] ?? null,
                'designation' => $data['designation'] ?? null,
                'joined_date' => $data['joined_date'] ?? null,
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'accepted' => false,
                'expires_at' => $expiresAt,
                'ip_address' => $ipAddress,
            ]);

            // Attach plain token for email (not stored in DB)
            $invitation->plain_token = $token;

            return $invitation;
        });
    }

    /**
     * Verify invitation token.
     *
     * @param string $email
     * @param string $token
     * @return EmployeeInvitation|null
     */
    public function verifyInvitation(string $email, string $token): ?EmployeeInvitation
    {
        $invitation = EmployeeInvitation::where('email', strtolower(trim($email)))->first();

        if (!$invitation) {
            return null;
        }

        // Check if invitation is accepted
        if ($invitation->isAccepted()) {
            return null;
        }

        // Check if invitation is expired
        if ($invitation->isExpired()) {
            return null;
        }

        // Verify token hash matches
        $hashedToken = hash('sha256', $token);
        if ($hashedToken !== $invitation->token) {
            return null;
        }

        return $invitation;
    }

    /**
     * Accept invitation and create user account.
     *
     * @param string $email
     * @param string $token
     * @param array $data (password, first_name, last_name)
     * @return User
     * @throws \Exception
     */
    public function acceptInvitation(string $email, string $token, array $data): User
    {
        return DB::transaction(function () use ($email, $token, $data) {
            $invitation = $this->verifyInvitation($email, $token);

            if (!$invitation) {
                throw new \Exception('Invalid or expired invitation token.', 400);
            }

            // Check if user already exists (race condition protection)
            $existingUser = User::where('email', $invitation->email)->first();
            if ($existingUser) {
                throw new \Exception('A user with this email already exists.', 400);
            }

            // Create user account
            $user = User::create([
                'email' => $invitation->email,
                'password' => Hash::make($data['password']),
                'first_name' => $data['first_name'] ?? $invitation->first_name ?? '',
                'last_name' => $data['last_name'] ?? $invitation->last_name ?? '',
                'name' => ($data['first_name'] ?? $invitation->first_name ?? '') . ' ' . ($data['last_name'] ?? $invitation->last_name ?? ''),
                'role' => $invitation->role,
                'is_metatech_employee' => true,
                'company_name' => null,
                'subdomain' => null,
                'department' => $invitation->department,
                'designation' => $invitation->designation,
                'joined_date' => $invitation->joined_date ?? now()->toDateString(),
                'status' => 'active',
                'email_verified_at' => now(), // Mark as verified since they accepted invitation
            ]);

            // Mark invitation as accepted
            $invitation->markAsAccepted();

            // Log invitation accepted (audit log service will be injected via app() helper)
            $auditLogService = app(AuditLogService::class);
            $auditLogService->logInvitation(
                'invitation_accepted',
                $invitation->invited_by,
                $user->id,
                request()->ip(),
                request()->userAgent(),
                [
                    'invitee_email' => $invitation->email,
                    'role' => $invitation->role,
                    'department' => $invitation->department,
                    'invitation_id' => $invitation->id,
                ]
            );

            return $user;
        });
    }

    /**
     * Get invitation URL.
     *
     * @param string $email
     * @param string $plainToken
     * @return string
     */
    public function getInvitationUrl(string $email, string $plainToken): string
    {
        // Always use the crm subdomain for internal employee invitations
        $host = request()->getHost();
        $scheme = request()->getScheme();
        $port = request()->getPort();
        
        // Determine the correct host for crm subdomain
        if (strpos($host, 'localhost') !== false) {
            // For localhost, use crm.localhost with port 8000
            $host = 'crm.localhost';
            $port = 8000; // Always use port 8000 for localhost
        } elseif (strpos($host, 'crm.') !== 0) {
            // For production (metatech.ae), prepend crm. subdomain
            $host = 'crm.' . $host;
            $port = null; // No port needed for production domains
        } else {
            // Already on crm subdomain
            // For localhost, keep port 8000, for production remove port
            if (strpos($host, 'localhost') !== false && !$port) {
                $port = 8000;
            } elseif (strpos($host, 'localhost') === false) {
                $port = null;
            }
        }
        
        // Build URL with port only if needed
        $url = $scheme . '://' . $host;
        if ($port && $port != 80 && $port != 443) {
            $url .= ':' . $port;
        }
        
        $url .= '/employee/invite/accept?email=' . urlencode($email) . '&token=' . urlencode($plainToken);
        
        return $url;
    }

    /**
     * Get all pending invitations.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingInvitations()
    {
        return EmployeeInvitation::where('accepted', false)
            ->where('expires_at', '>', now())
            ->with('inviter')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Cancel an invitation.
     *
     * @param int $invitationId
     * @param User $user
     * @return void
     * @throws \Exception
     */
    public function cancelInvitation(int $invitationId, User $user): void
    {
        if (!$user->canManageInternalEmployees()) {
            throw new \Exception('You do not have permission to cancel invitations.', 403);
        }

        $invitation = EmployeeInvitation::findOrFail($invitationId);

        if ($invitation->isAccepted()) {
            throw new \Exception('Cannot cancel an already accepted invitation.', 400);
        }

        $invitation->delete();
    }
}

