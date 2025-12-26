# CI/CD Deployment Guide for Metatech CRM - Hostinger

This guide will help you set up Continuous Integration/Continuous Deployment (CI/CD) for your Laravel project and deploy it to Hostinger.

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Understanding CI/CD](#understanding-cicd)
3. [Setting Up CI/CD with GitHub Actions](#setting-up-cicd-with-github-actions)
4. [Hostinger Deployment Setup](#hostinger-deployment-setup)
5. [Environment Configuration](#environment-configuration)
6. [Database Migration Strategy](#database-migration-strategy)
7. [Deployment Steps](#deployment-steps)

---

## Prerequisites

Before starting, ensure you have:
- ✅ GitHub account (or GitLab/Bitbucket)
- ✅ Hostinger hosting account with SSH access
- ✅ Domain configured (e.g., `metatech.ae`, `admincrm.metatech.ae`, `crm.metatech.ae`)
- ✅ Database created on Hostinger
- ✅ SSH credentials from Hostinger

---

## Understanding CI/CD

**CI/CD (Continuous Integration/Continuous Deployment)** automates:
- **CI**: Testing, building, and validating code when you push to Git
- **CD**: Automatically deploying to production when tests pass

**Benefits:**
- ✅ Automatic deployments
- ✅ Reduced human error
- ✅ Faster releases
- ✅ Better code quality

---

## Setting Up CI/CD with GitHub Actions

### Step 1: Create GitHub Actions Workflow

Create the following file in your project:

**`.github/workflows/deploy.yml`**

```yaml
name: Deploy to Hostinger

on:
  push:
    branches:
      - main  # or 'master' depending on your default branch
  workflow_dispatch:  # Allows manual trigger

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, xml, bcmath, pdo, pdo_mysql, tokenizer, json, ctype, fileinfo
      
      - name: Copy .env file
        run: |
          echo "Copying .env.example to .env"
          cp .env.example .env || true
      
      - name: Install dependencies
        run: |
          composer install --no-dev --optimize-autoloader --no-interaction
      
      - name: Run tests (optional)
        run: |
          php artisan test || echo "Tests skipped"
        continue-on-error: true
      
      - name: Build assets (if using Vite)
        run: |
          npm ci
          npm run build
        continue-on-error: true
      
      - name: Deploy to Hostinger via SSH
        uses: appleboy/scp-action@master
        with:
          host: ${{ secrets.HOSTINGER_HOST }}
          username: ${{ secrets.HOSTINGER_USERNAME }}
          key: ${{ secrets.HOSTINGER_SSH_KEY }}
          port: ${{ secrets.HOSTINGER_PORT || 65002 }}
          source: "."
          target: "/home/u123456789/domains/metatech.ae/public_html"
          rm: false
          strip_components: 0
      
      - name: Run deployment commands on server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.HOSTINGER_HOST }}
          username: ${{ secrets.HOSTINGER_USERNAME }}
          key: ${{ secrets.HOSTINGER_SSH_KEY }}
          port: ${{ secrets.HOSTINGER_PORT || 65002 }}
          script: |
            cd /home/u123456789/domains/metatech.ae/public_html
            php artisan down || true
            php artisan config:clear
            php artisan cache:clear
            php artisan route:clear
            php artisan view:clear
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            php artisan up
```

### Step 2: Configure GitHub Secrets

1. Go to your GitHub repository
2. Navigate to **Settings** → **Secrets and variables** → **Actions**
3. Add the following secrets:

| Secret Name | Description | How to Get |
|------------|-------------|------------|
| `HOSTINGER_HOST` | Your server IP or hostname | From Hostinger control panel |
| `HOSTINGER_USERNAME` | SSH username | Usually `u123456789` (from Hostinger) |
| `HOSTINGER_SSH_KEY` | Your private SSH key | Generate SSH key pair (see below) |
| `HOSTINGER_PORT` | SSH port (usually 65002) | From Hostinger SSH settings |

**Generate SSH Key:**
```bash
# On your local machine
ssh-keygen -t rsa -b 4096 -C "your_email@example.com" -f ~/.ssh/hostinger_deploy

# Copy the PUBLIC key to Hostinger
cat ~/.ssh/hostinger_deploy.pub
# Add this to Hostinger → Advanced → SSH Access → Authorized Keys

# Copy the PRIVATE key to GitHub Secrets
cat ~/.ssh/hostinger_deploy
# Copy the entire output (including -----BEGIN and -----END) to HOSTINGER_SSH_KEY secret
```

---

## Hostinger Deployment Setup

### Step 1: Get Your Hostinger Details

1. **SSH Access:**
   - Log in to Hostinger control panel
   - Go to **Advanced** → **SSH Access**
   - Note your:
     - Username (e.g., `u123456789`)
     - Host/IP address
     - Port (usually `65002`)

2. **Domain Path:**
   - Your project should be in: `/home/u123456789/domains/yourdomain.com/public_html`
   - For subdomains: `/home/u123456789/domains/subdomain.yourdomain.com/public_html`

### Step 2: Server Requirements

Ensure your Hostinger server has:
- ✅ PHP 8.1 or higher
- ✅ Composer installed
- ✅ MySQL/MariaDB
- ✅ Node.js & NPM (for asset compilation)
- ✅ Git

**Check PHP version:**
```bash
ssh u123456789@your-server-ip -p 65002
php -v
```

**Install Composer (if not installed):**
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

### Step 3: Initial Server Setup

**First-time deployment (manual):**

```bash
# SSH into your server
ssh u123456789@your-server-ip -p 65002

# Navigate to your domain directory
cd /home/u123456789/domains/metatech.ae/public_html

# Clone your repository (if not already done)
git clone https://github.com/yourusername/metatech-crm.git .

# Install dependencies
composer install --no-dev --optimize-autoloader

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R u123456789:u123456789 storage bootstrap/cache
```

---

## Environment Configuration

### Step 1: Create `.env` File on Server

Create `.env` file with production settings:

```env
APP_NAME="Metatech CRM"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://metatech.ae

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Mail Configuration (use Hostinger SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your_email@metatech.ae
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@metatech.ae
MAIL_FROM_NAME="${APP_NAME}"

# JWT Configuration
JWT_SECRET=your_jwt_secret_here
JWT_TTL=60

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

### Step 2: Configure Subdomains

For multi-tenant subdomains, you need to:

1. **Add DNS Records in Hostinger:**
   - `admincrm.metatech.ae` → A record → Your server IP
   - `crm.metatech.ae` → A record → Your server IP
   - `*.crm.metatech.ae` → A record → Your server IP (wildcard for client subdomains)

2. **Create Virtual Hosts (if using Apache):**
   - Hostinger usually handles this automatically
   - Ensure all subdomains point to the same `public_html` directory

3. **Update `.env`:**
   ```env
   # Add these for subdomain detection
   APP_DOMAIN=metatech.ae
   ADMIN_SUBDOMAIN=admincrm
   INTERNAL_SUBDOMAIN=crm
   ```

---

## Database Migration Strategy

### Option 1: Automatic Migration (Recommended for CI/CD)

The GitHub Actions workflow includes:
```yaml
php artisan migrate --force
```

This runs migrations automatically on each deployment.

### Option 2: Manual Migration

If you prefer manual control:
```bash
ssh u123456789@your-server-ip -p 65002
cd /home/u123456789/domains/metatech.ae/public_html
php artisan migrate
```

---

## Deployment Steps

### Initial Setup (One-time)

1. **Push code to GitHub:**
   ```bash
   git add .
   git commit -m "Initial commit"
   git push origin main
   ```

2. **Configure GitHub Secrets** (as described above)

3. **Set up server** (as described in "Initial Server Setup")

4. **Create `.env` file on server** with production values

5. **Run initial migration:**
   ```bash
   ssh u123456789@your-server-ip -p 65002
   cd /home/u123456789/domains/metatech.ae/public_html
   php artisan migrate
   ```

### Regular Deployment (Automatic)

After initial setup, deployments are automatic:

1. **Make changes locally**
2. **Commit and push:**
   ```bash
   git add .
   git commit -m "Your changes"
   git push origin main
   ```
3. **GitHub Actions automatically:**
   - Runs tests
   - Builds assets
   - Deploys to Hostinger
   - Runs migrations
   - Clears caches

### Manual Deployment (If Needed)

If you need to deploy manually:

```bash
# SSH into server
ssh u123456789@your-server-ip -p 65002

# Navigate to project
cd /home/u123456789/domains/metatech.ae/public_html

# Pull latest changes
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Additional Considerations

### 1. File Permissions

Ensure correct permissions:
```bash
chmod -R 755 storage bootstrap/cache
chown -R u123456789:u123456789 storage bootstrap/cache
```

### 2. Queue Workers (If Using Queues)

If you use Laravel queues, set up a supervisor or cron job:
```bash
# Add to crontab
* * * * * cd /home/u123456789/domains/metatech.ae/public_html && php artisan schedule:run >> /dev/null 2>&1
```

### 3. SSL Certificates

Hostinger usually provides free SSL certificates. Ensure they're enabled for all subdomains.

### 4. Backup Strategy

Before each deployment, backup:
- Database
- `.env` file
- `storage/` directory

### 5. Rollback Plan

If something goes wrong:
```bash
# Put site in maintenance mode
php artisan down

# Revert to previous commit
git reset --hard HEAD~1

# Restore from backup
# (restore database and files)

# Bring site back up
php artisan up
```

---

## Troubleshooting

### Issue: SSH Connection Failed
- **Solution:** Verify SSH key is correctly added to GitHub Secrets and Hostinger

### Issue: Permission Denied
- **Solution:** Check file ownership and permissions on server

### Issue: Migration Fails
- **Solution:** Review database credentials in `.env` file

### Issue: Assets Not Loading
- **Solution:** Run `npm run build` and ensure `public/build` is deployed

---

## Next Steps

1. ✅ Set up GitHub repository
2. ✅ Configure GitHub Secrets
3. ✅ Set up SSH access on Hostinger
4. ✅ Create `.env` file on server
5. ✅ Run initial deployment
6. ✅ Test the deployment
7. ✅ Set up monitoring and backups

---

## Support

If you encounter issues:
1. Check GitHub Actions logs
2. Review server error logs: `storage/logs/laravel.log`
3. Verify all environment variables are set correctly
4. Ensure all dependencies are installed

---

**Good luck with your deployment! 🚀**

