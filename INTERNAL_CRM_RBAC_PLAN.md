# Internal CRM Role-Based Access Control (RBAC) - Implementation Plan

## 🎯 Overview

Following **Notion's approach** - clean, intuitive, role-based access control for the Internal CRM (`crm.metatech.ae`).

---

## 👥 Role Structure

### **1. Internal Super Admin**
- **Full system access**
- Manage all employees (create, edit, delete)
- Manage roles and permissions
- Access all features
- System settings and configuration

### **2. Internal Admin**
- **Department/Team management**
- Manage team members (create, edit)
- Access all operational features
- Cannot manage Super Admins
- Cannot change system settings

### **3. Internal User** (Sales, Dev, Design, Accounts, HR, etc.)
- **Work-focused access**
- Manage own deals/projects/tasks
- View assigned items
- Collaborate with team
- Limited admin features

---

## 🎨 Notion-Style Permission Approach

### **Key Principles:**
1. **Role-based sidebar** - Different menu items per role
2. **Progressive disclosure** - Show only what user can access
3. **Contextual actions** - Actions appear based on permissions
4. **Smooth UX** - No "Access Denied" errors, just hide unavailable features

---

## 📋 Feature Matrix

| Feature | Super Admin | Admin | User |
|---------|-------------|-------|------|
| **Dashboard** | ✅ Full | ✅ Full | ✅ Limited |
| **Deals** | ✅ All | ✅ All | ✅ Own + Team |
| **Projects** | ✅ All | ✅ All | ✅ Assigned |
| **Tasks** | ✅ All | ✅ All | ✅ Assigned |
| **Clients** | ✅ All | ✅ All | ✅ Assigned |
| **Team Management** | ✅ Full (All roles) | ✅ Limited (Users only) | ❌ |
| **Create Employees** | ✅ Yes | ✅ Yes | ❌ |
| **Manage Roles** | ✅ Yes | ❌ | ❌ |
| **System Settings** | ✅ Yes | ❌ | ❌ |
| **Reports/Analytics** | ✅ All | ✅ All | ✅ Limited |
| **User Management** | ✅ Block/Unblock | ✅ Block/Unblock | ❌ |

---

## 🏗️ Implementation Strategy

### **Phase 1: Permission System Foundation**

#### **1.1 Create Permission Enum/Constants**

```php
// app/Enums/InternalPermission.php
enum InternalPermission: string
{
    // User Management
    case MANAGE_USERS = 'manage_users';
    case CREATE_EMPLOYEES = 'create_employees';
    case EDIT_EMPLOYEES = 'edit_employees';
    case DELETE_EMPLOYEES = 'delete_employees';
    case MANAGE_ROLES = 'manage_roles';
    
    // Deals
    case VIEW_ALL_DEALS = 'view_all_deals';
    case CREATE_DEALS = 'create_deals';
    case EDIT_ALL_DEALS = 'edit_all_deals';
    case DELETE_DEALS = 'delete_deals';
    
    // Projects
    case VIEW_ALL_PROJECTS = 'view_all_projects';
    case CREATE_PROJECTS = 'create_projects';
    case EDIT_ALL_PROJECTS = 'edit_all_projects';
    
    // Clients
    case VIEW_ALL_CLIENTS = 'view_all_clients';
    case MANAGE_CLIENTS = 'manage_clients';
    
    // Settings
    case MANAGE_SETTINGS = 'manage_settings';
    
    // Reports
    case VIEW_ALL_REPORTS = 'view_all_reports';
}
```

#### **1.2 Role-Permission Mapping**

```php
// app/Services/PermissionService.php
class PermissionService
{
    public function getRolePermissions(string $role): array
    {
        return match($role) {
            'super_admin' => [
                // All permissions
                InternalPermission::cases(),
            ],
            'admin' => [
                InternalPermission::VIEW_ALL_DEALS,
                InternalPermission::CREATE_DEALS,
                InternalPermission::EDIT_ALL_DEALS,
                InternalPermission::VIEW_ALL_PROJECTS,
                InternalPermission::CREATE_PROJECTS,
                InternalPermission::VIEW_ALL_CLIENTS,
                InternalPermission::MANAGE_CLIENTS,
                InternalPermission::CREATE_EMPLOYEES,
                InternalPermission::EDIT_EMPLOYEES,
                InternalPermission::VIEW_ALL_REPORTS,
                // ... more
            ],
            'user' => [
                InternalPermission::CREATE_DEALS,
                InternalPermission::VIEW_ALL_DEALS, // Own + assigned
                InternalPermission::CREATE_PROJECTS, // If assigned
                // ... limited
            ],
            default => [],
        };
    }
    
    public function hasPermission(User $user, InternalPermission $permission): bool
    {
        $permissions = $this->getRolePermissions($user->role);
        return in_array($permission, $permissions);
    }
}
```

