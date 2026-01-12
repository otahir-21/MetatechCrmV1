# Role-Based Task Assignment System - Implementation Plan

## 🎯 Overview

**Question:** Can invitation roles be used for task assignment in projects?

**Answer:** **YES!** The roles assigned during invitation (`user`, `admin`, `project_manager`) can and should be used for task assignment and project permissions.

---

## 🔄 Current Role Flow

```
┌─────────────────────────────────────────────────────────────┐
│  ROLE ASSIGNMENT FLOW                                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. INVITATION PHASE                                        │
│     Client Owner invites staff with role:                   │
│     ├─ "user" → Basic user                                 │
│     ├─ "admin" → Admin user                                │
│     └─ "project_manager" → Project Manager                 │
│     ↓                                                       │
│  2. USER CREATION                                           │
│     User created with assigned role                         │
│     Role stored in: users.role                             │
│     ↓                                                       │
│  3. PROJECT ACCESS                                          │
│     Role can determine:                                     │
│     ├─ Default project access level                        │
│     ├─ Task assignment permissions                         │
│     └─ Project management capabilities                      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 Role-to-Permission Mapping

### **Role: `user`**
**Capabilities:**
- ✅ View assigned projects
- ✅ View assigned tasks
- ✅ Update own tasks
- ❌ Cannot assign tasks
- ❌ Cannot create projects
- ❌ Cannot manage project access

**Task Assignment:**
- Can be **assigned** tasks
- Can **update** own tasks
- Cannot **assign** tasks to others

---

### **Role: `project_manager`**
**Capabilities:**
- ✅ View all projects in company
- ✅ Create tasks
- ✅ Assign tasks to team members
- ✅ Update any task in their projects
- ✅ Manage project timeline
- ❌ Cannot create projects (unless also Company Super Admin)
- ❌ Cannot grant project access (unless project admin)

**Task Assignment:**
- Can **create** tasks
- Can **assign** tasks to any team member
- Can **update** any task
- Can **delete** tasks
- Can set task priorities, due dates, etc.

---

### **Role: `admin`**
**Capabilities:**
- ✅ All `project_manager` capabilities
- ✅ Manage project settings
- ✅ Grant/revoke project access (if project admin)
- ✅ Manage team members
- ❌ Cannot create projects (unless also Company Super Admin)

**Task Assignment:**
- All `project_manager` capabilities
- Can manage task templates
- Can bulk assign tasks
- Can manage task workflows

---

## 🏗️ Proposed Task System Architecture

### **Database Schema**

```sql
-- Tasks Table
CREATE TABLE tasks (
    id BIGINT PRIMARY KEY,
    project_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('todo', 'in_progress', 'review', 'done') DEFAULT 'todo',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    assigned_to BIGINT, -- FK to users.id
    assigned_by BIGINT, -- FK to users.id (who assigned it)
    due_date TIMESTAMP NULL,
    created_by BIGINT NOT NULL, -- FK to users.id
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Task Comments/Updates
CREATE TABLE task_comments (
    id BIGINT PRIMARY KEY,
    task_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## 🔐 Role-Based Task Permissions

### **Permission Matrix:**

| Action | user | project_manager | admin | Company Super Admin |
|--------|------|-----------------|-------|---------------------|
| View Tasks | ✅ (assigned only) | ✅ (all in project) | ✅ (all in project) | ✅ (all) |
| Create Tasks | ❌ | ✅ | ✅ | ✅ |
| Assign Tasks | ❌ | ✅ | ✅ | ✅ |
| Update Own Tasks | ✅ | ✅ | ✅ | ✅ |
| Update Any Task | ❌ | ✅ | ✅ | ✅ |
| Delete Tasks | ❌ | ✅ | ✅ | ✅ |
| Change Task Status | ✅ (own only) | ✅ (any) | ✅ (any) | ✅ (any) |
| Set Priority | ❌ | ✅ | ✅ | ✅ |
| Add Comments | ✅ | ✅ | ✅ | ✅ |

---

## 💡 Implementation Strategy

### **Option 1: Use Existing Role Field (Recommended)**

**Advantages:**
- ✅ No additional database changes
- ✅ Role already assigned during invitation
- ✅ Simple permission checks
- ✅ Consistent with existing system

**How it works:**
```php
// Check if user can assign tasks
if ($user->role === 'project_manager' || $user->role === 'admin' || $user->isCompanySuperAdmin()) {
    // Allow task assignment
}

// Check if user can create tasks
if (in_array($user->role, ['project_manager', 'admin']) || $user->isCompanySuperAdmin()) {
    // Allow task creation
}
```

---

### **Option 2: Use Spatie Permissions (More Flexible)**

**Advantages:**
- ✅ More granular permissions
- ✅ Can assign multiple roles
- ✅ Can create custom permissions
- ✅ Better for complex scenarios

**How it works:**
```php
// Create permissions
Permission::create(['name' => 'create tasks']);
Permission::create(['name' => 'assign tasks']);
Permission::create(['name' => 'manage tasks']);

// Assign to roles
$projectManagerRole->givePermissionTo(['create tasks', 'assign tasks', 'manage tasks']);
$userRole->givePermissionTo(['view tasks']);

// Check permissions
if ($user->can('assign tasks')) {
    // Allow task assignment
}
```

**Recommendation:** Start with **Option 1** (existing roles), upgrade to Option 2 if needed.

---

## 🎯 Task Assignment Workflow

### **Scenario: Project Manager Assigns Task**

```
1. PROJECT MANAGER creates task
   ↓
2. PROJECT MANAGER selects assignee
   ↓
3. System checks:
   - Is assignee a member of the project? ✅
   - Does assignee have access to project? ✅
   - Is assignee's role appropriate? ✅
   ↓
4. Task assigned
   ↓
5. Assignee receives notification
   ↓
6. Assignee can view and update task
```

### **Role-Based Assignment Rules:**

**Who can assign tasks:**
- ✅ `project_manager` → Can assign to any project member
- ✅ `admin` → Can assign to any project member
- ✅ Company Super Admin → Can assign to any project member
- ❌ `user` → Cannot assign tasks

**Who can be assigned tasks:**
- ✅ Any user with project access (regardless of role)
- ✅ Role doesn't matter for being assigned tasks

---

## 📋 Implementation Plan

### **Phase 1: Task System Foundation**

#### **1.1 Database**
- [ ] Create `tasks` table migration
- [ ] Create `task_comments` table migration
- [ ] Add indexes for performance

#### **1.2 Models**
- [ ] Create `Task` model
- [ ] Create `TaskComment` model
- [ ] Add relationships:
  - Task → Project
  - Task → Assigned User
  - Task → Creator
  - Task → Comments

#### **1.3 Services**
- [ ] Create `TaskService`
  - `createTask()`
  - `assignTask()`
  - `updateTask()`
  - `deleteTask()`
  - `getUserTasks()`
  - `getProjectTasks()`

#### **1.4 Controllers**
- [ ] Create `TaskController` (API)
  - `POST /api/v1/projects/{projectId}/tasks` - Create task
  - `GET /api/v1/projects/{projectId}/tasks` - List tasks
  - `GET /api/v1/tasks/{id}` - Get task details
  - `PUT /api/v1/tasks/{id}` - Update task
  - `DELETE /api/v1/tasks/{id}` - Delete task
  - `POST /api/v1/tasks/{id}/assign` - Assign task

#### **1.5 Role-Based Permissions**
- [ ] Add permission checks in TaskService
- [ ] Use user role to determine capabilities
- [ ] Implement role-based filtering

---

### **Phase 2: Task Assignment UI**

#### **2.1 Company Dashboard Enhancement**
- [ ] Add "Tasks" section to project detail view
- [ ] Task creation form
- [ ] Task list with filters
- [ ] Task assignment interface
- [ ] Task status updates

#### **2.2 Role-Based UI Elements**
- [ ] Show "Create Task" button only for project_manager/admin
- [ ] Show "Assign Task" button only for project_manager/admin
- [ ] Show task actions based on user role
- [ ] Filter tasks based on role (users see only assigned)

---

### **Phase 3: Advanced Features**

#### **3.1 Task Management**
- [ ] Task priorities
- [ ] Due dates
- [ ] Task dependencies
- [ ] Task templates
- [ ] Bulk operations

#### **3.2 Notifications**
- [ ] Email notifications on task assignment
- [ ] Task status change notifications
- [ ] Due date reminders

#### **3.3 Reporting**
- [ ] Task completion reports
- [ ] User workload reports
- [ ] Project progress tracking

---

## 🔄 Integration with Existing System

### **How Roles Flow Through the System:**

```
INVITATION
  ↓
Role: "project_manager"
  ↓
USER CREATED
  ↓
users.role = "project_manager"
  ↓
PROJECT ACCESS GRANTED
  ↓
project_user.access_level = "admin" (auto for project_manager)
  ↓
TASK SYSTEM
  ↓
Can create tasks? ✅ (role = project_manager)
Can assign tasks? ✅ (role = project_manager)
Can manage tasks? ✅ (role = project_manager)
```

---

## 💻 Code Examples

### **Task Service with Role Checks:**

```php
class TaskService
{
    public function createTask(array $data, User $creator, int $projectId): Task
    {
        // Check if user can create tasks
        if (!$this->canCreateTasks($creator)) {
            throw new \Exception('You do not have permission to create tasks', 403);
        }

        // Verify project access
        if (!$this->projectAccessService->canUserAccessProject($creator, $projectId)) {
            throw new \Exception('You do not have access to this project', 403);
        }

        return Task::create([
            'project_id' => $projectId,
            'title' => $data['title'],
            'description' => $data['description'],
            'created_by' => $creator->id,
            'status' => 'todo',
            'priority' => $data['priority'] ?? 'medium',
        ]);
    }

    public function assignTask(int $taskId, int $assigneeId, User $assigner): Task
    {
        // Check if user can assign tasks
        if (!$this->canAssignTasks($assigner)) {
            throw new \Exception('You do not have permission to assign tasks', 403);
        }

        $task = Task::findOrFail($taskId);
        
        // Verify assigner has access to project
        if (!$this->projectAccessService->canUserAccessProject($assigner, $task->project_id)) {
            throw new \Exception('You do not have access to this project', 403);
        }

        // Verify assignee has access to project
        $assignee = User::findOrFail($assigneeId);
        if (!$this->projectAccessService->canUserAccessProject($assignee, $task->project_id)) {
            throw new \Exception('Assignee does not have access to this project', 403);
        }

        $task->assigned_to = $assigneeId;
        $task->assigned_by = $assigner->id;
        $task->save();

        return $task;
    }

    protected function canCreateTasks(User $user): bool
    {
        return in_array($user->role, ['project_manager', 'admin']) 
            || $user->isCompanySuperAdmin();
    }

    protected function canAssignTasks(User $user): bool
    {
        return in_array($user->role, ['project_manager', 'admin']) 
            || $user->isCompanySuperAdmin();
    }

    protected function canUpdateAnyTask(User $user): bool
    {
        return in_array($user->role, ['project_manager', 'admin']) 
            || $user->isCompanySuperAdmin();
    }
}
```

---

## 🎨 UI Example: Task Assignment

### **In Project Detail View:**

```html
<!-- Only show for project_manager, admin, or Company Super Admin -->
@if(in_array(auth()->user()->role, ['project_manager', 'admin']) || auth()->user()->isCompanySuperAdmin())
    <button onclick="showCreateTaskModal()">Create Task</button>
@endif

<!-- Task List -->
<div id="tasksList">
    @foreach($tasks as $task)
        <div class="task-card">
            <h4>{{ $task->title }}</h4>
            <p>Assigned to: {{ $task->assignedTo->name }}</p>
            <p>Status: {{ $task->status }}</p>
            
            <!-- Show assign button only for project_manager/admin -->
            @if(in_array(auth()->user()->role, ['project_manager', 'admin']) || auth()->user()->isCompanySuperAdmin())
                <button onclick="assignTask({{ $task->id }})">Reassign</button>
            @endif
            
            <!-- Users can only update their own tasks -->
            @if(auth()->user()->id === $task->assigned_to || in_array(auth()->user()->role, ['project_manager', 'admin']))
                <button onclick="updateTask({{ $task->id }})">Update</button>
            @endif
        </div>
    @endforeach
</div>
```

---

## ✅ Summary

**Yes, invitation roles CAN and SHOULD be used for task assignment!**

**Benefits:**
1. ✅ **Consistent** - Role assigned once, used everywhere
2. ✅ **Simple** - No need for separate permission system
3. ✅ **Flexible** - Easy to add new roles later
4. ✅ **Scalable** - Can upgrade to Spatie Permissions if needed

**Implementation:**
- Use `users.role` field (already exists)
- Check role in TaskService methods
- Show/hide UI elements based on role
- Filter tasks based on role

**Next Steps:**
1. Implement task system (Phase 1)
2. Add role-based permission checks
3. Build UI with role-based features
4. Test with different roles

Would you like me to start implementing the task system with role-based permissions?

