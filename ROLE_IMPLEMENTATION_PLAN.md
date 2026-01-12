# Role System Implementation Plan - Recommended Approach

## ✅ **RECOMMENDED: Spatie Permission (Hybrid Approach)**

### Why This Approach?
1. ✅ **Already installed** - Spatie Permission is already in your system
2. ✅ **Future-proof** - Industry standard, scales well
3. ✅ **Flexible** - Supports granular permissions, not just roles
4. ✅ **Backward compatible** - Keep enum field during migration
5. ✅ **Multi-tenant friendly** - Clean separation of Metatech vs Client roles

---

## Implementation Strategy

### **Phase 1: Create Roles & Permissions** (Seeder)

**Standard Metatech Roles:**
- `metatech.super_admin` - Full system access
- `metatech.admin` - Administrative access
- `metatech.executive` - Executive level access
- `metatech.sales` - Sales team access
- `metatech.accounts` - Accounts/finance access
- `metatech.hr` - Human resources access
- `metatech.design` - Design team access
- `metatech.development` - Development team access
- `metatech.marketing` - Marketing team access

**Client Roles:**
- `client.owner` - Company owner (full company access)
- `client.admin` - Company administrator
- `client.staff` - Regular company staff

**Permissions Examples:**
- `view.dashboard`
- `manage.users`
- `manage.projects`
- `manage.tasks`
- `view.reports`
- `manage.settings`
- `manage.companies` (Metatech only)
- `view.financial` (Accounts only)
- etc.

### **Phase 2: Migration Strategy**

1. **Create Seeder** - Define all roles and permissions
2. **Run Seeder** - Populate database with roles
3. **Assign Roles** - Update existing users to have Spatie roles
4. **Update Code** - Gradually replace enum checks with Spatie checks
5. **Keep Enum** - Maintain `role` field for backward compatibility (can remove later)

### **Phase 3: Helper Methods**

Add to `User` model:
- `hasMetatechRole($role)` - Check Metatech role
- `hasClientRole($role)` - Check Client role
- `getRoleName()` - Get role name without prefix
- Update existing methods to use Spatie

---

## Role Assignment Rules

### **Metatech Employees** (`is_metatech_employee = true`)
- Must have role starting with `metatech.`
- Examples: `metatech.sales`, `metatech.admin`

### **Client Users** (`is_metatech_employee = false`)
- Must have role starting with `client.`
- Examples: `client.owner`, `client.admin`, `client.staff`

---

## Usage Examples

### Assign Role
```php
$user->assignRole('metatech.sales');
$user->update(['role' => 'admin']); // Backward compatibility
```

### Check Permission
```php
if ($user->hasPermissionTo('manage.users')) {
    // Allow
}
```

### Check Role
```php
if ($user->hasRole('metatech.admin')) {
    // Allow
}
```

### Middleware
```php
Route::middleware(['role:metatech.admin|metatech.super_admin'])->group(function () {
    // Protected routes
});
```

---

## Benefits of This Approach

1. ✅ **Scalable** - Easy to add new roles/permissions
2. ✅ **Flexible** - Granular permission control
3. ✅ **Maintainable** - Standard Laravel pattern
4. ✅ **Type-safe** - Can use constants/enums for role names
5. ✅ **Cacheable** - Spatie caches permissions automatically
6. ✅ **Testable** - Easy to test permissions

---

## Next Steps

1. Create `RolePermissionSeeder`
2. Define all roles and permissions
3. Run seeder to populate database
4. Update user creation code to assign Spatie roles
5. Gradually migrate code from enum checks to Spatie checks

