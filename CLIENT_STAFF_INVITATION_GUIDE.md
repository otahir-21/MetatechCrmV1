# Client Owner: Staff Invitation & Project Access Control Guide

## 🎯 Overview

As a **Client Owner (Company Super Admin)**, you can:
1. ✅ **Invite staff members** to your company
2. ✅ **Control staff access by project** - grant/revoke access to specific projects

**This functionality is ALREADY IMPLEMENTED!** This guide explains how to use it.

---

## 📍 Important: Correct URL

**Note:** `crm.localhost` is for **Internal Metatech Employees**, NOT client owners.

As a **Client Owner**, you should use:
```
http://{your-company-subdomain}.localhost:8000
```

**Example:** If your company subdomain is `elite`:
```
http://elite.localhost:8000
```

---

## 🚀 Complete Workflow

### **STEP 1: Login as Company Super Admin**

1. Go to your company subdomain:
   ```
   http://{your-company-subdomain}.localhost:8000/login
   ```

2. Login with your Company Super Admin credentials

3. You'll be redirected to: `/company-dashboard`

---

### **STEP 2: Invite Staff Members**

#### **Via Web UI (Company Dashboard):**

1. In the Company Dashboard, click **"Staff Invitations"** in the left sidebar

2. Click **"Invite Staff"** button

3. Fill in the form:
   - **Email**: `newstaff@example.com`
   - **Role**: Choose from:
     - `user` - Basic user
     - `admin` - Admin with more permissions
     - `project_manager` - Project manager role

4. Click **"Send Invitation"**

5. An email will be sent to the staff member with an invitation link

#### **Via API (Alternative):**

```bash
POST /api/v1/staff/invite
Authorization: Bearer {your-jwt-token}
Content-Type: application/json

{
  "email": "newstaff@example.com",
  "role": "admin"
}
```

---

### **STEP 3: Staff Member Accepts Invitation**

1. Staff member receives email with invitation link:
   ```
   http://{your-company-subdomain}.localhost:8000/accept-invitation/{token}
   ```

2. Staff member clicks the link

3. Staff member fills out the form:
   - First Name
   - Last Name
   - Password (must meet requirements)
   - Confirm Password

4. Staff member clicks **"Accept Invitation & Create Account"**

5. Account is created and they can now login

---

### **STEP 4: Create Projects**

1. In Company Dashboard, click **"Projects"** in the left sidebar

2. Click **"Create Project"** button

3. Fill in:
   - **Project Name**: e.g., "Project Alpha"
   - **Description**: Optional description

4. Click **"Create"**

5. Project is created and you (Company Super Admin) automatically get **admin** access

---

### **STEP 5: Grant Project Access to Staff**

#### **Current Status:**
The UI for granting project access is **pending**. Currently, you can use the **API**:

#### **Via API:**

```bash
POST /api/v1/projects/{projectId}/grant-access
Authorization: Bearer {your-jwt-token}
Content-Type: application/json

{
  "user_id": 5,
  "access_level": "editor"  // or "viewer" or "admin"
}
```

**Access Levels:**
- `viewer` - Can only view project
- `editor` - Can view and edit project
- `admin` - Can manage project and grant access to others

#### **Example Flow:**

1. **Get the user ID** of the staff member you want to grant access to
   ```bash
   GET /api/v1/staff/invitations
   # Or check your database/users table
   ```

2. **Grant access:**
   ```bash
   POST /api/v1/projects/1/grant-access
   {
     "user_id": 5,
     "access_level": "editor"
   }
   ```

3. **Verify access:**
   ```bash
   GET /api/v1/projects/1
   # Should show the user in the "users" array
   ```

---

## 📊 How It Works (Technical Flow)

```
┌─────────────────────────────────────────────────────────────┐
│  CLIENT OWNER WORKFLOW                                      │
└─────────────────────────────────────────────────────────────┘

1. CLIENT OWNER LOGS IN
   ↓
   http://{subdomain}.localhost:8000/login
   ↓
   Redirected to: /company-dashboard

2. INVITE STAFF
   ↓
   Click "Staff Invitations" → "Invite Staff"
   ↓
   Enter email + role
   ↓
   System creates invitation record
   ↓
   Email sent with token

3. STAFF ACCEPTS INVITATION
   ↓
   Click link in email
   ↓
   Fill form (name, password)
   ↓
   Account created
   ↓
   Staff can now login

4. CREATE PROJECTS
   ↓
   Click "Projects" → "Create Project"
   ↓
   Enter project name + description
   ↓
   Project created
   ↓
   Client Owner gets admin access automatically

5. GRANT PROJECT ACCESS
   ↓
   Use API: POST /api/v1/projects/{id}/grant-access
   ↓
   Specify user_id + access_level
   ↓
   Access granted
   ↓
   Staff member can now see/access the project

6. STAFF MEMBER LOGS IN
   ↓
   http://{subdomain}.localhost:8000/login
   ↓
   Redirected to: /company-dashboard
   ↓
   Sees only projects they have access to
```

