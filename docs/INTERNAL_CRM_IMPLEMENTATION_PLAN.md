# Internal CRM (crm.localhost) - Implementation Plan

## 🎯 Current Status

**Internal CRM** (`crm.localhost`) is currently a **basic dashboard** for Metatech employees with:
- ✅ Basic welcome page
- ✅ User info display
- ✅ System status
- ❌ **No staff invitation feature**
- ❌ **No project management feature**

---

## 📋 Implementation Plan

### **Goal:**
Enable internal Metatech employees to:
1. **Invite other internal employees** to the system
2. **Create and manage internal projects**
3. **Control project access** for internal team members

---

## 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│  INTERNAL CRM (crm.localhost)                              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  USER TYPE: Internal Metatech Employees                    │
│  - is_metatech_employee = true                             │
│  - company_name = NULL                                      │
│  - subdomain = NULL                                         │
│                                                             │
│  FEATURES TO ADD:                                           │
│  1. Internal Staff Invitation System                       │
│  2. Internal Project Management                            │
│  3. Project Access Control                                  │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 📝 Phase-by-Phase Implementation

### **PHASE 1: Internal Staff Invitation System**

#### **1.1 Database Changes**
- ✅ **No new tables needed** - Can reuse `staff_invitations` table
- ⚠️ **Modification needed**: Update `staff_invitations` to support internal employees
  - Add `is_internal` flag OR
  - Use `company_id = NULL` to indicate internal invitation

#### **1.2 Service Layer**
**File:** `app/Services/InternalStaffInvitationService.php` (NEW)

**Methods:**
- `inviteInternalStaff(array $data, User $invitedBy): StaffInvitation`
  - Verify inviter is internal employee
  - Create invitation with `company_id = NULL`
  - Set `is_internal = true` or use NULL company_id
  
- `acceptInternalInvitation(string $token, array $userData): User`
  - Accept invitation
  - Create user with `is_metatech_employee = true`
  - Set `company_name = NULL`, `subdomain = NULL`

- `getInternalInvitations(User $invitedBy)`
  - Get all internal invitations

- `cancelInternalInvitation(int $invitationId, User $cancelledBy)`
  - Cancel internal invitation

#### **1.3 Controller**
**File:** `app/Http/Controllers/Api/V1/InternalStaffInvitationController.php` (NEW)

**Endpoints:**
- `POST /api/v1/internal/staff/invite` - Invite internal employee
- `GET /api/v1/internal/staff/invitations` - List invitations
- `POST /api/v1/internal/staff/invitations/{token}/accept` - Accept invitation
- `DELETE /api/v1/internal/staff/invitations/{id}` - Cancel invitation

#### **1.4 Routes**
**File:** `routes/api.php`

```php
// Internal Staff Invitation endpoints (Internal Employees only)
Route::prefix('internal/staff')->middleware(['auth:api', 'subdomain.verify'])->group(function () {
    Route::post('/invite', [InternalStaffInvitationController::class, 'invite']);
    Route::get('/invitations', [InternalStaffInvitationController::class, 'index']);
    Route::delete('/invitations/{id}', [InternalStaffInvitationController::class, 'destroy']);
});

// Public acceptance endpoint
Route::post('/internal/staff/invitations/{token}/accept', [InternalStaffInvitationController::class, 'accept']);
```

**File:** `routes/web.php`

```php
// Internal staff invitation acceptance page
Route::get('/internal/accept-invitation/{token}', [InternalStaffInvitationViewController::class, 'showAccept']);
Route::post('/internal/accept-invitation/{token}', [InternalStaffInvitationViewController::class, 'accept']);
```

#### **1.5 UI Components**
**File:** `resources/views/internal/dashboard.blade.php` (UPDATE)

**Add Sections:**
- Staff Invitations section
- Invite Internal Employee modal
- Invitations list table

---

### **PHASE 2: Internal Project Management**

#### **2.1 Database Changes**
- ✅ **No new tables needed** - Can reuse `projects` table
- ⚠️ **Modification needed**: 
  - Add `is_internal` flag to `projects` table OR
  - Use `company_id = NULL` to indicate internal project

