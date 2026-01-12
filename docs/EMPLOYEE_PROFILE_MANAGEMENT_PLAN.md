# Employee Profile Management - Implementation Plan

## 🎯 User Stories

1. **As a Super Admin, I want to create an employee profile (department, designation) so their access is correctly scoped from day one.**

2. **As an admin, I want to manage employee status (active/suspended) so access can be controlled centrally.**

---

## 📊 Database Schema

### **Option 1: Add Fields to Users Table** (Simple, Recommended)

Add columns to existing `users` table:

```sql
ALTER TABLE users ADD COLUMN department VARCHAR(100) NULL;
ALTER TABLE users ADD COLUMN designation VARCHAR(100) NULL;
ALTER TABLE users ADD COLUMN employee_status ENUM('active', 'suspended', 'inactive') DEFAULT 'active';
ALTER TABLE users ADD COLUMN joined_date DATE NULL;
```

**Pros:**
- Simple to implement
- Easy queries
- No extra joins needed

**Cons:**
- Users table might get large
- Limited flexibility for complex scenarios

---

### **Option 2: Create Employee Profiles Table** (Flexible)

Create separate `employee_profiles` table:

```sql
CREATE TABLE employee_profiles (
    id BIGINT PRIMARY KEY,
    user_id BIGINT UNIQUE NOT NULL,
    department VARCHAR(100) NULL,
    designation VARCHAR(100) NULL,
    employee_status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
    joined_date DATE NULL,
    manager_id BIGINT NULL, -- For reporting structure
    employee_number VARCHAR(50) UNIQUE NULL,
    phone VARCHAR(20) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL
);
```

**Pros:**
- More flexible
- Can add more fields later
- Keeps users table clean
- Supports reporting structure

**Cons:**
- More complex queries (joins)
- More tables to manage

---

## 🎯 Recommended Approach: **Option 1 (Simple Start)**

Start with adding fields to users table. Can migrate to separate table later if needed.

---

## 🔐 Access Control

### **Status-Based Access:**

```php
// Middleware or in controllers
if ($user->employee_status === 'suspended') {
    abort(403, 'Your account is suspended');
}

if ($user->employee_status === 'inactive') {
    abort(403, 'Your account is inactive');
}
```

### **Department-Based Access:**

```php
// Example: Sales department sees only Sales deals
if ($user->department === 'Sales') {
    $deals = Deal::where('department', 'Sales')->get();
} else {
    $deals = Deal::all(); // Admin/Super Admin see all
}
```

---

## 📋 Implementation Plan

### **Phase 1: Database & Models**

1. **Migration:**
   - Add `department` (nullable string)
   - Add `designation` (nullable string)
   - Add `employee_status` (enum: active, suspended, inactive)
   - Add `joined_date` (nullable date)

2. **Update User Model:**
   - Add fields to `$fillable`
   - Add casts
   - Add helper methods:
     - `isActive()` - Check if employee_status === 'active'
     - `isSuspended()` - Check if suspended
     - `getDepartment()` - Get department name

---

### **Phase 2: Employee Creation Form**

**Super Admin can specify:**
- First Name
- Last Name
- Email
- Password
- Role (super_admin, admin, user)
- **Department** (Sales, Dev, Design, Accounts, HR, etc.)
- **Designation** (Manager, Developer, Designer, Accountant, etc.)
- **Joined Date** (optional)

**Status:** Automatically set to 'active' on creation

---

### **Phase 3: Employee Management**

**Super Admin can:**
- View all employees
- Create employees (with department/designation)
- Edit employee profiles (department, designation)
- Manage employee status (active/suspended/inactive)

**Admin can:**
- View all employees
- Create employees (with department/designation)
- Edit employee profiles (department, designation)
- **Manage employee status** (active/suspended) ✅
- Cannot change role to super_admin

---

### **Phase 4: Status Management UI**

**Employee List View:**
- Show employee status badge
- Filter by status (All, Active, Suspended, Inactive)
- Quick action: Suspend/Activate button

**Employee Detail/Edit View:**
- Status dropdown (Active/Suspended/Inactive)
- Status change reason (optional)
- Status change history (audit log)

---

### **Phase 5: Access Control Middleware**

Create middleware to check employee status:

```php
// app/Http/Middleware/CheckEmployeeStatus.php
public function handle($request, Closure $next)
{
    $user = auth()->user();
    
    if ($user->is_metatech_employee) {
        if ($user->employee_status === 'suspended') {
            return redirect('/internal/suspended');
        }
        
        if ($user->employee_status === 'inactive') {
            return redirect('/internal/inactive');
        }
    }
    
    return $next($request);
}
```

---

## 🎨 UI/UX Design

### **Create Employee Form:**

```
┌─────────────────────────────────────┐
│ Create Internal Employee            │
├─────────────────────────────────────┤
│ First Name *      Last Name *       │
│ Email *                             │
│ Password *       Confirm Password * │
│ Role: [Super Admin ▼]               │
│ Department: [Sales ▼]               │
│ Designation: [Manager ▼]            │
│ Joined Date: [Date Picker]          │
│                                     │
│ [Cancel]  [Create Employee]         │
└─────────────────────────────────────┘
```

### **Employee List:**

