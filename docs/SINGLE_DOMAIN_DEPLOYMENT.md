# Single Domain Deployment Guide - admincrm.metatech.ae

Since you only have **one domain** (`admincrm.metatech.ae`), here's what you need to do:

## Deployment Path on Hostinger

Your deployment path will be:
```
/home/u123456789/domains/admincrm.metatech.ae/public_html
```

## What You Need to Do

### 1. Update GitHub Secret: HOSTINGER_DEPLOY_PATH

In GitHub → Settings → Secrets → Actions, set:

**HOSTINGER_DEPLOY_PATH:**
```
/home/u123456789/domains/admincrm.metatech.ae/public_html
```

(Replace `u123456789` with your actual Hostinger username)

### 2. Update .env File on Server

When you SSH into Hostinger and create the `.env` file, use:

```env
APP_NAME="Metatech CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admincrm.metatech.ae

# ... rest of your configuration
```

### 3. DNS Configuration

In Hostinger DNS settings, make sure:
- `admincrm.metatech.ae` → A record → Points to your server IP

### 4. SSL Certificate

Enable SSL certificate for `admincrm.metatech.ae` in Hostinger control panel.

## Important Note About Subdomain Detection

**Current Behavior:**
- The system will detect `admincrm` as a subdomain
- Currently, the code treats `admincrm` as Product Owner domain only

**What This Means:**
- ✅ Product Owner dashboard will work: `https://admincrm.metatech.ae/login`
- ⚠️ Internal CRM (`crm.metatech.ae`) won't work (you don't have this subdomain)
- ⚠️ Client subdomains (`company.crm.metatech.ae`) won't work

**If you need Internal CRM or Client portals:**
You'll need to modify the code to use path-based routing instead:
- `admincrm.metatech.ae/internal` → Internal CRM
- `admincrm.metatech.ae/company/{name}` → Client portals

**For now (Product Owner only):**
The deployment will work as-is for the Product Owner dashboard.

## Deployment Steps

1. **Set GitHub Secret** (HOSTINGER_DEPLOY_PATH) as shown above
2. **SSH into Hostinger:**
   ```bash
   ssh u123456789@your-server-ip -p 65002
   cd /home/u123456789/domains/admincrm.metatech.ae/public_html
   ```
3. **Clone repository** (first time only):
   ```bash
   git clone https://github.com/yourusername/your-repo.git .
   ```
4. **Configure .env** with `APP_URL=https://admincrm.metatech.ae`
5. **Push to GitHub** - deployment will happen automatically

That's it! 🚀

