# Frontend Testing Guide

## 🎯 How to Test Phase 3 & 4 Features from Frontend

This guide will help you test the Staff Invitation and Project-Based Access features through the web interface.

---

## 📋 Prerequisites

1. **You need a Company Super Admin account** - If you don't have one:
   - Log in as Product Owner at `http://admincrm.localhost:8000/login`
   - Go to Dashboard → Click "Generate CRM"
   - Create a Company Super Admin with credentials

2. **Ensure your server is running:**
   ```bash
   php artisan serve
   ```

---

## 🚀 Step-by-Step Testing

### **Step 1: Login as Company Super Admin**

1. Open your browser and navigate to your company's subdomain:
   ```
   http://{your-company-subdomain}.localhost:8000/login
   ```
   
   Example: If your company subdomain is `elite`, use:
   ```
   http://elite.localhost:8000/login
   ```

2. Log in with your Company Super Admin credentials

3. You should be redirected to `/company-dashboard`

---

### **Step 2: Test Staff Invitations (Phase 3)**

#### **2.1 Invite a Staff Member**

1. Click on **"Staff Invitations"** in the left sidebar
2. Click **"Invite Staff"** button
3. Fill in the form:
   - **Email**: Enter a test email (e.g., `newstaff@example.com`)
   - **Role**: Select a role (User, Admin, or Project Manager)
4. Click **"Send Invitation"**
5. You should see a success message and the invitation appear in the list

#### **2.2 View Invitations**

- All invitations will be listed in the table with:
  - Email address
  - Role
  - Status (pending, accepted, cancelled)
  - Expiration date
  - Cancel button (for pending invitations)

#### **2.3 Accept Invitation (Test as Invitee)**

1. **Check email** (or check `storage/logs/laravel.log` if using log mailer)
2. **Click the invitation link** in the email
3. **Fill out the acceptance form:**
   - First Name
   - Last Name
   - Password (must meet requirements: 8+ chars, uppercase, lowercase, number, symbol)
   - Confirm Password
4. **Click "Accept Invitation & Create Account"**
5. You should be redirected to login page with success message
6. **Log in** with the new credentials

#### **2.4 Cancel Invitation**

1. In the Staff Invitations section
2. Find a pending invitation
3. Click **"Cancel"** button
4. Confirm cancellation
5. Status should change to "cancelled"

---

### **Step 3: Test Projects (Phase 4)**

#### **3.1 Create a Project**

1. Click on **"Projects"** in the left sidebar (default view)
2. Click **"Create Project"** button
3. Fill in the form:
   - **Project Name**: Enter a name (e.g., "Project Alpha")
   - **Description**: Optional description
4. Click **"Create"**
5. The project should appear in your projects list

#### **3.2 View Projects**

- All projects you have access to are displayed as cards
- Each card shows:
  - Project name
  - Description
  - Your access level (admin, editor, viewer)
  - "View Details" button

#### **3.3 Grant Project Access (via API - UI pending)**

Currently, project access management is available via API. You can test it using:

**Using cURL:**
```bash
# Get your JWT token first (from browser console: sessionStorage.getItem('token') or check session)
TOKEN="your-jwt-token-here"
PROJECT_ID=1
USER_ID=5

curl -X POST http://elite.localhost:8000/api/v1/projects/$PROJECT_ID/grant-access \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "user_id": '$USER_ID',
    "access_level": "editor"
  }'
```

**Using JavaScript in Browser Console:**
```javascript
fetch('/api/v1/projects/1/grant-access', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    user_id: 5,
    access_level: 'editor'
  })
})
.then(r => r.json())
.then(console.log);
```

---

## 🧪 Testing Checklist

### ✅ Phase 3: Staff Invitations

- [ ] Can invite staff member with email and role
- [ ] Invitation appears in the list with "pending" status
- [ ] Email is sent (check logs if using log mailer)
- [ ] Can access invitation acceptance page via token
- [ ] Can create account by accepting invitation
- [ ] Can login with new account
- [ ] Can cancel pending invitation
- [ ] Cancelled invitation shows correct status

### ✅ Phase 4: Projects

- [ ] Can create a new project
- [ ] Project appears in projects list
- [ ] Company Super Admin has "admin" access to all projects
- [ ] Can view project details (via API)
- [ ] Can grant access to users (via API)
- [ ] Can revoke access from users (via API)
- [ ] Users only see projects they have access to

---

## 🔍 Troubleshooting

### **Issue: Can't access company dashboard**

**Solution:**
- Make sure you're logged in as a Company Super Admin (not Product Owner)
- Check you're on the correct subdomain (e.g., `elite.localhost:8000`)
- Verify your user has `company_name` and `subdomain` set in database

### **Issue: Invitation email not sent**

**Solution:**
- Check `.env` for mail configuration
- For testing, set `MAIL_MAILER=log` to log emails to `storage/logs/laravel.log`
- Check `storage/logs/laravel.log` for errors

### **Issue: API calls return 401/403**

**Solution:**
- Ensure you're logged in
- Check that the JWT token is valid
- Verify you're on the correct subdomain
- Check browser console for CORS errors

### **Issue: Projects not showing**

**Solution:**
- Verify you created projects while logged in as Company Super Admin
- Check browser console for API errors
- Ensure API token is being sent in Authorization header

---

## 📝 Quick Test Commands

### **Create Test Company Super Admin (via API)**

```bash
# Login as Product Owner first to get token
curl -X POST http://admincrm.localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "productowner@example.com",
    "password": "YourPassword"
  }'

# Use the token to create company
curl -X POST http://admincrm.localhost:8000/api/v1/company/create \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "company_name": "Test Company",
    "email": "companyadmin@test.com",
    "first_name": "Company",
    "last_name": "Admin",
    "password": "AdminPass123!"
  }'
```

### **Test Invitation Flow (via API)**

```bash
# 1. Login as Company Super Admin
# 2. Invite staff
curl -X POST http://testcompany.localhost:8000/api/v1/staff/invite \
  -H "Authorization: Bearer COMPANY_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "staff@test.com",
    "role": "admin"
  }'

# 3. Get invitation token from database or email
# 4. Accept invitation via web UI at:
# http://testcompany.localhost:8000/accept-invitation/{token}
```

---

## 🌐 URLs Reference

### **Product Owner:**
- Login: `http://admincrm.localhost:8000/login`
- Dashboard: `http://admincrm.localhost:8000/dashboard`

### **Company Super Admin:**
- Login: `http://{subdomain}.localhost:8000/login`
- Dashboard: `http://{subdomain}.localhost:8000/company-dashboard`

### **Internal Employee:**
- Login: `http://crm.localhost:8000/login`
- Dashboard: `http://crm.localhost:8000/internal/dashboard`

### **Staff Invitation:**
- Accept: `http://{subdomain}.localhost:8000/accept-invitation/{token}`

---

## ✨ Next Steps

After testing, you can:
1. Add more UI features (project detail view, access management UI)
2. Enhance the team members list
3. Add project management features
4. Implement notification system

Happy Testing! 🎉