#### **2.2 Service Layer**
**File:** `app/Services/InternalProjectService.php` (NEW)

**Methods:**
- `createInternalProject(array $data, User $createdBy): Project`
  - Verify creator is internal employee
  - Create project with `company_id = NULL` or `is_internal = true`
  
- `getInternalProjects(User $user)`
  - Get all internal projects user has access to

- `updateInternalProject(int $projectId, array $data, User $user): Project`
  - Update internal project

- `deleteInternalProject(int $projectId, User $user)`
  - Delete internal project

#### **2.3 Controller**
**File:** `app/Http/Controllers/Api/V1/InternalProjectController.php` (NEW)

**Endpoints:**
- `GET /api/v1/internal/projects` - List internal projects
- `POST /api/v1/internal/projects` - Create internal project
- `GET /api/v1/internal/projects/{id}` - Get project details
- `PUT /api/v1/internal/projects/{id}` - Update project
- `DELETE /api/v1/internal/projects/{id}` - Delete project

#### **2.4 Routes**
**File:** `routes/api.php`

```php
// Internal Projects endpoints (Internal Employees only)
Route::prefix('internal/projects')->middleware(['auth:api', 'subdomain.verify'])->group(function () {
    Route::get('/', [InternalProjectController::class, 'index']);
    Route::post('/', [InternalProjectController::class, 'store']);
    Route::get('/{id}', [InternalProjectController::class, 'show']);
    Route::put('/{id}', [InternalProjectController::class, 'update']);
    Route::delete('/{id}', [InternalProjectController::class, 'destroy']);
});
```

#### **2.5 UI Components**
**File:** `resources/views/internal/dashboard.blade.php` (UPDATE)

**Add Sections:**
- Projects section
- Create Project modal
- Projects list/grid
- Project detail view

---

### **PHASE 3: Internal Project Access Control**

#### **3.1 Service Layer**
**File:** `app/Services/InternalProjectAccessService.php` (NEW)

**Methods:**
- `grantInternalProjectAccess(int $projectId, int $userId, string $accessLevel, User $grantedBy)`
  - Grant access to internal project
  - Verify both users are internal employees
  
- `revokeInternalProjectAccess(int $projectId, int $userId, User $revokedBy)`
  - Revoke access from internal project

- `getInternalProjectUsers(int $projectId)`
  - Get all users with access to internal project

- `canUserAccessInternalProject(User $user, int $projectId): bool`
  - Check if user can access internal project

#### **3.2 Controller**
**File:** `app/Http/Controllers/Api/V1/InternalProjectAccessController.php` (NEW)

**Endpoints:**
- `POST /api/v1/internal/projects/{id}/grant-access` - Grant access
- `DELETE /api/v1/internal/projects/{id}/revoke-access/{userId}` - Revoke access
- `GET /api/v1/internal/projects/{id}/users` - List project users

#### **3.3 Routes**
**File:** `routes/api.php`

```php
// Internal Project Access Management
Route::prefix('internal/projects')->middleware(['auth:api', 'subdomain.verify'])->group(function () {
    Route::post('/{id}/grant-access', [InternalProjectAccessController::class, 'grant']);
    Route::delete('/{id}/revoke-access/{userId}', [InternalProjectAccessController::class, 'revoke']);
    Route::get('/{id}/users', [InternalProjectAccessController::class, 'users']);
});
```

#### **3.4 UI Components**
**File:** `resources/views/internal/dashboard.blade.php` (UPDATE)

**Add Features:**
- Project detail modal with user list
- Grant/revoke access buttons
- User selection dropdown
- Access level selector

---

## 🗄️ Database Schema Changes

### **Option 1: Use NULL company_id (Recommended)**
- ✅ No migration needed
- ✅ Reuse existing `projects` table
- ✅ Reuse existing `project_user` table
- ⚠️ Need to filter by `company_id IS NULL` for internal projects

### **Option 2: Add is_internal flag**
**Migration:** `add_is_internal_to_projects_table.php`

