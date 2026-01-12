# Examples: Updating User Creation Code

This document shows how to update existing user creation code to use Spatie roles.

---

## Example 1: Create Product Owner (Bootstrap)

**File**: `app/Services/BootstrapService.php`

**Before:**
```php
$user = User::create([
    'email' => strtolower(trim($data['email'])),
    'password' => Hash::make($data['password']),
    'first_name' => trim($data['first_name']),
    'last_name' => trim($data['last_name']),
    'name' => trim($data['first_name']) . ' ' . trim($data['last_name']),
    'role' => 'super_admin',
    'email_verified_at' => null,
]);
```

**After:**
```php
use App\Services\RoleService;

$user = User::create([
    'email' => strtolower(trim($data['email'])),
    'password' => Hash::make($data['password']),
    'first_name' => trim($data['first_name']),
    'last_name' => trim($data['last_name']),
    'name' => trim($data['first_name']) . ' ' . trim($data['last_name']),
    'role' => 'super_admin', // Keep for backward compatibility
    'email_verified_at' => null,
    'is_metatech_employee' => false, // Product Owner is not Metatech employee
]);

// Assign Spatie role (Product Owner doesn't get a Spatie role, they're system-level)
// Or create a special role for them if needed
```

---

## Example 2: Create Internal Employee

**File**: `app/Services/InternalEmployeeService.php` or similar

**Before:**
```php
$user = User::create([
    'email' => $data['email'],
    'password' => Hash::make($data['password']),
    'first_name' => $data['first_name'],
    'last_name' => $data['last_name'],
    'role' => $data['role'], // e.g., 'admin'
    'is_metatech_employee' => true,
    'department' => $data['department'],
    // ...
]);
```

**After:**
```php
use App\Services\RoleService;

$roleService = app(RoleService::class);

$user = User::create([
    'email' => $data['email'],
    'password' => Hash::make($data['password']),
    'first_name' => $data['first_name'],
    'last_name' => $data['last_name'],
    'role' => $data['role'], // Keep for backward compatibility
    'is_metatech_employee' => true,
    'department' => $data['department'],
    // ...
]);

// Assign Spatie role
// If specific role is provided (e.g., 'metatech.sales')
if (isset($data['spatie_role'])) {
    $roleService->assignRole($user, $data['spatie_role']);
} else {
    // Auto-assign based on enum role
    $roleService->assignRoleFromEnum($user, $data['role']);
}
```

---

## Example 3: Create Company Owner

**File**: `app/Services/CompanyOwnerInvitationService.php`

**Before:**
```php
$user = User::create([
    'email' => $invitation->email,
    'password' => Hash::make($data['password']),
    'first_name' => $data['first_name'],
    'last_name' => $data['last_name'],
    'name' => $data['first_name'] . ' ' . $data['last_name'],
    'role' => 'super_admin',
    'company_name' => $company->company_name,
    'subdomain' => $company->subdomain,
    'is_metatech_employee' => false,
]);
```

**After:**
```php
use App\Services\RoleService;

$roleService = app(RoleService::class);

$user = User::create([
    'email' => $invitation->email,
    'password' => Hash::make($data['password']),
    'first_name' => $data['first_name'],
    'last_name' => $data['last_name'],
    'name' => $data['first_name'] . ' ' . $data['last_name'],
    'role' => 'super_admin', // Keep for backward compatibility
    'company_name' => $company->company_name,
    'subdomain' => $company->subdomain,
    'is_metatech_employee' => false,
]);

// Assign Client Owner role
$roleService->assignRole($user, 'client.owner');
```

---

## Example 4: Create Company Staff

**File**: `app/Services/StaffInvitationService.php` or similar

**Before:**
```php
$user = User::create([
    'email' => $data['email'],
    'password' => Hash::make($data['password']),
    'first_name' => $data['first_name'],
    'last_name' => $data['last_name'],
    'role' => $data['role'] ?? 'user',
    'company_name' => $company->company_name,
    'subdomain' => $company->subdomain,
    'is_metatech_employee' => false,
]);
```

**After:**
```php
use App\Services\RoleService;

$roleService = app(RoleService::class);

$user = User::create([
    'email' => $data['email'],
    'password' => Hash::make($data['password']),
    'first_name' => $data['first_name'],
    'last_name' => $data['last_name'],
    'role' => $data['role'] ?? 'user', // Keep for backward compatibility
    'company_name' => $company->company_name,
    'subdomain' => $company->subdomain,
    'is_metatech_employee' => false,
]);

// Assign client role based on invitation role or default to staff
$clientRole = match($data['role'] ?? 'user') {
    'super_admin' => 'client.owner',
    'admin' => 'client.admin',
    default => 'client.staff',
};

$roleService->assignRole($user, $clientRole);
```

---

## Example 5: Update User Role

**File**: Any service that updates user roles

**Before:**
```php
$user->update(['role' => 'admin']);
```

**After:**
```php
use App\Services\RoleService;

$roleService = app(RoleService::class);

// Remove existing roles
$user->roles()->detach();

// Assign new role
$roleService->assignRole($user, 'metatech.admin'); // or 'client.admin'

// Update enum field for backward compatibility
$user->update(['role' => 'admin']);
```

---

## Example 6: Bulk Role Assignment for Existing Users

**File**: Create a command `app/Console/Commands/AssignRolesToExistingUsers.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\RoleService;
use Illuminate\Console\Command;

class AssignRolesToExistingUsers extends Command
{
    protected $signature = 'users:assign-roles';
    protected $description = 'Assign Spatie roles to existing users based on their enum role';

    public function handle()
    {
        $roleService = app(RoleService::class);
        $count = 0;

        User::chunk(100, function ($users) use ($roleService, &$count) {
            foreach ($users as $user) {
                // Skip if user already has a role
                if ($user->roles->count() > 0) {
                    continue;
                }

                // Assign role based on enum role
                $roleService->assignRoleFromEnum($user, $user->role);
                $count++;
            }
        });

        $this->info("Assigned roles to {$count} users.");
    }
}
```

Run with:
```bash
php artisan users:assign-roles
```

---

## Key Points

1. **Always assign Spatie role** after creating user
2. **Keep enum field** for backward compatibility
3. **Use RoleService** for consistent role assignment
4. **Match enum to Spatie role** appropriately:
   - `super_admin` enum → `metatech.super_admin` OR `client.owner`
   - `admin` enum → `metatech.admin` OR `client.admin`
   - `user` enum → specific role OR `client.staff`

