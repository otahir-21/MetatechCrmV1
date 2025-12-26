<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class CompanyContextService
{
    /**
     * Get current company context from request.
     *
     * @param Request|null $request
     * @return array|null
     */
    public function getCurrentContext(?Request $request = null): ?array
    {
        $request = $request ?? request();
        $subdomain = $request->attributes->get('subdomain');
        
        // No subdomain = Internal Metatech system
        if ($subdomain === null) {
            return [
                'type' => 'internal',
                'company_name' => 'Metatech',
                'subdomain' => null,
                'is_metatech_employee' => true,
            ];
        }
        
        // Has subdomain = Client company
        $company = User::where('subdomain', $subdomain)
            ->where('role', 'super_admin')
            ->whereNotNull('company_name')
            ->first();
        
        if (!$company) {
            return null; // Invalid subdomain
        }
        
        return [
            'type' => 'client',
            'company_name' => $company->company_name,
            'subdomain' => $subdomain,
            'company_id' => $company->id,
            'is_metatech_employee' => false,
        ];
    }

    /**
     * Verify user can access current subdomain.
     *
     * @param User $user
     * @param Request|null $request
     * @return bool
     */
    public function verifyUserAccess(User $user, ?Request $request = null): bool
    {
        $request = $request ?? request();
        $subdomain = $request->attributes->get('subdomain');
        $host = $request->getHost();
        
        // If subdomain not set by middleware, extract it manually
        if ($subdomain === null) {
            $subdomain = $this->extractSubdomainFromHost($host);
        }
        
        // For localhost development - be more lenient for testing
        if ($host === 'localhost' || strpos($host, '127.0.0.1') !== false || substr($host, -10) === '.localhost') {
            // admincrm.localhost - only Product Owner
            if (strpos($host, 'admincrm.') === 0) {
                return $user->isProductOwner();
            }
            
            // crm.localhost - only Internal Employees
            if ($host === 'crm.localhost') {
                return $user->is_metatech_employee && !$user->isProductOwner();
            }
            
            // For plain localhost:8000, check what the user is
            // If Product Owner, allow (they can login on localhost for testing)
            if ($user->isProductOwner()) {
                return $subdomain === null; // Allow Product Owner on plain localhost
            }
            
            // Metatech employees can access plain localhost (without admincrm or crm prefix) for internal CRM
            if ($user->is_metatech_employee && !$user->isProductOwner()) {
                return $subdomain === null && strpos($host, 'admincrm.') !== 0;
            }
            
            // Client users need matching subdomain
            if ($subdomain !== null) {
                // Check if user's subdomain matches the current subdomain
                $userSubdomain = strtolower(trim($user->subdomain ?? ''));
                $currentSubdomain = strtolower(trim($subdomain ?? ''));
                
                // Also check if user is a company super admin (they should have subdomain)
                if ($user->isCompanySuperAdmin() && !empty($userSubdomain)) {
                    return $userSubdomain === $currentSubdomain;
                }
                
                return $userSubdomain === $currentSubdomain && !empty($userSubdomain);
            }
            
            return false;
        }
        
        // Product Owner can ONLY access admincrm.metatech.ae (NOT crm.metatech.ae)
        if ($user->isProductOwner()) {
            // Strictly check for admincrm host only
            return strpos($host, 'admincrm.') === 0 || $host === 'admincrm.metatech.ae';
        }
        
        // Metatech employees can ONLY access crm.metatech.ae (NOT admincrm.metatech.ae)
        if ($user->is_metatech_employee && !$user->isProductOwner()) {
            // Must be crm.metatech.ae or crm.localhost, not admincrm
            return ($host === 'crm.metatech.ae' || $host === 'crm.localhost') && $subdomain === null && strpos($host, 'admincrm.') !== 0;
        }
        
        // Client users can only access their own subdomain
        if ($subdomain !== null) {
            // Check if user's subdomain matches the current subdomain
            $userSubdomain = strtolower(trim($user->subdomain ?? ''));
            $currentSubdomain = strtolower(trim($subdomain ?? ''));
            return $userSubdomain === $currentSubdomain && !empty($userSubdomain);
        }
        
        return false;
    }

    /**
     * Get allowed login URL for user.
     *
     * @param User $user
     * @param Request|null $request
     * @return string|null
     */
    public function getAllowedLoginUrl(User $user, ?Request $request = null): ?string
    {
        $request = $request ?? request();
        $host = $request->getHost();
        $port = $request->getPort();
        $protocol = $request->getScheme();
        $isLocalhost = ($host === 'localhost' || strpos($host, '127.0.0.1') !== false || substr($host, -10) === '.localhost');
        $portSuffix = ($port && $port !== 80 && $port !== 443) ? ':' . $port : '';
        
        if ($user->isProductOwner()) {
            $domain = $isLocalhost ? 'admincrm.localhost' . $portSuffix : 'admincrm.metatech.ae';
            return $protocol . '://' . $domain . '/login';
        }
        
        if ($user->is_metatech_employee && !$user->isProductOwner()) {
            $domain = $isLocalhost ? 'crm.localhost' . $portSuffix : 'crm.metatech.ae';
            return $protocol . '://' . $domain . '/login';
        }
        
        if ($user->subdomain) {
            // For production, use subdomain format (elitewealth.crm.metatech.ae)
            // This will work once Hostinger enables wildcard subdomain routing
            if (!$isLocalhost) {
                $domain = $user->subdomain . '.crm.metatech.ae';
                return 'http://' . $domain . '/login'; // HTTP until wildcard SSL is available
            }
            // For localhost, use subdomain directly
            $domain = $user->subdomain . '.localhost' . $portSuffix;
            return $protocol . '://' . $domain . '/login';
        }
        
        return null;
    }
    
    /**
     * Extract subdomain from host (fallback if middleware didn't set it)
     */
    protected function extractSubdomainFromHost(string $host): ?string
    {
        // Remove port if present
        $host = preg_replace('/:\d+$/', '', $host);
        
        // For admincrm.metatech.ae - this is product owner, no subdomain
        if (strpos($host, 'admincrm.') === 0) {
            return null;
        }
        
        // For crm.metatech.ae or crm.localhost (no subdomain) - this is internal Metatech employees
        if ($host === 'crm.metatech.ae' || $host === 'crm.localhost') {
            return null;
        }
        
        // For *.crm.metatech.ae format (client companies)
        // Example: acme.crm.metatech.ae -> acme
        if (preg_match('/^([^.]+)\.crm\.metatech\.ae$/', $host, $matches)) {
            return strtolower($matches[1]);
        }
        
        // For localhost development - check for subdomain pattern FIRST
        // Example: vyooo.localhost -> vyooo
        if (preg_match('/^([^.]+)\.localhost$/', $host, $matches)) {
            $extractedSubdomain = strtolower($matches[1]);
            // Don't treat 'crm' or 'admincrm' as subdomains on localhost
            if ($extractedSubdomain !== 'crm' && $extractedSubdomain !== 'admincrm') {
                return $extractedSubdomain;
            }
        }
        
        // For plain localhost (no subdomain) - return null
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return null;
        }
        
        return null;
    }
}