#### **1.3 Update User Model**

```php
// Add to User model
public function hasInternalPermission(InternalPermission $permission): bool
{
    if (!$this->is_metatech_employee) {
        return false;
    }
    
    return app(PermissionService::class)->hasPermission($this, $permission);
}

public function can(string $permission): bool
{
    return $this->hasInternalPermission(
        InternalPermission::from($permission)
    );
}
```

---

### **Phase 2: Middleware & Route Protection**

#### **2.1 Create Permission Middleware**

```php
// app/Http/Middleware/CheckInternalPermission.php
class CheckInternalPermission
{
    public function handle($request, Closure $next, string $permission)
    {
        $user = auth()->user();
        
        if (!$user || !$user->is_metatech_employee) {
            abort(403, 'Access denied');
        }
        
        if (!$user->can($permission)) {
            abort(403, 'You do not have permission to access this resource');
        }
        
        return $next($request);
    }
}
```

#### **2.2 Update Routes**

```php
// routes/web.php (Internal CRM routes)
Route::prefix('internal')->middleware(['auth', 'subdomain.verify'])->group(function () {
    Route::get('/dashboard', [InternalDashboardController::class, 'index']);
    
    // User Management (Super Admin & Admin only)
    Route::middleware('permission:create_employees')->group(function () {
        Route::get('/employees', [InternalEmployeeViewController::class, 'index']);
        Route::post('/employees', [InternalEmployeeController::class, 'create']);
    });
    
    // Deals (All can access, permissions checked in controller)
    Route::prefix('deals')->group(function () {
        Route::get('/', [DealController::class, 'index']);
        Route::post('/', [DealController::class, 'store'])->middleware('permission:create_deals');
    });
    
    // Settings (Super Admin only)
    Route::middleware('permission:manage_settings')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index']);
    });
});
```

---

### **Phase 3: UI - Notion-Style Sidebar**

#### **3.1 Dynamic Sidebar Component**

```blade
{{-- resources/views/components/internal-sidebar.blade.php --}}
<nav class="sidebar">
    @if(auth()->user()->can('manage_users'))
        <a href="/internal/employees" class="nav-item">
            <svg>...</svg>
            Team
        </a>
    @endif
    
    @if(auth()->user()->can('view_all_deals'))
        <a href="/internal/deals" class="nav-item">
            <svg>...</svg>
            Deals
        </a>
    @endif
    
    @if(auth()->user()->can('view_all_projects'))
        <a href="/internal/projects" class="nav-item">
            <svg>...</svg>
            Projects
        </a>
    @endif
    
    @if(auth()->user()->can('manage_settings'))
        <a href="/internal/settings" class="nav-item">
            <svg>...</svg>
            Settings
        </a>
    @endif
</nav>
```

#### **3.2 Conditional UI Elements**

```blade
{{-- In any view --}}
@if(auth()->user()->can('create_deals'))
    <button onclick="createDeal()">New Deal</button>
@endif

@if(auth()->user()->can('edit_all_deals'))
    <button onclick="editDeal()">Edit</button>
@elseif($deal->assigned_to === auth()->id())
    <button onclick="editDeal()">Edit</button>
@endif
```

---

### **Phase 4: Controller-Level Permissions**

#### **4.1 Base Controller with Permission Checks**

```php
// app/Http/Controllers/Internal/BaseController.php
abstract class BaseController extends Controller
{
    protected function authorizePermission(InternalPermission $permission): void
    {
        if (!auth()->user()->hasInternalPermission($permission)) {
            abort(403, 'Insufficient permissions');
        }
    }
    
    protected function filterByRole($query)
    {
        $user = auth()->user();
        
        if ($user->role === 'user') {
            // Users only see assigned items
            return $query->where('assigned_to', $user->id)
                        ->orWhere('created_by', $user->id);
        }
        
        // Admin & Super Admin see all
        return $query;
    }
}
```

#### **4.2 Example: Deal Controller**

