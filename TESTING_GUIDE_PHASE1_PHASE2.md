# Testing Guide: Phase 1 & Phase 2

## Prerequisites

1. Make sure you have:
   - Product Owner account (superadmin@productowner.com)
   - At least one Company Super Admin created
   - At least one Internal Employee created
   - JWT token for Product Owner

2. Start your Laravel server:
   ```bash
   php artisan serve
   ```

---

## 🔒 Phase 1: Block/Unblock System Testing

### Test 1.1: Block a User

**API Endpoint:**
```http
POST http://localhost:8000/api/v1/user-management/users/{user_id}/block
Authorization: Bearer {your_jwt_token}
Content-Type: application/json

{
  "reason": "Violation of terms of service"
}
```

**Using cURL:**
```bash
curl -X POST http://localhost:8000/api/v1/user-management/users/2/block \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Host: admincrm.localhost" \
  -d '{"reason": "Testing block functionality"}'
```

**Expected Response (200 OK):**
```json
{
  "message": "User blocked successfully",
  "data": {
    "user_id": 2
  }
}
```

**Verify in Database:**
```sql
SELECT id, email, status, status_reason, blocked_at, blocked_by 
FROM users 
WHERE id = 2;
```
- `status` should be `blocked`
- `status_reason` should contain your reason
- `blocked_at` should have a timestamp
- `blocked_by` should be the Product Owner's user ID

---

### Test 1.2: Try to Login with Blocked User

**Try to login at:**
```
POST http://localhost:8000/api/v1/auth/login
Content-Type: application/json

{
  "email": "blocked_user@example.com",
  "password": "password123"
}
```

**Expected Response (403 Forbidden):**
```json
{
  "message": "Your account has been blocked. Reason: Testing block functionality",
  "error_code": "ACCOUNT_BLOCKED"
}
```

**Or if using web login:**
- Should redirect to `/login` with error message

---

### Test 1.3: Unblock a User

**API Endpoint:**
```http
POST http://localhost:8000/api/v1/user-management/users/{user_id}/unblock
Authorization: Bearer {your_jwt_token}
```

**Using cURL:**
```bash
curl -X POST http://localhost:8000/api/v1/user-management/users/2/unblock \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Host: admincrm.localhost"
```

**Expected Response (200 OK):**
```json
{
  "message": "User unblocked successfully",
  "data": {
    "user_id": 2
  }
}
```

**Verify:**
- User can now login successfully
- Database: `status` = `active`, `blocked_at` = NULL, `blocked_by` = NULL

---

### Test 1.4: Block a Company

**API Endpoint:**
```http
POST http://localhost:8000/api/v1/user-management/companies/{company_id}/block
Authorization: Bearer {your_jwt_token}
Content-Type: application/json

{
  "reason": "Payment overdue"
}
```

**First, get company ID from companies list:**
```bash
curl -X GET http://localhost:8000/api/v1/company/ \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Host: admincrm.localhost"
```

**Then block the company:**
```bash
curl -X POST http://localhost:8000/api/v1/user-management/companies/1/block \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Host: admincrm.localhost" \
  -d '{"reason": "Payment overdue"}'
```

**Expected Response (200 OK):**
```json
{
  "message": "Company blocked successfully",
  "data": {
    "company_id": 1
  }
}
```

---

### Test 1.5: Try to Login as Company User (Blocked Company)

**Try to login with Company Super Admin credentials:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Host: company-subdomain.localhost" \
  -d '{
    "email": "company_admin@example.com",
    "password": "password123"
  }'
```

**Expected Response (403 Forbidden):**
```json
{
  "message": "Your company account has been blocked. Reason: Payment overdue",
  "error_code": "COMPANY_BLOCKED"
}
```

---

### Test 1.6: Unblock a Company

**API Endpoint:**
```http
POST http://localhost:8000/api/v1/user-management/companies/{company_id}/unblock
Authorization: Bearer {your_jwt_token}
```

```bash
curl -X POST http://localhost:8000/api/v1/user-management/companies/1/unblock \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Host: admincrm.localhost"
```

---

### Test 1.7: Cannot Block Product Owner

**Try to block the Product Owner:**
```bash
curl -X POST http://localhost:8000/api/v1/user-management/users/1/block \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Host: admincrm.localhost" \
  -d '{"reason": "Testing"}'
```

**Expected Response (403 Forbidden):**
```json
{
  "message": "Cannot block Product Owner",
  "error_code": "FORBIDDEN"
}
```

---

### Test 1.8: Non-Product Owner Cannot Block

**Try with Company Super Admin token:**
```bash
curl -X POST http://localhost:8000/api/v1/user-management/users/2/block \
  -H "Authorization: Bearer COMPANY_ADMIN_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Host: company-subdomain.localhost" \
  -d '{"reason": "Testing"}'
```

**Expected Response (403 Forbidden):**
```json
{
  "message": "Only Product Owner can block users",
  "error_code": "INSUFFICIENT_PERMISSIONS"
}
```

---

## 🎭 Phase 2: Role-Based Access Control Testing

### Setup: Assign Roles Using Tinker

First, let's assign roles to users using Spatie Permission:

```bash
php artisan tinker
```

```php
// Get the Product Owner
$productOwner = \App\Models\User::where('email', 'superadmin@productowner.com')->first();

// Get a company admin
$companyAdmin = \App\Models\User::whereNotNull('company_name')->first();

// Get an internal employee
$internalEmployee = \App\Models\User::where('is_metatech_employee', true)->first();

