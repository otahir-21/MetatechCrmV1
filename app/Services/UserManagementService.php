<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    /**
     * Block a user.
     *
     * @param int $userId
     * @param User $blockedBy
     * @param string|null $reason
     * @return bool
     * @throws \Exception
     */
    public function blockUser(int $userId, User $blockedBy, ?string $reason = null): bool
    {
        $user = User::findOrFail($userId);
        
        // Cannot block Product Owner
        if ($user->isProductOwner()) {
            throw new \Exception('Cannot block Product Owner', 403);
        }
        
        // Cannot block yourself
        if ($user->id === $blockedBy->id) {
            throw new \Exception('Cannot block yourself', 400);
        }
        
        $user->status = 'blocked';
        $user->status_reason = $reason;
        $user->blocked_at = now();
        $user->blocked_by = $blockedBy->id;
        $user->save();
        
        return true;
    }
    
    /**
     * Unblock a user.
     *
     * @param int $userId
     * @return bool
     */
    public function unblockUser(int $userId): bool
    {
        $user = User::findOrFail($userId);
        
        $user->status = 'active';
        $user->status_reason = null;
        $user->blocked_at = null;
        $user->blocked_by = null;
        $user->save();
        
        return true;
    }
    
    /**
     * Block a company.
     *
     * @param int $companyId
     * @param User $blockedBy
     * @param string|null $reason
     * @return bool
     * @throws \Exception
     */
    public function blockCompany(int $companyId, User $blockedBy, ?string $reason = null): bool
    {
        // First try to find company by ID
        $company = Company::find($companyId);
        
        // If not found, try to find by user ID (for companies created before Company records were added)
        if (!$company) {
            $companyUser = User::where('id', $companyId)
                ->whereNotNull('company_name')
                ->whereNotNull('subdomain')
                ->where('is_metatech_employee', false)
                ->first();
            
            if ($companyUser) {
                // Find or create Company record
                $company = Company::firstOrCreate(
                    ['subdomain' => $companyUser->subdomain],
                    [
                        'company_name' => $companyUser->company_name,
                        'company_super_admin_id' => $companyUser->id,
                        'status' => 'active',
                    ]
                );
            } else {
                throw new \Exception('Company not found', 404);
            }
        }
        
        $company->status = 'blocked';
        $company->status_reason = $reason;
        $company->blocked_at = now();
        $company->blocked_by = $blockedBy->id;
        $company->save();
        
        // Optionally: Block all users in the company
        // User::where('subdomain', $company->subdomain)->update([
        //     'status' => 'blocked',
        //     'blocked_at' => now(),
        //     'blocked_by' => $blockedBy->id,
        // ]);
        
        return true;
    }
    
    /**
     * Unblock a company.
     *
     * @param int $companyId
     * @return bool
     */
    public function unblockCompany(int $companyId): bool
    {
        // First try to find company by ID
        $company = Company::find($companyId);
        
        // If not found, try to find by user ID (for companies created before Company records were added)
        if (!$company) {
            $companyUser = User::where('id', $companyId)
                ->whereNotNull('company_name')
                ->whereNotNull('subdomain')
                ->where('is_metatech_employee', false)
                ->first();
            
            if ($companyUser) {
                // Find or create Company record
                $company = Company::firstOrCreate(
                    ['subdomain' => $companyUser->subdomain],
                    [
                        'company_name' => $companyUser->company_name,
                        'company_super_admin_id' => $companyUser->id,
                        'status' => 'active',
                    ]
                );
            } else {
                throw new \Exception('Company not found', 404);
            }
        }
        
        $company->status = 'active';
        $company->status_reason = null;
        $company->blocked_at = null;
        $company->blocked_by = null;
        $company->save();
        
        return true;
    }
}

