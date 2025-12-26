# Internal Metatech CRM Setup Guide

## Overview

The internal Metatech CRM is accessible at `crm.metatech.ae` (no subdomain) and is for Metatech employees only.

## How to Access

### 1. For Development (localhost)

Access via: `http://localhost:8000/internal/login`

Or configure your hosts file:
```
127.0.0.1 crm.localhost
```

Then access: `http://crm.localhost:8000/internal/login`

### 2. For Production

Access via: `https://crm.metatech.ae/internal/login`

## Creating Metatech Employee Users

### Option 1: Via Database (Quick Method)

Run this SQL to create a Metatech employee:

```sql
INSERT INTO users (name, email, password, role, first_name, last_name, company_name, subdomain, is_metatech_employee, created_at, updated_at)
VALUES (
    'John Employee',
    'employee@metatech.ae',
    '$2y$12$YourHashedPasswordHere',  -- Use bcrypt to hash your password
    'admin',  -- or 'user' or 'super_admin'
    'John',
    'Employee',
    NULL,
    NULL,
    1,  -- This marks them as Metatech employee
    NOW(),
    NOW()
);
```

### Option 2: Via PHP/Tinker

```php
php artisan tinker

$user = new \App\Models\User();
$user->email = 'employee@metatech.ae';
$user->password = \Illuminate\Support\Facades\Hash::make('YourPassword123!');
$user->first_name = 'John';
$user->last_name = 'Employee';
$user->name = 'John Employee';
$user->role = 'admin'; // or 'user' or 'super_admin'
$user->is_metatech_employee = true;
$user->company_name = null;
$user->subdomain = null;
$user->save();
```

### Option 3: Update Existing User

```php
php artisan tinker

$user = \App\Models\User::where('email', 'existing@email.com')->first();
$user->is_metatech_employee = true;
$user->company_name = null;  // Make sure these are null
$user->subdomain = null;     // Make sure these are null
$user->save();
```

## User Requirements

For a user to login at `crm.metatech.ae`, they must have:

- ✅ `is_metatech_employee = true`
- ✅ `company_name = NULL`
- ✅ `subdomain = NULL`
- ✅ Valid email and password
- ✅ Any role (admin, user, super_admin)

## Routes

- **Login Page**: `/internal/login` (GET)
- **Login Handler**: `/internal/login` (POST)
- **Dashboard**: `/internal/dashboard` (GET) - Protected
- **Logout**: `/internal/logout` (POST)

## Authentication

- Uses session-based authentication
- Only users with `is_metatech_employee = true` can login
- Subdomain verification ensures users can only access their designated system

## Next Steps

1. Create a Metatech employee user using one of the methods above
2. Access `http://localhost:8000/internal/login` (or `crm.metatech.ae/internal/login` in production)
3. Login with the employee credentials
4. You'll be redirected to `/internal/dashboard`

## Differences from Other Systems

| System | URL | Users | Access Control |
|--------|-----|-------|----------------|
| Product Owner | `admincrm.metatech.ae` | Product Owner (Super Admin, no company) | `isProductOwner() == true` |
| Internal CRM | `crm.metatech.ae` | Metatech Employees | `is_metatech_employee == true` |
| Client CRM | `<client>.crm.metatech.ae` | Client Company Users | `subdomain == <client>` |