// Create roles if they don't exist
use Spatie\Permission\Models\Role;
Role::firstOrCreate(['name' => 'super_admin']);
Role::firstOrCreate(['name' => 'admin']);
Role::firstOrCreate(['name' => 'user']);
Role::firstOrCreate(['name' => 'project_manager']);

// Assign roles
$productOwner->assignRole('super_admin');
$companyAdmin->assignRole('super_admin'); // Company Super Admin
$internalEmployee->assignRole('admin'); // Internal employee admin

// Verify
$productOwner->hasRole('super_admin'); // Should return true
$internalEmployee->hasRole('admin'); // Should return true
```

---

### Test 2.1: Create Permission-Based Route

Let's create a test route that requires specific roles:

**Add to `routes/api.php`:**
```php
Route::prefix('v1')->middleware(['auth:api', 'subdomain.verify'])->group(function () {
    // Test route - only super_admin can access
    Route::get('/test/super-admin-only', function() {
        return response()->json(['message' => 'Access granted - Super Admin only']);
    })->middleware('role:super_admin');
    
    // Test route - admin or super_admin can access
    Route::get('/test/admin-or-super', function() {
        return response()->json(['message' => 'Access granted - Admin or Super Admin']);
    })->middleware('role:admin,super_admin');
});
```

**Register middleware in `bootstrap/app.php`:**
```php
$middleware->alias([
    'subdomain.verify' => \App\Http\Middleware\VerifySubdomainAccess::class,
    'role' => \App\Http\Middleware\CheckRole::class, // Add this
]);
```

---

### Test 2.2: Access Route with Correct Role

**As Super Admin (Product Owner):**
```bash
curl -X GET http://localhost:8000/api/v1/test/super-admin-only \
  -H "Authorization: Bearer PRODUCT_OWNER_JWT_TOKEN" \
  -H "Host: admincrm.localhost"
```

**Expected Response (200 OK):**
```json
{
  "message": "Access granted - Super Admin only"
}
```

---

### Test 2.3: Access Route with Wrong Role

**As Admin (Internal Employee):**
```bash
curl -X GET http://localhost:8000/api/v1/test/super-admin-only \
  -H "Authorization: Bearer INTERNAL_EMPLOYEE_JWT_TOKEN" \
  -H "Host: crm.localhost"
```

**Expected Response (403 Forbidden):**
```json
{
  "message": "You do not have permission to access this resource",
  "error_code": "INSUFFICIENT_PERMISSIONS"
}
```

---

### Test 2.4: Access Route with Multiple Allowed Roles

**As Admin (should have access to admin-or-super route):**
```bash
curl -X GET http://localhost:8000/api/v1/test/admin-or-super \
  -H "Authorization: Bearer INTERNAL_EMPLOYEE_JWT_TOKEN" \
  -H "Host: crm.localhost"
```

**Expected Response (200 OK):**
```json
{
  "message": "Access granted - Admin or Super Admin"
}
```

---

### Test 2.5: Check User Roles via Tinker

```bash
php artisan tinker
```

```php
$user = \App\Models\User::find(1); // Product Owner
$user->roles; // Should show roles
$user->hasRole('super_admin'); // Should return true

$user = \App\Models\User::where('is_metatech_employee', true)->first();
$user->hasRole('admin'); // Should return true
$user->hasRole('super_admin'); // Should return false
```

---

### Test 2.6: Assign Permission to Role

```php
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Create permission
$permission = Permission::firstOrCreate(['name' => 'manage users']);

// Assign permission to role
$role = Role::findByName('admin');
$role->givePermissionTo('manage users');

// Check if user has permission
$user = \App\Models\User::where('is_metatech_employee', true)->first();
$user->hasPermissionTo('manage users'); // Should return true
```

---

## 🔍 Quick Testing Checklist

### Phase 1 Checklist:
- [ ] Block a user successfully
- [ ] Blocked user cannot login
- [ ] Unblock a user successfully
- [ ] Unblocked user can login again
- [ ] Block a company successfully
- [ ] Company users cannot login when company is blocked
- [ ] Unblock a company successfully
- [ ] Cannot block Product Owner
- [ ] Non-Product Owner cannot block users

### Phase 2 Checklist:
- [ ] Created roles in database
- [ ] Assigned roles to users
- [ ] Route with role middleware works for correct role
- [ ] Route with role middleware denies access for wrong role
- [ ] Multiple roles work (admin or super_admin)
- [ ] Permissions can be created and assigned
- [ ] Users can check their permissions

---

## 🛠️ Helper Script: Get JWT Token

Create a file `get_token.php`:

```php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$email = $argv[1] ?? 'superadmin@productowner.com';
$password = $argv[2] ?? 'Admin123@'; // Change to your password

$user = \App\Models\User::where('email', $email)->first();

if (!$user || !\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
    echo "Invalid credentials\n";
    exit(1);
}

$token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
echo "Token for {$email}:\n";
echo $token . "\n";
```

**Usage:**
```bash
php get_token.php superadmin@productowner.com Admin123@
```

---

## 📝 Notes

1. **All API requests must include:**
   - `Authorization: Bearer {token}` header
   - `Host: admincrm.localhost` for Product Owner endpoints
   - `Host: crm.localhost` for Internal Employee endpoints
   - `Host: {subdomain}.localhost` for Company endpoints

2. **For web testing:**
   - Login via browser at `http://admincrm.localhost:8000/login`
   - Check browser console/network tab for API calls

3. **Database verification:**
   - Use phpMyAdmin or MySQL CLI to check `users` table status
   - Check `companies` table status
   - Check `model_has_roles` table for role assignments

