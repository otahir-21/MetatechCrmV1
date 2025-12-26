<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use App\Services\CompanyOwnerInvitationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\CompanyOwnerInvitationMail;

class CompanyService
{
    /**
     * Create Company and send invitation to Company Owner.
     *
     * @param array $data
     * @param User $productOwner
     * @param string $ipAddress
     * @return array
     * @throws \Exception
     */
    public function createCompanyAndSendInvitation(array $data, User $productOwner, string $ipAddress): array
    {
        if (!$productOwner->isProductOwner()) {
            throw new \Exception('Only Product Owner can create companies', 403);
        }

        return DB::transaction(function () use ($data, $ipAddress, $productOwner) {
            // Check if company name already exists
            $existingCompany = Company::where('company_name', $data['company_name'])->first();
            if ($existingCompany) {
                throw new \Exception('Company name already exists', 409);
            }

            // Check if subdomain already exists
            $subdomain = strtolower(trim($data['subdomain']));
            $existingSubdomain = Company::where('subdomain', $subdomain)->first();
            if ($existingSubdomain) {
                throw new \Exception('Subdomain already taken', 409);
            }

            // Validate subdomain format
            if (!preg_match('/^[a-z0-9-]+$/', $subdomain)) {
                throw new \Exception('Subdomain can only contain lowercase letters, numbers, and hyphens', 400);
            }

            // Create Company record first (without super admin ID)
            $company = Company::create([
                'company_name' => trim($data['company_name']),
                'subdomain' => $subdomain,
                'company_super_admin_id' => null, // Will be set when invitation is accepted
                'status' => 'active',
            ]);

            // Create invitation using CompanyOwnerInvitationService
            $invitationService = app(CompanyOwnerInvitationService::class);
            $invitation = $invitationService->createInvitation(
                [
                    'email' => $data['email'],
                    'company_name' => $data['company_name'],
                    'subdomain' => $subdomain,
                    'first_name' => $data['first_name'] ?? null,
                    'last_name' => $data['last_name'] ?? null,
                ],
                $productOwner,
                $ipAddress
            );

            // Send invitation email
            Mail::to($invitation->email)->send(
                new CompanyOwnerInvitationMail($invitation, $invitation->plain_token)
            );

            // Log invitation sent (audit log)
            $auditLogService = app(\App\Services\AuditLogService::class);
            $auditLogService->logInvitation(
                'company_owner_invitation_sent',
                $productOwner->id,
                null, // target_user_id is null until invitation is accepted
                $ipAddress,
                request()->userAgent() ?? null,
                [
                    'invitee_email' => $invitation->email,
                    'company_name' => $invitation->company_name,
                    'subdomain' => $invitation->subdomain,
                    'invitation_id' => $invitation->id,
                    'invitation_type' => 'company_owner',
                ]
            );

            return [
                'company' => [
                    'id' => $company->id,
                    'company_name' => $company->company_name,
                    'subdomain' => $company->subdomain,
                    'status' => $company->status,
                    'created_at' => $company->created_at->toIso8601String(),
                ],
                'invitation' => [
                    'id' => $invitation->id,
                    'email' => $invitation->email,
                    'expires_at' => $invitation->expires_at->toIso8601String(),
                    'created_at' => $invitation->created_at->toIso8601String(),
                ],
            ];
        });
    }

    /**
     * Create Company Super Admin (DEPRECATED - Use createCompanyAndSendInvitation instead).
     * 
     * @deprecated Use createCompanyAndSendInvitation instead
     */
    public function createCompanySuperAdmin(array $data, User $productOwner, string $ipAddress): array
    {
        return $this->createCompanyAndSendInvitation($data, $productOwner, $ipAddress);
    }