```php
class DealController extends BaseController
{
    public function index()
    {
        $this->authorizePermission(InternalPermission::VIEW_ALL_DEALS);
        
        $deals = Deal::query();
        
        // Filter based on role
        if (auth()->user()->role === 'user') {
            $deals = $deals->where('assigned_to', auth()->id())
                          ->orWhere('created_by', auth()->id());
        }
        
        return view('internal.deals.index', ['deals' => $deals->get()]);
    }
    
    public function store(Request $request)
    {
        $this->authorizePermission(InternalPermission::CREATE_DEALS);
        
        // Create deal...
    }
}
```

---

## 🎨 Notion-Inspired UI Structure

### **Dashboard Layout:**

```
┌─────────────────────────────────────────────┐
│  Header (User info, logout)                │
├──────────┬──────────────────────────────────┤
│          │                                  │
│ Sidebar  │  Main Content Area              │
│ (Role-   │                                  │
│  based)  │  - Deals                         │
│          │  - Projects                      │
│ - Team   │  - Tasks                         │
│ - Deals  │  - Clients                       │
│ - Projects│  - Reports                      │
│ - Tasks  │                                  │
│ - Clients│                                  │
│ - Reports│                                  │
│ - Settings│                                 │
│          │                                  │
└──────────┴──────────────────────────────────┘
```

---

## 📊 Feature Breakdown

### **1. Deals Management**
- **Super Admin/Admin:** View all deals, create, edit, delete
- **User:** View assigned deals, create deals, edit own deals
- **Permissions:**
  - `view_all_deals`
  - `create_deals`
  - `edit_all_deals`
  - `delete_deals`

### **2. Projects**
- **Super Admin/Admin:** Full access to all projects
- **User:** View assigned projects, create if assigned
- **Permissions:**
  - `view_all_projects`
  - `create_projects`
  - `edit_all_projects`

### **3. Tasks** (Already implemented, add permissions)
- **All roles:** CRUD on tasks (already works)
- **Add filtering:** Users see only assigned tasks
- **Permissions:**
  - `view_all_tasks`
  - `create_tasks`
  - `edit_all_tasks`

### **4. Clients**
- **Super Admin/Admin:** Full client management
- **User:** View assigned clients
- **Permissions:**
  - `view_all_clients`
  - `manage_clients`

### **5. Team Management**
- **Super Admin:** Manage all employees (all roles)
- **Admin:** Manage users only (cannot manage admins/super admins)
- **User:** No access
- **Permissions:**
  - `create_employees`
  - `edit_employees`
  - `delete_employees`
  - `manage_roles` (Super Admin only)

---

## 🔒 Security Considerations

### **1. Server-Side Validation**
- Always check permissions in controllers
- Never trust client-side checks alone
- Use middleware for route protection

### **2. Data Filtering**
- Users see only their assigned data
- Admins see department/team data
- Super Admins see everything

### **3. Action Restrictions**
- Hide UI elements user can't use
- Show friendly messages instead of errors
- Log permission violations

---

## 🚀 Implementation Steps

### **Step 1: Create Permission System** (1-2 hours)
1. Create `InternalPermission` enum
2. Create `PermissionService`
3. Add methods to User model

### **Step 2: Middleware & Routes** (1 hour)
1. Create `CheckInternalPermission` middleware
2. Register middleware
3. Protect routes

### **Step 3: Update Controllers** (2-3 hours)
1. Add permission checks to controllers
2. Filter data based on role
3. Update API responses

### **Step 4: UI Updates** (3-4 hours)
1. Create dynamic sidebar component
2. Add conditional UI elements
3. Update dashboard views
4. Add role-based navigation

### **Step 5: Testing** (1-2 hours)
1. Test each role's access
2. Verify data filtering
3. Test permission checks

---

## 💡 Questions to Consider

1. **Department-based permissions?** (e.g., Sales can only see Sales deals?)
2. **Team/Project-based access?** (Users see only their team's projects?)
3. **Custom roles?** (Do you need custom role creation, or fixed roles are enough?)
4. **Audit logging?** (Track who accessed what?)

---

## 🎯 Recommended Approach

**Start Simple, Expand Later:**

1. **Phase 1:** Basic role-based permissions (Super Admin, Admin, User)
2. **Phase 2:** Add feature-specific permissions
3. **Phase 3:** Add department/team filtering
4. **Phase 4:** Add custom roles (if needed)

Would you like me to:
1. **Start implementing** this permission system?
2. **Create the permission enum and service first?**
3. **Build the dynamic sidebar component?**
4. **Or discuss any specific part in more detail?**

Let me know how you'd like to proceed! 🚀

