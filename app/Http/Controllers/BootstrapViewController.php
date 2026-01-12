<?php

namespace App\Http\Controllers;

use App\Services\BootstrapService;
use Illuminate\Http\Request;

class BootstrapViewController extends Controller
{
    protected BootstrapService $bootstrapService;

    public function __construct(BootstrapService $bootstrapService)
    {
        $this->bootstrapService = $bootstrapService;
    }

    /**
     * Show bootstrap status page
     */
    public function index()
    {
        $host = request()->getHost();
        
        // For crm.metatech.ae (internal Metatech employees) - redirect to login
        // The login page will automatically show the correct form based on subdomain
        if ($host === 'crm.metatech.ae') {
            return redirect()->route('login');
        }
        
        // For admincrm or root (bootstrap/product owner flow)
        $status = $this->bootstrapService->getStatus();
        
        // Redirect based on status
        if ($status['status'] === 'BOOTSTRAP_PENDING') {
            return redirect()->route('bootstrap.create');
        } elseif ($status['status'] === 'BOOTSTRAP_CONFIRMED' || $status['status'] === 'ACTIVE') {
            // If confirmed or active, redirect to product owner login
            return redirect()->route('login');
        }

        return view('bootstrap.index', compact('status'));
    }

    /**
     * Show create Super Admin form
     */
    public function create()
    {
        $status = $this->bootstrapService->getStatus();
        
        if ($status['status'] !== 'BOOTSTRAP_PENDING') {
            return redirect()->route('bootstrap.index');
        }

        return view('bootstrap.create');
    }

    /**
     * Show confirm bootstrap page
     */
    public function confirm()
    {
        $status = $this->bootstrapService->getStatus();
        
        if ($status['status'] === 'ACTIVE') {
            // If already confirmed, redirect to login
            return redirect()->route('login');
        }
        
        if ($status['status'] !== 'BOOTSTRAP_CONFIRMED') {
            return redirect()->route('bootstrap.index');
        }

        // Redirect to login page - user can confirm bootstrap via API if needed
        return redirect()->route('login');
    }

    /**
     * Show bootstrap complete page
     */
    public function complete()
    {
        $status = $this->bootstrapService->getStatus();
        
        // Redirect to login after bootstrap is complete
        return redirect()->route('login');
    }

    /**
     * Show login page (detects which login page to show based on subdomain)
     */
    public function showLogin()
    {
        $host = request()->getHost();
        $subdomain = request()->attributes->get('subdomain');
        
        // If subdomain not set by middleware, extract it manually
        if ($subdomain === null) {
            $subdomain = $this->extractSubdomainFromHost($host);
        }
        
        // Debug output (temporary - remove after testing)
        // Uncomment the line below to see debug info on the page
        // return response("Host: $host, Subdomain: " . ($subdomain ?? 'null') . ", Full URL: " . request()->fullUrl() . ", Company: " . ($subdomain ? (\App\Models\Company::where('subdomain', $subdomain)->exists() ? 'exists' : 'not found') : 'N/A'));
        
        // For crm.metatech.ae or crm.localhost - Internal Employee login
        if ($host === 'crm.metatech.ae' || $host === 'crm.localhost') {
            return view('auth.internal-login');
        }
        
        // For admincrm.localhost or admincrm.metatech.ae - Product Owner login
        if (strpos($host, 'admincrm.') === 0 || $host === 'admincrm.metatech.ae') {
            return view('auth.login', ['loginType' => 'product_owner']);
        }
        
        // For company subdomains via query parameter (e.g., ?subdomain=elitewealth)
        // This handles company login on main domain since wildcard subdomains don't work on shared hosting
        $subdomainParam = request()->query('subdomain');
        if ($subdomainParam) {
            $company = \App\Models\Company::where('subdomain', $subdomainParam)->first();
            if ($company) {
                return view('auth.login', [
                    'loginType' => 'company',
                    'companyName' => $company->company_name,
                    'subdomain' => $subdomainParam,
                ]);
            }
        }
        
        // For company subdomains (e.g., vyooo.localhost, acme.crm.metatech.ae) - Company login
        // Check BEFORE the plain localhost check (for localhost development)
        if ($subdomain !== null && $subdomain !== 'crm' && $subdomain !== 'admincrm') {
            // Verify company exists
            $company = \App\Models\Company::where('subdomain', $subdomain)->first();
            if ($company) {
                return view('auth.login', [
                    'loginType' => 'company',
                    'companyName' => $company->company_name,
                    'subdomain' => $subdomain,
                ]);
            }
        }
        
        // For plain localhost (without subdomain) in development - show Product Owner login by default
        // IMPORTANT: Only match exact 'localhost' or '127.0.0.1', NOT subdomains like 'vyooo.localhost'
        if ($host === 'localhost' || $host === '127.0.0.1') {
            // Check if user explicitly wants internal login via query param
            if (request()->get('type') === 'internal') {
                return view('auth.internal-login');
            }
            // Default to Product Owner login on plain localhost
            return view('auth.login', ['loginType' => 'product_owner']);
        }
        
        // Default to Product Owner login
        return view('auth.login', ['loginType' => 'product_owner']);
    }

    /**
     * Show Internal CRM login page (for Metatech employees)
     */
    public function showInternalLogin()
    {
        return view('auth.login', ['loginType' => 'internal']);
    }

    /**
     * Show dashboard
     */
    public function dashboard()
    {
        $user = auth()->user();
        $token = null;
        
        // Generate JWT token for API calls if user is authenticated
        if ($user) {
            $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        }
        
        return view('dashboard.index', ['api_token' => $token]);
    }

    /**
     * Show company creation form
     */
    public function showCompanyCreate()
    {
        return view('company.create');
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
        
        // For localhost development - check for subdomain pattern FIRST (before plain localhost check)
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
