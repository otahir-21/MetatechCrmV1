# Internal Dashboard Testing Guide

## ✅ Setup Status

- ✅ Route registered: `/internal/dashboard`
- ✅ Controller exists: `InternalDashboardController`
- ✅ View file exists: `resources/views/internal/dashboard.blade.php`
- ✅ Internal employees found in database

## 🧪 How to Test Internal Dashboard

### **Step 1: Verify You Have an Internal Employee Account**

Run this command to check:
```bash
php artisan tinker
```

Then:
```php
$employees = \App\Models\User::where('is_metatech_employee', true)
    ->whereNull('company_name')
    ->whereNull('subdomain')
    ->get();

foreach ($employees as $emp) {
    echo "Email: {$emp->email}\n";
    echo "Role: {$emp->role}\n";
    echo "Is Metatech Employee: " . ($emp->is_metatech_employee ? 'Yes' : 'No') . "\n\n";
}
```

**Current Internal Employees:**
- `employee@metatech.ae` (Role: admin)
- `superadmin@metatech.ae` (Role: super_admin) ⚠️ Note: This might be Product Owner

---

### **Step 2: Access the Login Page**

1. Open your browser and navigate to:
   ```
   http://crm.localhost:8000/login
   ```
   
   **Important:** You MUST use `crm.localhost:8000` (not `admincrm.localhost` or company subdomain)

2. You should see the internal login form

---

### **Step 3: Login as Internal Employee**

1. Enter your internal employee credentials:
   - Email: `employee@metatech.ae` (or your internal employee email)
   - Password: (your password)

2. Click "Login"

3. **You should be automatically redirected to:**
   ```
   http://crm.localhost:8000/internal/dashboard
   ```

---

### **Step 4: Verify Dashboard Access**

After login, you should see:
- The internal dashboard page
- Welcome message with your email
- Dashboard content for Metatech employees

---

## 🔍 Troubleshooting

### **Issue: Getting 403 Forbidden**

**Possible Causes:**
1. User is not marked as internal employee
2. User has `company_name` or `subdomain` set (should be NULL)
3. User is Product Owner (Product Owner cannot access internal dashboard)

**Solution:**
```sql
-- Check user in database
SELECT id, email, is_metatech_employee, company_name, subdomain, role 
FROM users 
WHERE email = 'employee@metatech.ae';

-- Fix if needed (should have):
-- is_metatech_employee = 1 (or true)
-- company_name = NULL
-- subdomain = NULL
```

---

### **Issue: Getting 404 Not Found**

**Possible Causes:**
1. Route not registered
2. Wrong URL

**Solution:**
```bash
# Check route exists
php artisan route:list | grep internal.dashboard

# Should show:
# GET|HEAD  internal/dashboard
```

---

### **Issue: Redirecting to Wrong Dashboard**

**Possible Causes:**
1. Login controller logic issue
2. User type detection issue

**Check the login redirect logic in:**
`app/Http/Controllers/Auth/WebLoginController.php`

The logic should be:
- If user is Company Super Admin → `/company-dashboard`
- If user is Internal Employee (not Product Owner) → `/internal/dashboard`
- If user is Product Owner → `/dashboard`

---

### **Issue: Can't Login on crm.localhost**

**Possible Causes:**
1. DNS/hosts file not configured
2. Server not running on correct host

**Solution:**
1. Make sure `php artisan serve` is running
2. Make sure you're using `http://crm.localhost:8000` (not `localhost:8000`)
3. Check your `/etc/hosts` file (Mac/Linux) or `C:\Windows\System32\drivers\etc\hosts` (Windows):
   ```
   127.0.0.1   crm.localhost
   127.0.0.1   admincrm.localhost
   ```

---

## 🧪 Quick Test Script

Run this to verify everything is set up correctly:

```bash
php artisan tinker
```

Then:
```php
// Find internal employee
$emp = \App\Models\User::where('is_metatech_employee', true)
    ->whereNull('company_name')
    ->whereNull('subdomain')
    ->where('email', 'employee@metatech.ae')
    ->first();

if ($emp) {
    echo "✅ Internal Employee Found:\n";
    echo "   Email: {$emp->email}\n";
    echo "   Role: {$emp->role}\n";
    echo "   Is Metatech Employee: " . ($emp->is_metatech_employee ? 'Yes' : 'No') . "\n";
    echo "   Company Name: " . ($emp->company_name ?? 'NULL ✓') . "\n";
    echo "   Subdomain: " . ($emp->subdomain ?? 'NULL ✓') . "\n\n";
    
    echo "🌐 Login URL:\n";
    echo "   http://crm.localhost:8000/login\n\n";
    
    echo "📋 Dashboard URL (after login):\n";
    echo "   http://crm.localhost:8000/internal/dashboard\n";
} else {
    echo "❌ No internal employee found with email: employee@metatech.ae\n";
    echo "Create one via Product Owner dashboard first.\n";
}
```

---

## 📋 Test Checklist

- [ ] Internal employee exists in database
- [ ] Internal employee has `is_metatech_employee = true`
- [ ] Internal employee has `company_name = NULL`
- [ ] Internal employee has `subdomain = NULL`
- [ ] Can access `http://crm.localhost:8000/login`
- [ ] Can login with internal employee credentials
- [ ] Gets redirected to `/internal/dashboard` after login
- [ ] Dashboard page loads without errors
- [ ] Can see dashboard content

---

## 🔗 Related URLs

- **Login:** `http://crm.localhost:8000/login`
- **Dashboard:** `http://crm.localhost:8000/internal/dashboard`
- **Product Owner Dashboard:** `http://admincrm.localhost:8000/dashboard`
- **Company Dashboard:** `http://{subdomain}.localhost:8000/company-dashboard`

---

## 💡 Notes

- Internal employees can ONLY access `crm.localhost:8000` (not `admincrm.localhost`)
- Internal employees should NOT have `company_name` or `subdomain` set
- If `superadmin@metatech.ae` is Product Owner, it cannot access internal dashboard
- Create separate internal employee accounts via Product Owner dashboard

---

Happy Testing! 🎉