    /**
     * Get list of all companies.
     *
     * @return array
     */
    public function getAllCompanies(): array
    {
        $companies = \App\Models\User::where('role', 'super_admin')
            ->whereNotNull('company_name')
            ->whereNotNull('subdomain')
            ->where('is_metatech_employee', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return $companies->map(function ($user) {
            // Get company record
            $company = \App\Models\Company::where('subdomain', $user->subdomain)->first();
            $companyId = $company ? $company->id : null;
            $status = $company ? $company->status : 'active';
            
            return [
                'id' => $user->id,
                'company_id' => $companyId,
                'company_name' => $user->company_name,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'subdomain' => $user->subdomain,
                'created_at' => $user->created_at->toIso8601String(),
                'status' => $status,
            ];
        })->toArray();
    }

    /**
     * Get company details by ID.
     *
     * @param int $companyId
     * @return array
     * @throws \Exception
     */
    public function getCompanyDetails(int $companyId): array
    {
        $company = \App\Models\User::where('id', $companyId)
            ->where('role', 'super_admin')
            ->whereNotNull('company_name')
            ->whereNotNull('subdomain')
            ->where('is_metatech_employee', false)
            ->first();

        if (!$company) {
            throw new \Exception('Company not found', 404);
        }

        // Count admins/users for this company (for now, return 0 as placeholder)
        $adminCount = 0; // Will be implemented later

        // Get company record
        $companyRecord = \App\Models\Company::where('subdomain', $user->subdomain)->first();
        $companyRecordId = $companyRecord ? $companyRecord->id : null;
        $status = $companyRecord ? $companyRecord->status : 'active';

        return [
            'id' => $user->id,
            'company_id' => $companyRecordId,
            'company_name' => $user->company_name,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'subdomain' => $user->subdomain,
            'created_at' => $user->created_at->toIso8601String(),
            'status' => $status,
            'admin_count' => $adminCount,
            'user_count' => 0, // Dummy count
            'subscription_status' => 'active', // Dummy subscription status
        ];
    }

    /**
     * Get companies statistics.
     *
     * @return array
     */
    public function getCompaniesStats(): array
    {
        $totalCompanies = \App\Models\User::where('role', 'super_admin')
            ->whereNotNull('company_name')
            ->whereNotNull('subdomain')
            ->where('is_metatech_employee', false)
            ->count();

        return [
            'total_companies' => $totalCompanies,
            'active_companies' => $totalCompanies, // Dummy for now
            'total_admins' => 0, // Dummy for now
            'total_users' => 0, // Dummy for now
        ];
    }

    /**
     * Update company subdomain.
     *
     * @param int $companyId
     * @param string $subdomain
     * @param User $productOwner
     * @return array
     * @throws \Exception
     */
    public function updateCompanySubdomain(int $companyId, string $subdomain, User $productOwner): array
    {
        if (!$productOwner->isProductOwner()) {
            throw new \Exception('Only Product Owner can update company subdomain', 403);
        }

        return DB::transaction(function () use ($companyId, $subdomain) {
            // Find the company
            $company = User::where('id', $companyId)
                ->where('role', 'super_admin')
                ->whereNotNull('company_name')
                ->where('is_metatech_employee', false)
                ->first();

            if (!$company) {
                throw new \Exception('Company not found', 404);
            }

            // Validate subdomain format
            $subdomain = strtolower(trim($subdomain));
            if (!preg_match('/^[a-z0-9-]+$/', $subdomain)) {
                throw new \Exception('Subdomain can only contain lowercase letters, numbers, and hyphens', 400);
            }

            // Check if subdomain is already taken by another company
            $existingSubdomain = User::where('subdomain', $subdomain)
                ->where('id', '!=', $companyId)
                ->first();

            if ($existingSubdomain) {
                throw new \Exception('Subdomain already taken', 409);
            }

            // Update subdomain
            $company->subdomain = $subdomain;
            $company->save();

            return [
                'id' => $company->id,
                'company_name' => $company->company_name,
                'subdomain' => $company->subdomain,
                'email' => $company->email,
            ];
        });
    }
}

