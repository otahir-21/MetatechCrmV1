# Testing URLs Guide - Metatech CRM

Server is running at: **http://localhost:8000**

---

## 🌐 Base URLs by Subdomain

### 1. Product Owner Dashboard
**URL:** `http://admincrm.localhost:8000` or `http://localhost:8000`

### 2. Internal CRM (Metatech Employees)
**URL:** `http://crm.localhost:8000`

### 3. Company/Client Portal
**URL:** `http://vyooo.localhost:8000` (or any company subdomain)

---

## 🔐 Authentication URLs

### Product Owner Login
- **Login Page:** `http://admincrm.localhost:8000/login`
- **Login Action:** `POST http://admincrm.localhost:8000/login`
- **Logout:** `POST http://admincrm.localhost:8000/logout`

**Test Credentials:**
- Email: `superadmin@metatech.ae` (or your Product Owner email)
- Password: (your Product Owner password)

### Internal CRM Login
- **Login Page:** `http://crm.localhost:8000/login`
- **Login Action:** `POST http://crm.localhost:8000/login`

### Company Login
- **Login Page:** `http://vyooo.localhost:8000/login`
- **Login Action:** `POST http://vyooo.localhost:8000/login`

**Test Credentials (Company Owner):**
- Email: `ae.metatech@gmail.com`
- Password: `Admin123@`

### Password Reset
- **Request Form:** `http://localhost:8000/password/forgot`
- **Reset Link:** `http://localhost:8000/password/reset?token=xxx&email=xxx`

---

## 📊 Product Owner Dashboard URLs

### Main Dashboard
- **Dashboard:** `http://admincrm.localhost:8000/dashboard`
  - Overview/Stats
  - Companies list
  - Users management
  - Company invitations list

### Company Management
- **Create Company:** `http://admincrm.localhost:8000/company/create`
- **Company Details:** (via dashboard)

### Internal Employee Management
- **Create Internal Employee:** `http://admincrm.localhost:8000/internal-employee/create`

### Audit Logs
- **Audit Logs:** `http://admincrm.localhost:8000/audit-logs`
  - View login logs
  - View invitation logs
  - View role change logs

---

## 👥 Internal CRM URLs (Metatech Employees)

### Main Dashboard
- **Internal Dashboard:** `http://crm.localhost:8000/internal/dashboard`

### Projects Management
- **Projects List:** `http://crm.localhost:8000/internal/projects`
- **Create Project:** `http://crm.localhost:8000/internal/projects/create`
- **View Project:** `http://crm.localhost:8000/internal/projects/{id}`
- **Edit Project:** `http://crm.localhost:8000/internal/projects/{id}/edit`

### Employee Management
- **Employees List:** `http://crm.localhost:8000/internal/employees`
- **View Employee:** `http://crm.localhost:8000/internal/employees/{id}`
- **Edit Employee:** `http://crm.localhost:8000/internal/employees/{id}/edit`

### Suspended Page
- **Suspended:** `http://crm.localhost:8000/internal/suspended` (if user is suspended)

---

## 🏢 Company/Client Dashboard URLs

### Company Dashboard
- **Company Dashboard:** `http://vyooo.localhost:8000/company-dashboard`
  - Company overview
  - Staff management
  - Projects management
  - Tasks management

---

## ✉️ Invitation URLs

### Company Owner Invitation
- **Accept Invitation:** `http://vyooo.localhost:8000/company-invite/accept?email=xxx&token=xxx`
- **Accept Action:** `POST http://vyooo.localhost:8000/company-invite/accept`

### Staff Invitation (Company Staff)
- **Accept Invitation:** `http://vyooo.localhost:8000/accept-invitation/{token}`
- **Accept Action:** `POST http://vyooo.localhost:8000/accept-invitation/{token}`

### Employee Invitation (Internal CRM)
- **Accept Invitation:** `http://crm.localhost:8000/employee/invite/accept?email=xxx&token=xxx`
- **Accept Action:** `POST http://crm.localhost:8000/employee/invite/accept`

---

## 🔄 Bootstrap URLs (One-time Setup)

