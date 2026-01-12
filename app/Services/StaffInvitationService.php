<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use App\Models\StaffInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffInvitationService
{
    /**
     * Invite staff member to company.
     *
     * @param array $data
     * @param User $invitedBy
     * @return StaffInvitation
     * @throws \Exception
     */
    public function inviteStaff(array $data, User $invitedBy): StaffInvitation
    {
        // Verify inviter is Company Super Admin
        if (!$invitedBy->isCompanySuperAdmin()) {
            throw new \Exception('Only Company Super Admin can invite staff', 403);
        }

        // Get company
        $company = Company::where('subdomain', $invitedBy->subdomain)->first();
        if (!$company) {
            // Try to create company record if it doesn't exist
            $company = Company::create([
                'company_name' => $invitedBy->company_name,
                'subdomain' => $invitedBy->subdomain,
                'company_super_admin_id' => $invitedBy->id,
                'status' => 'active',
            ]);
        }

        // Check if email already exists in company
        $existingUser = User::where('email', strtolower($data['email']))
            ->where('subdomain', $company->subdomain)
            ->where('is_metatech_employee', false)
            ->first();

        if ($existingUser) {
            throw new \Exception('User already exists in this company', 409);
        }

        // Check if there's already a pending invitation for this email
        $existingInvitation = StaffInvitation::where('company_id', $company->id)
            ->where('email', strtolower($data['email']))
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            throw new \Exception('Invitation already sent to this email', 409);
        }

        // Create invitation
        $invitation = StaffInvitation::create([
            'company_id' => $company->id,
            'invited_by' => $invitedBy->id,
            'email' => strtolower(trim($data['email'])),
            'token' => Str::random(64),
            'role' => $data['role'] ?? 'user',
            'status' => 'pending',
            'expires_at' => now()->addDays(7), // Expires in 7 days
        ]);

        // Load relationships for email
        $invitation->load('company', 'invitedBy');

        return $invitation;
    }

    /**
     * Accept invitation and create user.
     *
     * @param string $token
     * @param array $userData
     * @return User
     * @throws \Exception
     */
    public function acceptInvitation(string $token, array $userData): User
    {
        $invitation = StaffInvitation::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        // Check if user already exists
        $existingUser = User::where('email', $invitation->email)->first();
        if ($existingUser) {
            throw new \Exception('User with this email already exists', 409);
        }

        $company = $invitation->company;

        return DB::transaction(function () use ($invitation, $userData, $company) {
            // Create user
            $user = User::create([
                'email' => $invitation->email,
                'password' => Hash::make($userData['password']),
                'first_name' => trim($userData['first_name']),
                'last_name' => trim($userData['last_name']),
                'name' => trim($userData['first_name']) . ' ' . trim($userData['last_name']),
                'role' => $invitation->role,
                'company_name' => $company->company_name,
                'subdomain' => $company->subdomain,
                'is_metatech_employee' => false,
                'status' => 'active',
            ]);

            // Mark invitation as accepted
            $invitation->status = 'accepted';
            $invitation->accepted_at = now();
            $invitation->save();

            return $user;
        });
    }

    /**
     * Get all invitations for a company.
     *
     * @param User $companySuperAdmin
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCompanyInvitations(User $companySuperAdmin)
    {
        if (!$companySuperAdmin->isCompanySuperAdmin()) {
            throw new \Exception('Only Company Super Admin can view invitations', 403);
        }

        $company = Company::where('subdomain', $companySuperAdmin->subdomain)->firstOrFail();

        return StaffInvitation::where('company_id', $company->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Cancel an invitation.
     *
     * @param int $invitationId
     * @param User $cancelledBy
     * @return bool
     * @throws \Exception
     */
    public function cancelInvitation(int $invitationId, User $cancelledBy): bool
    {
        if (!$cancelledBy->isCompanySuperAdmin()) {
            throw new \Exception('Only Company Super Admin can cancel invitations', 403);
        }

        $invitation = StaffInvitation::findOrFail($invitationId);
        $company = Company::where('subdomain', $cancelledBy->subdomain)->firstOrFail();

        if ($invitation->company_id !== $company->id) {
            throw new \Exception('You can only cancel invitations for your own company', 403);
        }

        $invitation->status = 'cancelled';
        $invitation->save();

        return true;
    }
}

