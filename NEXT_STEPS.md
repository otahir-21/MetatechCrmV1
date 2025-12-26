# Next Steps - After GitHub is Connected

Now that your GitHub repository is connected, follow these steps to set up automatic deployment to Hostinger.

## ✅ Step 1: Get Hostinger SSH Information

1. **Log in to Hostinger Control Panel**
   - Go to: https://hpanel.hostinger.com
   - Log in with your credentials

2. **Get SSH Details:**
   - Click on **"Advanced"** → **"SSH Access"**
   - You'll see:
     - **Username:** (e.g., `u123456789`)
     - **Host/IP:** (e.g., `185.230.63.107`)
     - **Port:** (usually `65002`)

3. **Note down these details** - you'll need them for GitHub Secrets

---

## ✅ Step 2: Generate SSH Key for Deployment

**On your local computer, run these commands:**

```bash
# Generate SSH key pair
ssh-keygen -t rsa -b 4096 -C "your_email@example.com" -f ~/.ssh/hostinger_deploy

# When prompted, press Enter (use default location)
# When prompted for passphrase, press Enter (no passphrase for CI/CD)
```

**Copy the PUBLIC key:**
```bash
cat ~/.ssh/hostinger_deploy.pub
```

**Add it to Hostinger:**
1. Go to Hostinger → **Advanced** → **SSH Access**
2. Click **"Manage SSH Keys"** or **"Add SSH Key"**
3. Paste the PUBLIC key content
4. Save

**Copy the PRIVATE key (for GitHub):**
```bash
cat ~/.ssh/hostinger_deploy
```
- Copy the ENTIRE output (including `-----BEGIN OPENSSH PRIVATE KEY-----` and `-----END OPENSSH PRIVATE KEY-----`)
- You'll add this to GitHub Secrets in the next step

---

## ✅ Step 3: Configure GitHub Secrets

1. **Go to your GitHub repository**
   - Navigate to: `https://github.com/yourusername/your-repo-name`

2. **Go to Settings:**
   - Click **"Settings"** tab (top menu)
   - Click **"Secrets and variables"** → **"Actions"** (left sidebar)
   - Click **"New repository secret"**

3. **Add these secrets one by one:**

   **Secret 1: HOSTINGER_HOST**
   - Name: `HOSTINGER_HOST`
   - Value: Your server IP from Hostinger (e.g., `185.230.63.107`)
   - Click **"Add secret"**

   **Secret 2: HOSTINGER_USERNAME**
   - Name: `HOSTINGER_USERNAME`
   - Value: Your SSH username (e.g., `u123456789`)
   - Click **"Add secret"**

   **Secret 3: HOSTINGER_SSH_KEY**
   - Name: `HOSTINGER_SSH_KEY`
   - Value: Paste the PRIVATE key you copied earlier (entire content)
   - Click **"Add secret"**

   **Secret 4: HOSTINGER_PORT**
   - Name: `HOSTINGER_PORT`
   - Value: `65002` (or your port from Hostinger)
   - Click **"Add secret"**

   **Secret 5: HOSTINGER_DEPLOY_PATH**
   - Name: `HOSTINGER_DEPLOY_PATH`
   - Value: `/home/u123456789/domains/yourdomain.com/public_html`
     - Replace `u123456789` with your actual username
     - Replace `yourdomain.com` with your actual domain
   - Click **"Add secret"**

---

## ✅ Step 4: Find Your Deployment Path

**To find your exact deployment path:**

1. **SSH into Hostinger:**
   ```bash
   ssh u123456789@your-server-ip -p 65002
   ```
   (Replace with your actual username and IP)

2. **Find your domain directory:**
   ```bash
   cd ~
   ls -la domains/
   ```
   You'll see your domain folder (e.g., `metatech.ae`)

3. **Your deployment path will be:**
   ```
   /home/u123456789/domains/yourdomain.com/public_html
   ```

4. **Exit SSH:**
   ```bash
   exit
   ```

---

## ✅ Step 5: Initial Server Setup (One-Time)

**SSH into your Hostinger server and run:**

