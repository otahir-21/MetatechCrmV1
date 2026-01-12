# Deployment Checklist for Hostinger

Use this checklist to ensure a smooth deployment to Hostinger.

## Pre-Deployment

### 1. Code Preparation
- [ ] All code is committed to Git
- [ ] All tests pass (if applicable)
- [ ] `.env.example` is up to date
- [ ] No sensitive data in code (use `.env` for secrets)
- [ ] `APP_DEBUG=false` in production `.env`
- [ ] `APP_ENV=production` in production `.env`

### 2. Database Preparation
- [ ] Database created on Hostinger
- [ ] Database user created with proper permissions
- [ ] Backup of local database (if migrating data)
- [ ] All migrations are tested locally

### 3. Server Requirements Check
- [ ] PHP 8.1+ installed
- [ ] Composer installed
- [ ] Node.js & NPM installed (for asset building)
- [ ] MySQL/MariaDB available
- [ ] SSH access configured
- [ ] SSL certificate installed (for HTTPS)

### 4. GitHub Setup
- [ ] Repository created on GitHub
- [ ] Code pushed to GitHub
- [ ] GitHub Secrets configured:
  - [ ] `HOSTINGER_HOST`
  - [ ] `HOSTINGER_USERNAME`
  - [ ] `HOSTINGER_SSH_KEY`
  - [ ] `HOSTINGER_PORT`
  - [ ] `HOSTINGER_DEPLOY_PATH`

### 5. Environment Configuration
- [ ] `.env` file created on server with:
  - [ ] `APP_KEY` generated
  - [ ] Database credentials
  - [ ] Mail configuration
  - [ ] JWT secret
  - [ ] All required environment variables

## Deployment Steps

### 6. Initial Server Setup
- [ ] SSH into server
- [ ] Navigate to project directory
- [ ] Clone repository (first time only)
- [ ] Install Composer dependencies
- [ ] Set file permissions
- [ ] Create `.env` file
- [ ] Generate application key
- [ ] Run initial migrations

### 7. Domain & Subdomain Configuration
- [ ] Main domain configured (`metatech.ae`)
- [ ] `admincrm.metatech.ae` DNS configured
- [ ] `crm.metatech.ae` DNS configured
- [ ] Wildcard DNS for `*.crm.metatech.ae` configured
- [ ] All subdomains point to correct directory

### 8. First Deployment
- [ ] Push code to `main` branch
- [ ] GitHub Actions workflow triggered
- [ ] Deployment completed successfully
- [ ] Site accessible via browser
- [ ] All routes working
- [ ] Database migrations applied

## Post-Deployment

### 9. Testing
- [ ] Main domain loads correctly
- [ ] Product Owner login works (`admincrm.metatech.ae`)
- [ ] Internal CRM login works (`crm.metatech.ae`)
- [ ] Company subdomain login works (`company.crm.metatech.ae`)
- [ ] All API endpoints working
- [ ] Email sending works
- [ ] File uploads work (if applicable)
- [ ] Database operations work

### 10. Security
- [ ] SSL certificate active (HTTPS)
- [ ] `.env` file not accessible via web
- [ ] `storage` directory permissions correct
- [ ] `bootstrap/cache` directory permissions correct
- [ ] No sensitive files in public directory

### 11. Performance
- [ ] Config cache enabled
- [ ] Route cache enabled
- [ ] View cache enabled
- [ ] Assets compiled and optimized
- [ ] Database indexes created

### 12. Monitoring & Backup
- [ ] Error logging configured
- [ ] Backup strategy in place
- [ ] Monitoring set up (optional)
- [ ] Rollback plan documented

## Regular Deployment

### 13. Ongoing Deployments
- [ ] Code changes committed
- [ ] Pushed to GitHub
- [ ] GitHub Actions deployment successful
- [ ] Site tested after deployment
- [ ] Rollback plan ready (if needed)

## Troubleshooting

### Common Issues
- [ ] SSH connection issues → Check SSH key and credentials
- [ ] Permission errors → Check file ownership and permissions
- [ ] Database connection errors → Verify database credentials in `.env`
- [ ] 500 errors → Check `storage/logs/laravel.log`
- [ ] Assets not loading → Run `npm run build` and deploy `public/build`

---

**Last Updated:** [Date]
**Deployed By:** [Name]
**Deployment Version:** [Git Commit Hash]

