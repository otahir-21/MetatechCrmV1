# Hostinger Wildcard Subdomain Configuration Request

## Issue
I need to configure wildcard subdomains for my multi-tenant Laravel SaaS application. Currently:
- DNS wildcard record (`*.crm.metatech.ae`) is configured and resolving correctly
- But the web server (Apache) is not routing requests to my Laravel application
- Individual subdomains like `elitewealth.crm.metatech.ae` return "404 Not Found"

## What I Need
Apache VirtualHost configuration to route all `*.crm.metatech.ae` subdomains to my Laravel application located at:
```
/home/u199293942/domains/metatech.ae/public_html/admincrm/public
```

## Request to Hostinger Support

### Option 1: Enable Wildcard Subdomain Routing (Preferred)
Please configure Apache to handle wildcard subdomains for `*.crm.metatech.ae` and route them all to:
```
/home/u199293942/domains/metatech.ae/public_html/admincrm/public
```

This requires adding an Apache VirtualHost configuration like:
```apache
<VirtualHost *:80>
    ServerAlias *.crm.metatech.ae
    DocumentRoot /home/u199293942/domains/metatech.ae/public_html/admincrm/public
    <Directory /home/u199293942/domains/metatech.ae/public_html/admincrm/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Option 2: Manual Subdomain Configuration (Alternative)
If wildcard subdomains are not supported, please manually create subdomains pointing to the same directory:
- Each subdomain: `{companyname}.crm.metatech.ae`
- All pointing to: `/home/u199293942/domains/metatech.ae/public_html/admincrm/public`

This is less ideal as it requires manual configuration for each new company.

## Current Setup
- Domain: `metatech.ae`
- Main application: `/home/u199293942/domains/metatech.ae/public_html/admincrm/public`
- DNS wildcard: `*.crm` → `147.93.17.204` (configured)
- Existing subdomains working:
  - `admincrm.metatech.ae` ✅
  - `crm.metatech.ae` ✅

## Laravel Application Details
- Framework: Laravel 11
- Uses middleware to detect subdomains from `HTTP_HOST`
- All routes are handled by a single Laravel application
- The application already handles subdomain detection internally

## Why This Is Needed
This is a multi-tenant SaaS application where each client gets their own subdomain:
- `company1.crm.metatech.ae`
- `company2.crm.metatech.ae`
- etc.

The Laravel application routes requests based on the subdomain, but Apache needs to send all `*.crm.metatech.ae` requests to the Laravel application first.

