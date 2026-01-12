<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class DetectSubdomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        
        // Extract subdomain from host
        // Example: acme.crm.metatech.ae -> acme
        // Example: crm.metatech.ae -> null (no subdomain)
        $subdomain = $this->extractSubdomain($host);
        
        // Store subdomain in request for later use
        $request->attributes->set('subdomain', $subdomain);
        
        // Set app context based on subdomain
        App::instance('subdomain', $subdomain);
        
        return $next($request);
    }

    /**
     * Extract subdomain from host.
     *
     * @param string $host
     * @return string|null
     */
    protected function extractSubdomain(string $host): ?string
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
        
        // For localhost development
        if ($host === 'localhost' || strpos($host, '127.0.0.1') !== false || strpos($host, ':8000') !== false) {
            // admincrm.localhost - Product Owner (no subdomain)
            if (strpos($host, 'admincrm.') === 0) {
                return null;
            }
            
            // crm.localhost - Internal CRM (no subdomain)
            if ($host === 'crm.localhost' || preg_match('/^crm\.localhost/', $host)) {
                return null;
            }
            
            // For local development with client subdomain
            // Example: acme.localhost:8000 -> acme
            // But NOT crm.localhost or admincrm.localhost (already handled above)
            if (preg_match('/^([^.]+)\.localhost/', $host, $matches)) {
                $extractedSubdomain = strtolower($matches[1]);
                // Don't treat 'crm' or 'admincrm' as subdomains on localhost
                if ($extractedSubdomain !== 'crm' && $extractedSubdomain !== 'admincrm') {
                    return $extractedSubdomain;
                }
            }
        }
        
        return null;
    }
}
