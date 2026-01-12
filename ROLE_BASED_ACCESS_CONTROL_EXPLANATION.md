# Role-Based Access Control & User Management System - Implementation Guide

This document explains how to implement the role-based system, blocking/unblocking functionality, and client owner staff invitation system.

---

## 📋 **Part 1: Block/Unblock Users & Companies (Product Owner Powers)**

### **1.1 Database Schema Changes**

#### **A. Add Status Field to Users Table**
```php
// Migration: add_status_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->enum('status', ['active', 'blocked', 'suspended'])->default('active')->after('role');
    $table->text('status_reason')->nullable(); // Reason for blocking
    $table->timestamp('blocked_at')->nullable(); // When blocked
    $table->foreignId('blocked_by')->nullable()->constrained('users'); // Who blocked
});
```

#### **B. Add Status Field to Companies**
Since companies are stored in the `users` table (where `company_name` is NOT NULL), you can use the same `status` field. However, you might want a separate `companies` table for better organization:

```php
// Migration: create_companies_table.php
Schema::create('companies', function (Blueprint $table) {
    $table->id();
    $table->string('company_name')->unique();
    $table->string('subdomain')->unique();
    $table->foreignId('company_super_admin_id')->constrained('users'); // Link to user
    $table->enum('status', ['active', 'blocked', 'suspended', 'trial'])->default('active');
    $table->text('status_reason')->nullable();
    $table->timestamp('blocked_at')->nullable();
    $table->foreignId('blocked_by')->nullable()->constrained('users');
    $table->json('subscription_details')->nullable();
    $table->timestamps();
});
```

### **1.2 Implementation Steps**

#### **Step 1: Create Middleware to Check User/Company Status**
```php
// app/Http/Middleware/CheckUserStatus.php
public function handle(Request $request, Closure $next)
{
    if (auth()->check()) {
        $user = auth()->user();
        
        // Check if user is blocked
        if ($user->status === 'blocked' || $user->status === 'suspended') {
            Auth::logout();
            return redirect('/login')->with('error', 'Your account has been blocked.');
        }
        
        // If user belongs to a company, check company status
        if ($user->company_name) {
            $company = Company::where('subdomain', $user->subdomain)->first();
            if ($company && in_array($company->status, ['blocked', 'suspended'])) {
                Auth::logout();
                return redirect('/login')->with('error', 'Your company account has been blocked.');
            }
        }
    }
    
    return $next($request);
}
```

#### **Step 2: Create Service for Block/Unblock Operations**
```php
// app/Services/UserManagementService.php
class UserManagementService
{
    public function blockUser(int $userId, User $blockedBy, ?string $reason = null): bool
    {
        $user = User::findOrFail($userId);
        
        // Cannot block Product Owner
        if ($user->isProductOwner()) {
            throw new \Exception('Cannot block Product Owner');
        }
        
        $user->status = 'blocked';
        $user->status_reason = $reason;
        $user->blocked_at = now();
        $user->blocked_by = $blockedBy->id;
        $user->save();
        
        // Optionally: Invalidate all user sessions/tokens
        // Optionally: Send notification email
        
        return true;
    }
    
    public function unblockUser(int $userId): bool
    {
        $user = User::findOrFail($userId);
        $user->status = 'active';
        $user->status_reason = null;
        $user->blocked_at = null;
        $user->blocked_by = null;
        $user->save();
        
        return true;
    }
    
    public function blockCompany(int $companyId, User $blockedBy, ?string $reason = null): bool
    {
        $company = Company::findOrFail($companyId);
        
        $company->status = 'blocked';
        $company->status_reason = $reason;
        $company->blocked_at = now();
        $company->blocked_by = $blockedBy->id;
        $company->save();
        
        // Optionally: Block all users in the company
        // User::where('subdomain', $company->subdomain)->update(['status' => 'blocked']);
        
        return true;
    }
    
    public function unblockCompany(int $companyId): bool
    {
        $company = Company::findOrFail($companyId);
        $company->status = 'active';
        $company->status_reason = null;
        $company->blocked_at = null;
        $company->blocked_by = null;
        $company->save();
        
        return true;
    }
}
```

#### **Step 3: Create API Endpoints (Product Owner Only)**
```php
// app/Http/Controllers/Api/V1/UserManagementController.php
Route::middleware(['auth:api', 'product.owner'])->group(function () {
    Route::post('/users/{id}/block', [UserManagementController::class, 'blockUser']);
    Route::post('/users/{id}/unblock', [UserManagementController::class, 'unblockUser']);
    Route::post('/companies/{id}/block', [UserManagementController::class, 'blockCompany']);
    Route::post('/companies/{id}/unblock', [UserManagementController::class, 'unblockCompany']);
});
```

---

## 📋 **Part 2: Role-Based System for Internal CRM**

### **2.1 Current Role Structure**
Your system already has:
- `super_admin` - Product Owner or Company Super Admin
- `admin` - Admin users
- `user` - Regular users

### **2.2 Enhanced Role System**