```bash
# SSH into server
ssh u123456789@your-server-ip -p 65002

# Navigate to your domain directory
cd /home/u123456789/domains/yourdomain.com/public_html

# Clone your repository (if not already done)
git clone https://github.com/yourusername/your-repo-name.git .

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Create .env file
cp .env.example .env

# Generate application key
php artisan key:generate

# Set correct permissions
chmod -R 755 storage bootstrap/cache
chown -R u123456789:u123456789 storage bootstrap/cache

# Exit
exit
```

---

## ✅ Step 6: Configure Production .env File

**SSH into server and edit .env:**

```bash
ssh u123456789@your-server-ip -p 65002
cd /home/u123456789/domains/yourdomain.com/public_html
nano .env
```

**Update these important values:**

```env
APP_NAME="Metatech CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database (get from Hostinger → Databases)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Mail (use Hostinger SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your_email@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Save and exit:**
- Press `Ctrl + X`
- Press `Y` to confirm
- Press `Enter` to save

---

## ✅ Step 7: Run Initial Database Migration

```bash
# Still on server
php artisan migrate --force
```

---

## ✅ Step 8: Test Your First Deployment

1. **Make a small change locally:**
   ```bash
   # On your local computer
   echo "# Test deployment" >> README.md
   ```

2. **Commit and push:**
   ```bash
   git add .
   git commit -m "Test CI/CD deployment"
   git push origin main
   ```

3. **Check GitHub Actions:**
   - Go to your GitHub repository
   - Click **"Actions"** tab
   - You should see "Deploy to Hostinger" workflow running
   - Wait for it to complete (green checkmark = success)

4. **Verify deployment:**
   - Visit your website
   - Check if changes are live

---

## ✅ Step 9: Configure DNS for Subdomains

**In Hostinger Control Panel:**

1. Go to **"Domains"** → **"DNS Zone Editor"**
2. Add these DNS records:

   **For Product Owner Dashboard:**
   - Type: `A`
   - Name: `admincrm`
   - Points to: Your server IP
   - TTL: `3600`

   **For Internal CRM:**
   - Type: `A`
   - Name: `crm`
   - Points to: Your server IP
   - TTL: `3600`

   **For Client Subdomains (Wildcard):**
   - Type: `A`
   - Name: `*.crm`
   - Points to: Your server IP
   - TTL: `3600`

3. **Wait 5-10 minutes** for DNS to propagate

---

## ✅ Step 10: Enable SSL Certificates

1. **In Hostinger Control Panel:**
   - Go to **"SSL"** section
   - Enable SSL for:
     - Main domain (`yourdomain.com`)
     - `admincrm.yourdomain.com`
     - `crm.yourdomain.com`
   - Hostinger usually provides free SSL certificates

---

## 🎉 You're Done!

**From now on, every time you push to GitHub:**
- ✅ Code automatically deploys to Hostinger
- ✅ Database migrations run automatically
- ✅ Caches are cleared and rebuilt
- ✅ Your site is updated

**To deploy:**
```bash
git add .
git commit -m "Your changes"
git push origin main
```

---

## 🔧 Troubleshooting

### Issue: GitHub Actions fails with "Permission denied"
**Solution:** 
- Check SSH key is correctly added to Hostinger
- Verify private key is correctly added to GitHub Secrets

### Issue: Deployment path not found
**Solution:**
- Verify `HOSTINGER_DEPLOY_PATH` secret is correct
- Check path exists on server: `ls -la /home/u123456789/domains/`

### Issue: Composer not found
**Solution:**
- Install Composer on server:
  ```bash
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
  ```

### Issue: Database connection fails
**Solution:**
- Verify database credentials in `.env`
- Check database exists in Hostinger
- Ensure database user has proper permissions

---

## 📞 Need Help?

1. Check GitHub Actions logs (click on failed workflow)
2. Check server logs: `storage/logs/laravel.log`
3. Verify all secrets are set correctly
4. Test SSH connection manually:
   ```bash
   ssh u123456789@your-server-ip -p 65002
   ```

---

**Good luck! 🚀**

