<?php

namespace App\Services;

use App\Models\CompanyOwnerInvitation;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyOwnerInvitationService
{
    /**
     * Invitation expiration time in days (default: 7 days).
     */
    protected int $invitationExpiresInDays = 7;

    /**
     * Create a new company owner invitation.
     *
     * @param array $data
     * @param User $inviter (Product Owner)
     * @param string $ipAddress
     * @return CompanyOwnerInvitation
     * @throws \Exception
     */
    public function createInvitation(array $data, User $inviter, string $ipAddress): CompanyOwnerInvitation
    {
        if (!$inviter->isProductOwner()) {
            throw new \Exception('Only Product Owner can invite Company Owners', 403);
        }

        return DB::transaction(function () use ($data, $inviter, $ipAddress) {
            // Check if email already exists as a user
            $existingUser = User::where('email', strtolower($data['email']))->first();
            if ($existingUser) {
                throw new \Exception('A user with this email already exists.', 409);
            }

            // Check if a pending invitation already exists for this email
            $existingInvitation = CompanyOwnerInvitation::where('email', strtolower($data['email']))
                                                        ->where('accepted', false)
                                                        ->where('expires_at', '>', now())
                                                        ->first();
            if ($existingInvitation) {
                throw new \Exception('A pending invitation already exists for this email.', 409);
            }

            $plainToken = Str::random(64);
            $hashedToken = hash('sha256', $plainToken);

            $invitation = CompanyOwnerInvitation::create([
                'email' => strtolower(trim($data['email'])),
                'token' => $hashedToken,
                'company_name' => trim($data['company_name']),
                'subdomain' => strtolower(trim($data['subdomain'])),
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'invited_by' => $inviter->id,
                'expires_at' => now()->addDays($this->invitationExpiresInDays),
                'ip_address' => $ipAddress,
            ]);

            // Attach plain token for email (not stored in DB)
            $invitation->plain_token = $plainToken;

            return $invitation;
        });
    }

    /**
     * Verify an company owner invitation token.
     *
     * @param string $email
     * @param string $token
     * @return CompanyOwnerInvitation|null
     */
    public function verifyInvitation(string $email, string $token): ?CompanyOwnerInvitation
    {
        $invitation = CompanyOwnerInvitation::where('email', strtolower(trim($email)))->first();

        if (!$invitation) {
            return null;
        }

        // Verify token hash matches (using SHA256)
        $hashedToken = hash('sha256', $token);
        if ($hashedToken !== $invitation->token) {
            return null;
        }

        // Check if invitation is used or expired
        if (!$invitation->isValid()) {
            return null;
        }

        return $invitation;
    }

    /**
     * Accept an company owner invitation and create a user account.
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
                throw new \Exception('Invalid or expired invitation link.', 400);
            }

            // Check if user already exists (race condition protection)
            $existingUser = User::where('email', $invitation->email)->first();
            if ($existingUser) {
                throw new \Exception('A user with this email already exists.', 400);
            }

            // Get or create company record
            $company = Company::where('subdomain', $invitation->subdomain)->first();
            if (!$company) {
                // Create company record if it doesn't exist
                $company = Company::create([
                    'company_name' => $invitation->company_name,
                    'subdomain' => $invitation->subdomain,
                    'company_super_admin_id' => null, // Will be set after user creation
                    'status' => 'active',
                ]);
            }

            // Create user account
            $user = User::create([
                'email' => $invitation->email,
                'password' => Hash::make($data['password']),
                'first_name' => $data['first_name'] ?? $invitation->first_name ?? '',
                'last_name' => $data['last_name'] ?? $invitation->last_name ?? '',
                'name' => ($data['first_name'] ?? $invitation->first_name ?? '') . ' ' . ($data['last_name'] ?? $invitation->last_name ?? ''),
                'role' => 'super_admin',
                'is_metatech_employee' => false,
                'company_name' => $invitation->company_name,
                'subdomain' => $invitation->subdomain,
                'status' => 'active',
                'email_verified_at' => now(), // Mark as verified since they accepted invitation
            ]);

            // Update company record with super admin ID
            $company->update([
                'company_super_admin_id' => $user->id,
            ]);

            // Mark invitation as accepted
            $invitation->markAsAccepted();

            // Log invitation accepted (audit log service will be injected via app() helper)
            $auditLogService = app(\App\Services\AuditLogService::class);
            $auditLogService->logInvitation(
                'company_owner_invitation_accepted',
                $invitation->invited_by,
                $user->id,
                request()->ip(),
                request()->userAgent(),
                [
                    'invitee_email' => $invitation->email,
                    'company_name' => $invitation->company_name,
                    'subdomain' => $invitation->subdomain,
                    'invitation_id' => $invitation->id,
                    'invitation_type' => 'company_owner',
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
     * @param string $subdomain
     * @return string
     */
    public function getInvitationUrl(string $email, string $plainToken, string $subdomain): string
    {
        $host = request()->getHost();
        $scheme = request()->getScheme();
        $port = request()->getPort();
        
        // Determine the correct host for company subdomain
        if (strpos($host, 'localhost') !== false) {
            // For localhost, use {subdomain}.localhost with port 8000
            $host = $subdomain . '.localhost';
            $port = 8000;
        } else {
            // For production: Use admincrm.metatech.ae (main domain with SSL) instead of company subdomain
            // Since wildcard SSL is not available on shared hosting, we use the main domain with subdomain parameter
            $host = 'admincrm.metatech.ae';
            $port = null; // No port needed for production domains
        }
        
        // Build URL with port only if needed
        $url = $scheme . '://' . $host;
        if ($port && $port != 80 && $port != 443) {
            $url .= ':' . $port;
        }
        
        // Add subdomain as parameter since we're using main domain
        $url .= '/company-invite/accept?email=' . urlencode($email) . '&token=' . urlencode($plainToken) . '&subdomain=' . urlencode($subdomain);
        
        return $url;
    }

    /**
     * Get login URL for company portal.
     *
     * @param string $subdomain
     * @return string
     */
    public function getLoginUrl(string $subdomain): string
    {
        $host = request()->getHost();
        $scheme = request()->getScheme();
        $port = request()->getPort();
        
        // Determine the correct host for company subdomain
        if (strpos($host, 'localhost') !== false) {
            // For localhost, use {subdomain}.localhost with port 8000
            $host = $subdomain . '.localhost';
            $port = 8000;
            $scheme = 'http';
        } else {
            // For production: Try subdomain format first (once Hostinger configures it)
            // Fallback to main domain with parameter if subdomains don't work yet
            // TODO: Once Hostinger enables wildcard subdomain routing, this will use subdomain format automatically
            $host = $subdomain . '.crm.metatech.ae';
            $scheme = 'http'; // HTTP until wildcard SSL is available
            $port = null;
        }
        
        // Build URL with port only if needed
        $url = $scheme . '://' . $host;
        if ($port && $port != 80 && $port != 443) {
            $url .= ':' . $port;
        }
        
        $url .= '/login';
        
        return $url;
    }

    /**
     * Get all company owner invitations (pending and accepted).
     * For Product Owner only.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    public function getAllInvitations(User $user)
    {
        if (!$user->isProductOwner()) {
            throw new \Exception('Only Product Owner can view company owner invitations', 403);
        }

        return CompanyOwnerInvitation::with('inviter')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get pending company owner invitations (not accepted and not expired).
     * For Product Owner only.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    public function getPendingInvitations(User $user)
    {
        if (!$user->isProductOwner()) {
            throw new \Exception('Only Product Owner can view pending invitations', 403);
        }

        return CompanyOwnerInvitation::where('accepted', false)
            ->where('expires_at', '>', now())
            ->with('inviter')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Cancel a company owner invitation.
     *
     * @param int $invitationId
     * @param User $user
     * @return void
     * @throws \Exception
     */
    public function cancelInvitation(int $invitationId, User $user): void
    {
        if (!$user->isProductOwner()) {
            throw new \Exception('Only Product Owner can cancel invitations', 403);
        }

        $invitation = CompanyOwnerInvitation::findOrFail($invitationId);

        if ($invitation->isAccepted()) {
            throw new \Exception('Cannot cancel an already accepted invitation.', 400);
        }

        $invitation->delete();

        // Log invitation cancelled
        $auditLogService = app(\App\Services\AuditLogService::class);
        $auditLogService->logInvitation(
            'company_owner_invitation_cancelled',
            $user->id,
            null,
            request()->ip(),
            request()->userAgent(),
            [
                'invited_email' => $invitation->email,
                'company_name' => $invitation->company_name,
                'subdomain' => $invitation->subdomain,
                'invitation_id' => $invitation->id,
                'invitation_type' => 'company_owner',
            ]
        );
    }
}