#### **Option A: Use Permissions Table (More Flexible)**
```php
// Migration: create_permissions_table.php
Schema::create('permissions', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique(); // 'create_user', 'edit_project', etc.
    $table->string('group'); // 'users', 'projects', 'settings'
    $table->text('description')->nullable();
});

// Migration: create_role_permissions_table.php (Many-to-Many)
Schema::create('role_permissions', function (Blueprint $table) {
    $table->id();
    $table->string('role'); // 'admin', 'project_manager', 'user'
    $table->foreignId('permission_id')->constrained('permissions');
});

// Migration: create_user_permissions_table.php (For custom user permissions)
Schema::create('user_permissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users');
    $table->foreignId('permission_id')->constrained('permissions');
});
```

#### **Option B: Use Laravel Spatie Permission Package (Recommended)**
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

Then in your User model:
```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    
    // Usage:
    // $user->assignRole('admin');
    // $user->hasRole('admin');
    // $user->hasPermissionTo('edit projects');
}
```

### **2.3 Create Role-Based Middleware**
```php
// app/Http/Middleware/CheckRole.php
public function handle(Request $request, Closure $next, ...$roles)
{
    if (!auth()->check()) {
        return redirect('/login');
    }
    
    $user = auth()->user();
    
    // Check if user has one of the required roles
    if (!$user->hasAnyRole($roles)) {
        abort(403, 'Unauthorized');
    }
    
    return $next($request);
}
```

### **2.4 Usage in Routes**
```php
Route::middleware(['auth', 'role:admin,super_admin'])->group(function () {
    // Only admins and super admins can access
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    // Only admins can access
});
```

---

## 📋 **Part 3: Client Owner Staff Invitation & Project-Based Access**

### **3.1 Database Schema**

#### **A. Staff Invitations Table**
```php
// Migration: create_staff_invitations_table.php
Schema::create('staff_invitations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained('companies'); // or users table
    $table->foreignId('invited_by')->constrained('users'); // Company Super Admin
    $table->string('email');
    $table->string('token', 64)->unique();
    $table->enum('role', ['admin', 'project_manager', 'user'])->default('user');
    $table->enum('status', ['pending', 'accepted', 'expired', 'cancelled'])->default('pending');
    $table->timestamp('expires_at');
    $table->timestamp('accepted_at')->nullable();
    $table->timestamps();
});
```

#### **B. Projects Table**
```php
// Migration: create_projects_table.php
Schema::create('projects', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained('companies'); // Which company owns this project
    $table->string('name');
    $table->text('description')->nullable();
    $table->enum('status', ['active', 'archived', 'completed'])->default('active');
    $table->foreignId('created_by')->constrained('users'); // Company Super Admin
    $table->timestamps();
});
```

#### **C. Project User Access (Many-to-Many)**
```php
// Migration: create_project_user_table.php
Schema::create('project_user', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->enum('access_level', ['viewer', 'editor', 'admin'])->default('viewer');
    $table->timestamp('granted_at')->useCurrent();
    $table->foreignId('granted_by')->constrained('users'); // Who granted access
    $table->unique(['project_id', 'user_id']); // Prevent duplicate access
});
```

#### **D. Update Users Table for Company Relationship**
```php
// Migration: add_company_id_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('company_id')->nullable()->constrained('companies')->after('subdomain');
    // OR if using users table for companies:
    // $table->foreignId('company_owner_id')->nullable()->constrained('users')->after('subdomain');
});
```

### **3.2 Implementation Steps**

#### **Step 1: Staff Invitation Service**
```php
// app/Services/StaffInvitationService.php
class StaffInvitationService
{
    public function inviteStaff(array $data, User $invitedBy): StaffInvitation
    {
        // Verify inviter is Company Super Admin
        if (!$invitedBy->isCompanySuperAdmin()) {
            throw new \Exception('Only Company Super Admin can invite staff');
        }
        
        // Get company
        $company = Company::where('subdomain', $invitedBy->subdomain)->firstOrFail();
        
        // Check if email already exists in company
        $existingUser = User::where('email', $data['email'])
            ->where('company_id', $company->id)
            ->first();
            
        if ($existingUser) {
            throw new \Exception('User already exists in this company');
        }
        
        // Create invitation
        $invitation = StaffInvitation::create([
            'company_id' => $company->id,
            'invited_by' => $invitedBy->id,
            'email' => $data['email'],
            'token' => Str::random(64),
            'role' => $data['role'] ?? 'user',
            'status' => 'pending',
            'expires_at' => now()->addDays(7), // Expires in 7 days
        ]);
        
        // Send invitation email
        Mail::to($data['email'])->send(new StaffInvitationMail($invitation));
        
        return $invitation;
    }
    
    public function acceptInvitation(string $token, array $userData): User
    {
        $invitation = StaffInvitation::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail();
        
        // Create user
        $user = User::create([
            'email' => $invitation->email,
            'password' => Hash::make($userData['password']),
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'name' => $userData['first_name'] . ' ' . $userData['last_name'],
            'role' => $invitation->role,
            'company_id' => $invitation->company_id,
            'company_name' => $invitation->company->company_name,
            'subdomain' => $invitation->company->subdomain,
            'status' => 'active',
        ]);
        
        // Mark invitation as accepted
        $invitation->status = 'accepted';
        $invitation->accepted_at = now();
        $invitation->save();
        
        return $user;
    }
}
```

