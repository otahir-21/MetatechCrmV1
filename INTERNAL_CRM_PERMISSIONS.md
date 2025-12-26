# Internal CRM Permissions Guide

## 🔐 Permission Structure

### **Internal CRM Access Levels:**

1. **Internal Super Admin** (`role = 'super_admin'` AND `is_metatech_employee = true`)
   - Can create/manage internal employees
   - Can manage roles
   - Can invite users and admins
   - Full access to internal CRM features

2. **Internal Admin** (`role = 'admin'` AND `is_metatech_employee = true`)
   - Can create/manage internal employees
   - Can manage roles
   - Can invite users and admins
   - Full access to internal CRM features

3. **Internal User** (`role = 'user'` AND `is_metatech_employee = true`)
   - Limited access (to be defined based on requirements)

---

## ✅ Who Can Create Internal Employees?

The following users can create internal employees:

1. ✅ **Product Owner** (System Super Admin)
   - `role = 'super_admin'`
   - `is_metatech_employee = false`
   - `company_name = null`
   - `subdomain = null`

2. ✅ **Internal Super Admin**
   - `role = 'super_admin'`
   - `is_metatech_employee = true`

3. ✅ **Internal Admin**
   - `role = 'admin'`
   - `is_metatech_employee = true`

---

## 🔍 Check Your User's Permissions

To check if your current user can manage internal employees:

```php
// In Tinker or Controller
$user = auth()->user();

// Check if user is Internal Super Admin
$user->isInternalSuperAdmin(); // Returns true/false

// Check if user is Internal Admin
$user->isInternalAdmin(); // Returns true/false

// Check if user can manage internal employees
$user->canManageInternalEmployees(); // Returns true/false
```

---

## 📋 Required User Attributes

For a user to be able to create internal employees, they must have:

```php
// For Internal Super Admin:
$user->role = 'super_admin';
$user->is_metatech_employee = true;

// OR

// For Internal Admin:
$user->role = 'admin';
$user->is_metatech_employee = true;
```

---

## 🐛 Troubleshooting

### Error: "Only Product Owner, Internal Super Admin, or Internal Admin can create Internal Employees"

**Check:**
1. Is `is_metatech_employee = true`? 
2. Is `role = 'super_admin'` OR `role = 'admin'`?
3. Are you logged in on `crm.localhost:8000`?

**Verify in Database:**
```sql
SELECT id, email, role, is_metatech_employee, company_name, subdomain 
FROM users 
WHERE email = 'your-email@metatech.ae';
```

**Expected values:**
- `role` = 'super_admin' OR 'admin'
- `is_metatech_employee` = 1 (true)
- `company_name` = NULL
- `subdomain` = NULL

---

## 🎯 Dashboard Access

### Internal CRM Dashboard (`http://crm.localhost:8000/internal/dashboard`)

**Visible Features:**
- ✅ Manage Internal Employees
- ✅ Add Employee (Create new employees)
- ✅ User Management (Block/unblock users)
- ✅ Statistics
- ✅ Settings
- ❌ **Companies** (Removed - only for Product Owner)

---

## 📝 Next Steps

If you're still getting permission errors:

1. **Verify your user account:**
   ```bash
   php artisan tinker
   $user = App\Models\User::where('email', 'your-email@metatech.ae')->first();
   echo "Role: " . $user->role . PHP_EOL;
   echo "Is Metatech Employee: " . ($user->is_metatech_employee ? 'YES' : 'NO') . PHP_EOL;
   echo "Can Manage: " . ($user->canManageInternalEmployees() ? 'YES' : 'NO') . PHP_EOL;
   ```

2. **Update user if needed:**
   ```bash
   php artisan tinker
   $user = App\Models\User::where('email', 'your-email@metatech.ae')->first();
   $user->role = 'admin'; // or 'super_admin'
   $user->is_metatech_employee = true;
   $user->save();
   ```

3. **Clear caches:**
   ```bash
   php artisan route:clear
   php artisan config:clear
   php artisan view:clear
   ```