---

## 🔐 Access Control Rules

### **Company Super Admin (Client Owner):**
- ✅ Can access **ALL projects** in their company
- ✅ Can create projects
- ✅ Can grant/revoke access to any project
- ✅ Can invite staff
- ✅ Has **admin** access to all projects automatically

### **Staff Members:**
- ✅ Can only see projects they've been **explicitly granted access to**
- ✅ Access level determines what they can do:
  - **viewer** → Read-only
  - **editor** → Can edit project
  - **admin** → Can manage project and grant access

### **Project Access Levels:**
- **viewer**: Can view project details only
- **editor**: Can view and edit project
- **admin**: Can view, edit, and manage project access (grant/revoke)

---

## 🧪 Testing the Complete Flow

### **Test Scenario: Invite Staff & Grant Project Access**

1. **Login as Company Super Admin:**
   ```
   http://elite.localhost:8000/login
   ```

2. **Invite Staff:**
   - Go to Staff Invitations
   - Invite: `staff1@example.com` with role `admin`

3. **Accept Invitation (as staff member):**
   - Check email/log for invitation link
   - Visit: `http://elite.localhost:8000/accept-invitation/{token}`
   - Create account

4. **Create Project:**
   - Go to Projects
   - Create: "Project Alpha"

5. **Grant Access (via API):**
   ```bash
   # Get your JWT token (from browser console or session)
   TOKEN="your-jwt-token"
   
   # Get user ID of staff member (from database or API)
   USER_ID=5  # Staff member's user ID
   PROJECT_ID=1  # Project Alpha's ID
   
   # Grant editor access
   curl -X POST http://elite.localhost:8000/api/v1/projects/$PROJECT_ID/grant-access \
     -H "Authorization: Bearer $TOKEN" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{
       "user_id": '$USER_ID',
       "access_level": "editor"
     }'
   ```

6. **Login as Staff Member:**
   - Login with `staff1@example.com`
   - Go to Projects section
   - Should see "Project Alpha" with "editor" access level

---

## 🎨 UI Enhancement Needed

**Current Status:** The Company Dashboard has:
- ✅ Staff Invitation UI (complete)
- ✅ Project Creation UI (complete)
- ⏳ Project Access Management UI (pending - use API for now)

**To Add:** A UI section in Company Dashboard to:
- View all staff members
- See which projects each staff member has access to
- Grant/revoke project access with a click

---

## 📝 Quick Reference

### **Staff Invitation Endpoints:**
- `POST /api/v1/staff/invite` - Invite staff
- `GET /api/v1/staff/invitations` - List invitations
- `POST /api/v1/staff/invitations/{token}/accept` - Accept invitation
- `DELETE /api/v1/staff/invitations/{id}` - Cancel invitation

### **Project Endpoints:**
- `GET /api/v1/projects` - List accessible projects
- `POST /api/v1/projects` - Create project
- `GET /api/v1/projects/{id}` - Get project details
- `PUT /api/v1/projects/{id}` - Update project
- `POST /api/v1/projects/{id}/grant-access` - Grant access
- `DELETE /api/v1/projects/{id}/revoke-access/{userId}` - Revoke access

---

## ✅ Summary

**What's Already Working:**
1. ✅ Staff invitation system (complete)
2. ✅ Project creation (complete)
3. ✅ Project-based access control (complete via API)
4. ✅ Staff can only see projects they have access to (complete)

**What Needs UI Enhancement:**
- ⏳ Visual interface for granting/revoking project access (currently API-only)

**The core functionality is COMPLETE!** You can invite staff and control their project access. The only missing piece is a UI for the access management, but you can use the API for now.

---

## 🚀 Next Steps

Would you like me to:
1. **Add a UI for project access management** in the Company Dashboard?
2. **Create a "Team Members" section** showing all staff and their project access?
3. **Add bulk operations** (grant access to multiple users at once)?

Let me know what you'd like to enhance! 🎉