```
┌─────────────────────────────────────────────────────────┐
│ Employees                    [Status: All ▼] [+ New]    │
├─────────────────────────────────────────────────────────┤
│ Name       │ Email        │ Dept │ Status   │ Actions  │
│─────────────────────────────────────────────────────────│
│ John Doe   │ john@...     │ Sales│ 🟢 Active│ [Edit]   │
│ Jane Smith │ jane@...     │ Dev  │ 🔴 Susp. │ [Edit]   │
└─────────────────────────────────────────────────────────┘
```

### **Employee Edit/Status Management:**

```
┌─────────────────────────────────────┐
│ Edit Employee: John Doe             │
├─────────────────────────────────────┤
│ Department: [Sales ▼]               │
│ Designation: [Manager ▼]            │
│ Status: [Active ▼]                  │
│   - Active                          │
│   - Suspended                       │
│   - Inactive                        │
│ Reason (if suspended):              │
│ [Text area...]                      │
│                                     │
│ [Cancel]  [Save Changes]            │
└─────────────────────────────────────┘
```

---

## 📊 Department & Designation Options

### **Departments:**
- Sales
- Development
- Design
- Accounts
- HR
- Marketing
- Operations
- Support

### **Designations (can vary by department):**

**Sales:**
- Sales Manager
- Sales Executive
- Sales Representative

**Development:**
- Tech Lead
- Senior Developer
- Developer
- Junior Developer

**Design:**
- Design Lead
- Senior Designer
- Designer

**Accounts:**
- Finance Manager
- Accountant
- Accounts Executive

**HR:**
- HR Manager
- HR Executive
- HR Assistant

---

## 🔐 Permission Matrix

| Action | Super Admin | Admin | User |
|--------|-------------|-------|------|
| Create Employee | ✅ (All fields) | ✅ (Can't set super_admin) | ❌ |
| Edit Employee Profile | ✅ (All fields) | ✅ (Can't change to super_admin) | ❌ |
| Manage Status | ✅ (All statuses) | ✅ (Active/Suspended only) | ❌ |
| View Employees | ✅ (All) | ✅ (All) | ❌ |
| View Own Profile | ✅ | ✅ | ✅ |

---

## 🔄 Status Management Flow

### **Active → Suspended:**
1. Admin/Super Admin changes status to "Suspended"
2. User gets email notification (optional)
3. On next login, user sees "Account Suspended" page
4. Cannot access any internal CRM features

### **Suspended → Active:**
1. Admin/Super Admin changes status to "Active"
2. User gets email notification (optional)
3. User can login normally

### **Active → Inactive:**
1. Super Admin only can set to "Inactive"
2. Used for employees who left the company
3. Cannot login
4. Data preserved for records

---

## 💾 Database Changes

### **Migration:**

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('department')->nullable()->after('is_metatech_employee');
    $table->string('designation')->nullable()->after('department');
    $table->enum('employee_status', ['active', 'suspended', 'inactive'])
          ->default('active')
          ->after('status');
    $table->date('joined_date')->nullable()->after('employee_status');
});
```

**Note:** We already have `status` field (active, blocked, suspended). We can:
- **Option A:** Use existing `status` field (but it's used for Product Owner blocking)
- **Option B:** Add new `employee_status` field (recommended for clarity)

---

## 🚀 Implementation Steps

### **Step 1: Database Migration** (30 min)
1. Create migration for employee profile fields
2. Run migration
3. Update User model

### **Step 2: Update Employee Creation** (1 hour)
1. Add department/designation fields to form
2. Update InternalEmployeeService
3. Update validation rules

### **Step 3: Employee List/Management** (2 hours)
1. Create employee list view
2. Add filters (status, department)
3. Add edit functionality

### **Step 4: Status Management** (1 hour)
1. Add status dropdown in edit form
2. Update service to handle status changes
3. Add status change logging

### **Step 5: Access Control** (1 hour)
1. Create middleware for status check
2. Update routes
3. Create suspended/inactive pages

---

## 📝 Questions to Answer

1. **Status Field Conflict:**
   - We have `status` (active, blocked, suspended) for Product Owner blocking
   - Should we use `employee_status` for internal employees?
   - Or merge them?

2. **Department List:**
   - Fixed list or custom entries?
   - Should it be a table or enum?

3. **Designation:**
   - Free text or predefined list?
   - Should it be department-specific?

4. **Status Change Notification:**
   - Send email when status changes?
   - Who gets notified?

5. **Reporting Structure:**
   - Do we need manager assignment now?
   - Or add later?

---

## ✅ Recommended Implementation Order

1. ✅ **Database Migration** - Add fields to users table
2. ✅ **Update Employee Creation** - Add department/designation
3. ✅ **Employee List View** - Show all employees with filters
4. ✅ **Employee Edit/Status Management** - Admin can change status
5. ✅ **Access Control Middleware** - Block suspended/inactive users

---

## 🎯 Let's Discuss

**Which approach do you prefer?**

1. **Simple Start:** Add fields to users table, basic status management
2. **Flexible:** Create employee_profiles table, more features

**For Department/Designation:**
1. **Fixed List:** Predefined options (dropdown)
2. **Free Text:** Users can type anything

**For Status:**
1. **Use existing `status` field:** Merge with current blocking system
2. **New `employee_status` field:** Separate field for clarity

Let me know your preferences and I'll implement it! 🚀