#### **Step 2: Project Access Management Service**
```php
// app/Services/ProjectAccessService.php
class ProjectAccessService
{
    public function grantProjectAccess(int $projectId, int $userId, string $accessLevel, User $grantedBy): void
    {
        // Verify grantor is Company Super Admin or Project Admin
        $project = Project::findOrFail($projectId);
        
        if (!$grantedBy->isCompanySuperAdmin() && !$this->isProjectAdmin($grantedBy, $projectId)) {
            throw new \Exception('Unauthorized to grant project access');
        }
        
        // Verify user belongs to same company
        if ($project->company_id !== $grantedBy->company_id) {
            throw new \Exception('User must belong to the same company');
        }
        
        // Grant access
        ProjectUser::updateOrCreate(
            ['project_id' => $projectId, 'user_id' => $userId],
            [
                'access_level' => $accessLevel,
                'granted_by' => $grantedBy->id,
                'granted_at' => now(),
            ]
        );
    }
    
    public function revokeProjectAccess(int $projectId, int $userId): void
    {
        ProjectUser::where('project_id', $projectId)
            ->where('user_id', $userId)
            ->delete();
    }
    
    public function getUserProjects(User $user): Collection
    {
        return Project::where('company_id', $user->company_id)
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();
    }
    
    public function canUserAccessProject(User $user, int $projectId): bool
    {
        // Company Super Admin can access all projects
        if ($user->isCompanySuperAdmin()) {
            return Project::where('id', $projectId)
                ->where('company_id', $user->company_id)
                ->exists();
        }
        
        // Check if user has explicit access
        return ProjectUser::where('project_id', $projectId)
            ->where('user_id', $user->id)
            ->exists();
    }
}
```

#### **Step 3: Create Middleware for Project Access**
```php
// app/Http/Middleware/CheckProjectAccess.php
public function handle(Request $request, Closure $next)
{
    $projectId = $request->route('projectId') ?? $request->input('project_id');
    
    if (!$projectId) {
        abort(400, 'Project ID required');
    }
    
    $user = auth()->user();
    $projectAccessService = app(ProjectAccessService::class);
    
    if (!$projectAccessService->canUserAccessProject($user, $projectId)) {
        abort(403, 'You do not have access to this project');
    }
    
    return $next($request);
}
```

#### **Step 4: Create API Routes**
```php
// routes/api.php

// Staff Invitation (Company Super Admin only)
Route::middleware(['auth:api', 'subdomain.verify'])->group(function () {
    Route::post('/staff/invite', [StaffInvitationController::class, 'invite']);
    Route::get('/staff/invitations', [StaffInvitationController::class, 'list']);
    Route::post('/staff/invitations/{token}/accept', [StaffInvitationController::class, 'accept']);
    Route::delete('/staff/invitations/{id}', [StaffInvitationController::class, 'cancel']);
});

// Projects (Company Super Admin and Staff)
Route::middleware(['auth:api', 'subdomain.verify'])->group(function () {
    Route::get('/projects', [ProjectController::class, 'index']); // Filtered by user access
    Route::post('/projects', [ProjectController::class, 'create'])->middleware('role:super_admin');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])->middleware('project.access');
    Route::put('/projects/{id}', [ProjectController::class, 'update'])->middleware('project.access');
    
    // Project Access Management (Company Super Admin only)
    Route::post('/projects/{id}/grant-access', [ProjectAccessController::class, 'grant'])->middleware('role:super_admin');
    Route::delete('/projects/{id}/revoke-access/{userId}', [ProjectAccessController::class, 'revoke'])->middleware('role:super_admin');
});
```

---

## 📋 **Summary: Implementation Order**

1. **Phase 1: Block/Unblock System**
   - Add `status` fields to users/companies
   - Create `UserManagementService`
   - Create API endpoints for Product Owner
   - Add middleware to check status

2. **Phase 2: Role-Based Access (Internal CRM)**
   - Install Spatie Permission package OR create custom permissions system
   - Create role-based middleware
   - Apply roles to routes

3. **Phase 3: Staff Invitation System**
   - Create `staff_invitations` table
   - Create `StaffInvitationService`
   - Create invitation email template
   - Create API endpoints

4. **Phase 4: Project-Based Access**
   - Create `projects` and `project_user` tables
   - Create `ProjectAccessService`
   - Create project access middleware
   - Create API endpoints

---

## 🔐 **Security Considerations**

1. **Always verify ownership**: Company Super Admin can only manage their own company's staff/projects
2. **Use middleware**: Check access at route level, not just in controllers
3. **Audit logs**: Log all block/unblock actions, invitations, and access grants
4. **Token expiration**: Staff invitations should expire (7-14 days)
5. **Email verification**: Verify email before allowing staff to accept invitation
6. **Rate limiting**: Limit invitation sending to prevent abuse

---

Would you like me to start implementing any of these features?

