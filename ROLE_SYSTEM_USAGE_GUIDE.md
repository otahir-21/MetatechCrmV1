# Role System Usage Guide

## Overview

The role system uses **Spatie Laravel Permission** with a hybrid approach:
- **Primary**: Spatie roles/permissions for all access control
- **Secondary**: Enum `role` field for backward compatibility

---

## Setup

### 1. Run the Seeder

First, seed the database with all roles and permissions:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

Or include it in the main seeder:

```bash
php artisan db:seed
```

---

## Role Names

### Metatech Employee Roles
- `metatech.super_admin` - Full system access
- `metatech.admin` - Administrative access
- `metatech.executive` - Executive level access
- `metatech.sales` - Sales team
- `metatech.accounts` - Accounts/Finance
- `metatech.hr` - Human Resources
- `metatech.design` - Design team
- `metatech.development` - Development team
- `metatech.marketing` - Marketing team

### Client Roles
- `client.owner` - Company owner (full company access)
- `client.admin` - Company administrator
- `client.staff` - Regular company staff

---

## Usage Examples

### Assign Role When Creating User

#### Option 1: Using RoleService (Recommended)

```php
use App\Services\RoleService;

$user = User::create([
    'email' => 'user@example.com',
    'name' => 'John Doe',
    'is_metatech_employee' => true,
    'role' => 'admin', // Enum field for backward compatibility
    // ... other fields
]);

$roleService = app(RoleService::class);

// Assign specific role
$roleService->assignRole($user, 'metatech.sales');

// OR assign based on enum role (auto-maps)
$roleService->assignRoleFromEnum($user, 'admin', 'metatech.sales');
```

#### Option 2: Direct Spatie Assignment

```php
$user = User::create([...]);

// Assign role directly
$user->assignRole('metatech.sales');

// Update enum field for backward compatibility
$user->update(['role' => 'admin']);
```

### Check User Roles

```php
// Check Metatech role
if ($user->hasMetatechRole('sales')) {
    // User is in Sales
}

// Check Client role
if ($user->hasClientRole('owner')) {
    // User is Client Owner
}

// Check multiple roles
if ($user->hasAnyMetatechRole(['admin', 'super_admin'])) {
    // User is admin or super admin
}

// Get role name
$roleName = $user->getRoleName(); // Returns 'sales' (without prefix)
$fullRole = $user->getFullRoleName(); // Returns 'metatech.sales'
```

### Check Permissions

```php
// Check specific permission
if ($user->hasPermissionTo('manage.users')) {
    // User can manage users
}

// Check multiple permissions
if ($user->hasAnyPermission(['view.projects', 'create.projects'])) {
    // User can view or create projects
}

// Check all permissions
if ($user->hasAllPermissions(['view.projects', 'create.projects'])) {
    // User can view AND create projects
}
```

### In Controllers

```php
use Illuminate\Http\Request;

public function index(Request $request)
{
    $user = $request->user();
    
    // Check role
    if (!$user->hasMetatechRole('admin')) {
        abort(403, 'Unauthorized');
    }
    
    // Check permission
    if (!$user->hasPermissionTo('view.users')) {
        abort(403, 'You do not have permission to view users');
    }
    
    // ... rest of controller logic
}
```

### In Middleware

#### Using Route Middleware

```php
// routes/web.php
Route::middleware(['role:metatech.admin|metatech.super_admin'])->group(function () {
    Route::get('/admin/users', [UserController::class, 'index']);
});

// OR using permission
Route::middleware(['permission:manage.users'])->group(function () {
    Route::post('/admin/users', [UserController::class, 'store']);
});
```

#### Custom Middleware

```php
// app/Http/Middleware/CheckRole.php
public function handle($request, Closure $next, $role)
{
    $user = $request->user();
    
    if (!$user->hasMetatechRole($role)) {
        abort(403);
    }
    
    return $next($request);
}
```

### In Blade Templates

```blade
@can('manage.users')
    <a href="/admin/users/create">Create User</a>
@endcan

@role('metatech.admin')
    <div>Admin Dashboard</div>
@endrole

@hasanyrole('metatech.admin|metatech.super_admin')
    <div>Admin Content</div>
@endhasanyrole
```

### Get Available Roles

```php
use App\Services\RoleService;

$roleService = app(RoleService::class);

// Get available roles for Metatech employees
$metatechRoles = $roleService->getAvailableRoles(true);

// Get available roles for Client users
$clientRoles = $roleService->getAvailableRoles(false);

// Get role display name
$displayName = $roleService->getRoleDisplayName('metatech.sales');
// Returns: "Sales"
```

---

## Migration Strategy

### For Existing Users

When migrating existing users, assign roles:

```php
use App\Services\RoleService;

$roleService = app(RoleService::class);

User::chunk(100, function ($users) use ($roleService) {
    foreach ($users as $user) {
        // Assign role based on existing enum role
        $roleService->assignRoleFromEnum($user, $user->role);
    }
});
```

### Update Existing Code

Replace enum checks with Spatie checks gradually:

**Before:**
```php
if ($user->role === 'admin') {
    // ...
}
```

**After:**
```php
if ($user->hasMetatechRole('admin') || $user->hasPermissionTo('manage.users')) {
    // ...
}
```

---

## Permissions Reference

### Common Permissions

- `view.dashboard` - View dashboard
- `view.users` - View users list
- `create.users` - Create users
- `edit.users` - Edit users
- `delete.users` - Delete users
- `manage.users` - Full user management (combined)
- `block.users` - Block/unblock users
- `invite.users` - Invite users

- `view.projects` - View projects
- `create.projects` - Create projects
- `edit.projects` - Edit projects
- `delete.projects` - Delete projects
- `manage.projects` - Full project management (combined)

- `view.tasks` - View tasks
- `create.tasks` - Create tasks
- `edit.tasks` - Edit tasks
- `delete.tasks` - Delete tasks
- `manage.tasks` - Full task management (combined)
- `assign.tasks` - Assign tasks to users

- `view.reports` - View reports
- `export.reports` - Export reports
- `view.analytics` - View analytics

- `view.financial` - View financial data
- `manage.financial` - Manage financial data
- `view.invoices` - View invoices
- `create.invoices` - Create invoices

- `view.settings` - View settings
- `edit.settings` - Edit settings
- `manage.settings` - Full settings management (combined)

- `view.audit.logs` - View audit logs
- `view.roles` - View roles
- `assign.roles` - Assign roles
- `manage.roles` - Full role management (combined)

---

## Best Practices

1. **Always assign Spatie roles** when creating users
2. **Keep enum field updated** for backward compatibility
3. **Use permissions** instead of role checks when possible (more flexible)
4. **Use helper methods** (`hasMetatechRole`, `hasClientRole`) for cleaner code
5. **Cache permissions** - Spatie handles this automatically
6. **Test permissions** thoroughly before deploying

---

## Troubleshooting

### Role not found error

Make sure the seeder has been run:
```bash
php artisan db:seed --class=RolePermissionSeeder
```

### Permission check fails

1. Check if user has the role assigned
2. Check if the role has the permission
3. Clear cache: `php artisan permission:cache-reset`

### Role assignment not working

1. Make sure `HasRoles` trait is used in User model
2. Check database tables exist (`roles`, `model_has_roles`)
3. Clear cache: `php artisan config:clear && php artisan cache:clear`