```php
Schema::table('projects', function (Blueprint $table) {
    $table->boolean('is_internal')->default(false)->after('company_id');
});
```

**Migration:** `add_is_internal_to_staff_invitations_table.php`

```php
Schema::table('staff_invitations', function (Blueprint $table) {
    $table->boolean('is_internal')->default(false)->after('company_id');
    $table->nullable()->change('company_id'); // Make company_id nullable
});
```

**Recommendation:** Use **Option 1** (NULL company_id) - simpler, no migrations needed.

---

## 📊 Updated Internal Dashboard Structure

```
┌─────────────────────────────────────────────────────────────┐
│  INTERNAL CRM DASHBOARD                                      │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  LEFT SIDEBAR:                                              │
│  ├─ Dashboard (Overview)                                    │
│  ├─ Projects                                                │
│  ├─ Staff Invitations                                       │
│  └─ Team Members                                            │
│                                                             │
│  MAIN CONTENT:                                              │
│  ├─ Projects Section:                                       │
│  │  ├─ Create Project button                               │
│  │  ├─ Projects grid/list                                  │
│  │  └─ Project cards with access info                      │
│  │                                                          │
│  ├─ Staff Invitations Section:                             │
│  │  ├─ Invite Staff button                                 │
│  │  ├─ Invitations table                                   │
│  │  └─ Status indicators                                   │
│  │                                                          │
│  └─ Team Members Section:                                  │
│     ├─ All internal employees list                         │
│     ├─ Their project access                                │
│     └─ Quick actions                                        │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔐 Access Control Rules

### **Internal Employee Permissions:**

**Who can invite:**
- ✅ Any internal employee (with proper role check)
- OR: Only admins/super_admins (configurable)

**Who can create projects:**
- ✅ Any internal employee
- OR: Only admins/super_admins (configurable)

**Who can grant project access:**
- ✅ Project creator (admin access)
- ✅ Internal admins/super_admins
- ✅ Project admins (if they have admin access to project)

**Project Access Levels:**
- `viewer` - Can view project
- `editor` - Can edit project
- `admin` - Can manage project and grant access

---

## 📋 Implementation Checklist

### **Phase 1: Internal Staff Invitations**
- [ ] Create `InternalStaffInvitationService`
- [ ] Create `InternalStaffInvitationController` (API)
- [ ] Create `InternalStaffInvitationViewController` (Web)
- [ ] Add API routes for internal staff invitations
- [ ] Add web routes for invitation acceptance
- [ ] Update `staff_invitations` table (make company_id nullable if needed)
- [ ] Create email template for internal invitations
- [ ] Update internal dashboard UI with invitations section
- [ ] Test invitation flow

### **Phase 2: Internal Projects**
- [ ] Create `InternalProjectService`
- [ ] Create `InternalProjectController` (API)
- [ ] Add API routes for internal projects
- [ ] Update `projects` table (make company_id nullable if needed)
- [ ] Update internal dashboard UI with projects section
- [ ] Test project creation

### **Phase 3: Internal Project Access**
- [ ] Create `InternalProjectAccessService`
- [ ] Create `InternalProjectAccessController` (API)
- [ ] Add API routes for project access management
- [ ] Update internal dashboard UI with access management
- [ ] Test access grant/revoke

---

## 🎨 UI/UX Design

### **Internal Dashboard Layout:**

```
┌─────────────────────────────────────────────────────────────┐
│  Header: Metatech Internal CRM | User Name | Logout        │
├──────────────┬──────────────────────────────────────────────┤
│              │                                              │
│  SIDEBAR     │  MAIN CONTENT                                │
│              │                                              │
│  • Dashboard │  [Selected Section Content]                 │
│  • Projects  │                                              │
│  • Staff     │                                              │
│    Invites   │                                              │
│  • Team      │                                              │
│              │                                              │
└──────────────┴──────────────────────────────────────────────┘
```

### **Key UI Components:**

1. **Projects Section:**
   - Grid view of project cards
   - Each card shows: Name, Description, Access Level, Actions
   - "Create Project" button (top right)
   - Click card → View details & manage access

2. **Staff Invitations Section:**
   - Table with: Email, Role, Status, Expires, Actions
   - "Invite Staff" button (top right)
   - Status badges (pending, accepted, cancelled)
   - Cancel button for pending invitations

3. **Team Members Section:**
   - List of all internal employees
   - Shows their roles
   - Shows projects they have access to
   - Quick actions per user

---

## 🔄 Data Flow

### **Staff Invitation Flow:**
```
Internal Employee → Invite Staff
    ↓
