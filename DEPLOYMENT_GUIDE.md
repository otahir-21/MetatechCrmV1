# Multi-Tenant Subdomain Deployment Guide

## Overview

This application uses **subdomain-based multi-tenancy**. You host the entire application on **ONE server**, and Laravel automatically detects and routes requests based on the subdomain.

## DNS Configuration

### Option 1: Wildcard DNS (Recommended for Production)

Configure DNS records for `metatech.ae`:

```
Type    Name                    Value                    TTL
A       admincrm                YOUR_SERVER_IP           3600
A       crm                     YOUR_SERVER_IP           3600
A       *.crm                   YOUR_SERVER_IP           3600
```

**OR use CNAME (if using a domain manager like Cloudflare):**

```
Type    Name                    Value                           TTL
A       admincrm                YOUR_SERVER_IP                  3600
CNAME   crm                     YOUR_SERVER_IP or main domain   3600
CNAME   *.crm                   YOUR_SERVER_IP or main domain   3600
```

### What This Means:

- `admincrm.metatech.ae` → Points to your server
- `crm.metatech.ae` → Points to your server  
- `acme.crm.metatech.ae` → Points to your server (wildcard catches all)
- `anycompany.crm.metatech.ae` → Points to your server (wildcard catches all)

**All subdomains point to the SAME server/IP address!**

## Web Server Configuration

### For Apache (with mod_rewrite)

Ensure your `.htaccess` is configured to handle all subdomains:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### For Nginx

Configure your server block to handle all subdomains:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name admincrm.metatech.ae crm.metatech.ae *.crm.metatech.ae;
    root /path/to/MetatechCrmV1/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Key point**: `server_name` includes wildcard `*.crm.metatech.ae` to catch all client subdomains.

## SSL/HTTPS Configuration

### Using Let's Encrypt (Certbot) with Wildcard Certificate

For wildcard subdomains, you need DNS-01 challenge:

```bash
certbot certonly --manual --preferred-challenges dns \
  -d admincrm.metatech.ae \
  -d crm.metatech.ae \
  -d *.crm.metatech.ae
```

Or use automatic DNS challenge if your DNS provider supports it (e.g., Cloudflare plugin).

### Nginx SSL Configuration

```nginx
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name admincrm.metatech.ae crm.metatech.ae *.crm.metatech.ae;
    
    ssl_certificate /etc/letsencrypt/live/metatech.ae/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/metatech.ae/privkey.pem;
    
    # ... rest of configuration
}
```

## Laravel Configuration

### Environment File (`.env`)

```env
APP_URL=https://admincrm.metatech.ae
SESSION_DOMAIN=.metatech.ae  # Important for cookie sharing across subdomains
```

**Note**: The `SESSION_DOMAIN` with a leading dot allows sessions to work across all subdomains.

## How It Works

1. **User visits** `acme.crm.metatech.ae/login`
2. **DNS resolves** to your server IP (wildcard DNS)
3. **Web server** (Apache/Nginx) forwards request to Laravel
4. **DetectSubdomain middleware** extracts "acme" from hostname
5. **VerifySubdomainAccess middleware** checks if user belongs to "acme" subdomain
6. **Laravel routes** to appropriate controller/view

## Testing Locally

### Option 1: Modify hosts file

Edit `/etc/hosts` (Linux/Mac) or `C:\Windows\System32\drivers\etc\hosts` (Windows):

```
127.0.0.1 admincrm.localhost
127.0.0.1 crm.localhost
127.0.0.1 acme.localhost
127.0.0.1 testcompany.localhost
```

Then access:
- http://admincrm.localhost:8000
- http://crm.localhost:8000
- http://acme.localhost:8000

### Option 2: Use Laravel Valet

Valet automatically handles `*.test` subdomains:
- http://admincrm.test
- http://crm.test
- http://acme.test

## Summary

✅ **ONE hosting/server**  
✅ **ONE Laravel application**  
✅ **Wildcard DNS configuration**  
✅ **Web server configured for wildcard subdomains**  
✅ **Laravel handles subdomain detection and routing**

You do NOT need:
- ❌ Separate hosting for each subdomain
- ❌ Separate applications for each subdomain
- ❌ Manual configuration for each new client subdomain

## What to Search/Configure

1. **DNS Provider** (where you manage metatech.ae domain):
   - Search: "How to add wildcard DNS record"
   - Add: `*.crm` A record pointing to your server IP

2. **Web Server** (Apache/Nginx):
   - Search: "Configure wildcard subdomain Apache/Nginx"
   - Configure server to accept all subdomains

3. **SSL Certificate**:
   - Search: "Let's Encrypt wildcard certificate DNS challenge"
   - Get SSL certificate for `*.crm.metatech.ae`

4. **Laravel Session**:
   - Configure `SESSION_DOMAIN=.metatech.ae` in `.env`