### Bootstrap Flow
- **Bootstrap Index:** `http://localhost:8000/`
- **Create Super Admin:** `http://localhost:8000/bootstrap/create`
- **Confirm Bootstrap:** `http://localhost:8000/bootstrap/confirm`
- **Bootstrap Complete:** `http://localhost:8000/bootstrap/complete`

---

## 🧪 Testing Checklist

### ✅ Product Owner Flow
1. [ ] Login at `http://admincrm.localhost:8000/login`
2. [ ] Access dashboard at `http://admincrm.localhost:8000/dashboard`
3. [ ] Create a company
4. [ ] Send company owner invitation
5. [ ] View company invitations list
6. [ ] Create internal employee
7. [ ] View audit logs
8. [ ] Block/unblock company
9. [ ] Block/unblock user
10. [ ] Logout

### ✅ Internal CRM Flow
1. [ ] Login at `http://crm.localhost:8000/login`
2. [ ] Access dashboard at `http://crm.localhost:8000/internal/dashboard`
3. [ ] View projects list
4. [ ] Create a project
5. [ ] Edit a project
6. [ ] View employees list
7. [ ] Edit employee details
8. [ ] Send employee invitation
9. [ ] Logout

### ✅ Company Owner Flow
1. [ ] Receive company owner invitation email
2. [ ] Click invitation link (accept invitation)
3. [ ] Set password
4. [ ] Login at `http://vyooo.localhost:8000/login`
5. [ ] Access company dashboard
6. [ ] Invite staff members
7. [ ] Create projects
8. [ ] Create tasks
9. [ ] Logout

### ✅ Staff Member Flow
1. [ ] Receive staff invitation email
2. [ ] Click invitation link (accept invitation)
3. [ ] Set password
4. [ ] Login at `http://vyooo.localhost:8000/login`
5. [ ] Access company dashboard
6. [ ] View assigned projects
7. [ ] View assigned tasks
8. [ ] Logout

---

## 📝 Important Test Scenarios

### 1. Multi-Tenant Access Control
- [ ] Product Owner can only access `admincrm.localhost:8000`
- [ ] Internal employee can only access `crm.localhost:8000`
- [ ] Company owner can only access their subdomain (e.g., `vyooo.localhost:8000`)
- [ ] Users cannot access wrong subdomain

### 2. Invitation System
- [ ] Company owner invitation works
- [ ] Staff invitation works
- [ ] Employee invitation works
- [ ] Invitation links expire correctly
- [ ] Cannot use same invitation twice

### 3. User Management
- [ ] Product Owner can block/unblock companies
- [ ] Product Owner can block/unblock users
- [ ] Internal Super Admin can manage employees
- [ ] Company Super Admin can manage staff

### 4. Role-Based Access
- [ ] Internal Super Admin has full access
- [ ] Internal Admin has limited access
- [ ] Internal User has read access
- [ ] Company Super Admin has full company access
- [ ] Company Admin has limited access
- [ ] Company User has read access

---

## 🔍 API Endpoints (Optional Testing)

All API endpoints are under `/api/v1/` and require authentication token.

### Authentication
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`

### Companies (Product Owner only)
- `GET /api/v1/company`
- `POST /api/v1/company`
- `GET /api/v1/company/{id}`
- `GET /api/v1/company/stats`
- `GET /api/v1/company/invitations`
- `DELETE /api/v1/company/invitations/{id}`

### Audit Logs (Product Owner only)
- `GET /api/v1/audit-logs`

---

## ⚠️ Notes

1. **Subdomain Setup:** Make sure you've added entries to your `/etc/hosts` file:
   ```
   127.0.0.1 admincrm.localhost
   127.0.0.1 crm.localhost
   127.0.0.1 vyooo.localhost
   ```

2. **Database:** Ensure your database is set up and migrations are run:
   ```bash
   php artisan migrate
   ```

3. **First Time Setup:** If this is the first time, you may need to:
   - Create Product Owner (bootstrap)
   - Confirm bootstrap
   - Then proceed with testing

---

## 🚀 Quick Start Testing

1. **Start Server:** `php artisan serve`
2. **Test Product Owner:** `http://admincrm.localhost:8000/login`
3. **Test Internal CRM:** `http://crm.localhost:8000/login`
4. **Test Company:** `http://vyooo.localhost:8000/login`

---

**Happy Testing! 🎉**