Create Invitation (company_id = NULL)
    ↓
Send Email
    ↓
Staff Clicks Link
    ↓
Accept Invitation Form
    ↓
Create User (is_metatech_employee = true)
    ↓
User Can Login
```

### **Project Access Flow:**
```
Internal Employee → Create Project
    ↓
Project Created (company_id = NULL)
    ↓
Creator Gets Admin Access
    ↓
Grant Access to Other Internal Employees
    ↓
Staff Members See Project in Their List
```

---

## 🧪 Testing Plan

### **Phase 1 Testing:**
1. Login as internal employee
2. Invite another internal employee
3. Check email/log for invitation
4. Accept invitation as new user
5. Verify user created with correct flags
6. Login with new user

### **Phase 2 Testing:**
1. Create internal project
2. Verify project created with company_id = NULL
3. List projects - should see created project
4. Update project
5. Delete project

### **Phase 3 Testing:**
1. Create project
2. Grant access to another internal employee
3. Login as that employee
4. Verify they can see the project
5. Verify access level is correct
6. Revoke access
7. Verify they can't see project anymore

---

## 📝 Files to Create/Modify

### **New Files:**
1. `app/Services/InternalStaffInvitationService.php`
2. `app/Services/InternalProjectService.php`
3. `app/Services/InternalProjectAccessService.php`
4. `app/Http/Controllers/Api/V1/InternalStaffInvitationController.php`
5. `app/Http/Controllers/Api/V1/InternalProjectController.php`
6. `app/Http/Controllers/Api/V1/InternalProjectAccessController.php`
7. `app/Http/Controllers/InternalStaffInvitationViewController.php`
8. `app/Http/Requests/InternalStaffInvitationCreateRequest.php`
9. `app/Mail/InternalStaffInvitationMail.php`
10. `resources/views/internal/staff-invitation-accept.blade.php`
11. `resources/views/emails/internal-staff-invitation.blade.php`

### **Files to Modify:**
1. `resources/views/internal/dashboard.blade.php` - Add all new sections
2. `routes/api.php` - Add internal API routes
3. `routes/web.php` - Add internal web routes
4. `database/migrations/` - Optional: Make company_id nullable if needed

---

## ⚠️ Important Considerations

1. **Separation of Concerns:**
   - Internal projects should NOT mix with company projects
   - Use `company_id IS NULL` to filter internal projects
   - Internal invitations should NOT mix with company invitations

2. **Access Control:**
   - Verify users are internal employees in all services
   - Check `is_metatech_employee = true` AND `company_name IS NULL`

3. **Email Configuration:**
   - Internal invitations should use `crm.localhost` URLs
   - Company invitations use `{subdomain}.localhost` URLs

4. **Subdomain Verification:**
   - All internal routes must verify user is on `crm.localhost`
   - Middleware should check `is_metatech_employee = true`

---

## 🚀 Implementation Order

**Recommended Order:**
1. **Phase 1** (Staff Invitations) - Foundation
2. **Phase 2** (Projects) - Core feature
3. **Phase 3** (Access Control) - Enhancement

**Estimated Time:**
- Phase 1: 2-3 hours
- Phase 2: 2-3 hours
- Phase 3: 2-3 hours
- **Total: 6-9 hours**

---

## ✅ Ready to Start?

This plan provides:
- ✅ Clear architecture
- ✅ Detailed implementation steps
- ✅ Database considerations
- ✅ UI/UX design
- ✅ Testing plan
- ✅ File structure

**Should I proceed with implementation?** I can start with Phase 1 (Internal Staff Invitations) and work through each phase systematically.

