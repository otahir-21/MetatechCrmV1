# Quick Start Testing Guide

## 🚀 Quick Test Steps

### 1. Start Laravel Server
```bash
php artisan serve
```

### 2. Setup Roles (One-time setup)
```bash
php setup_roles_for_testing.php
```

### 3. Get JWT Token
```bash
php get_token.php superadmin@productowner.com Admin123@
```

Copy the token that's displayed.

### 4. Quick Automated Test
```bash
./quick_test_phase1_phase2.sh
```

This will automatically test:
- Block user
- Unblock user  
- Super admin route access
- Multi-role route access

---

## 📋 Manual Testing Commands

### Phase 1: Block/Unblock

**Block User (replace TOKEN and USER_ID):**
```bash
curl -X POST http://localhost:8000/api/v1/user-management/users/2/block \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Host: admincrm.localhost" \
  -d '{"reason": "Testing"}'
```

**Unblock User:**
```bash
curl -X POST http://localhost:8000/api/v1/user-management/users/2/unblock \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Host: admincrm.localhost"
```

**Block Company:**
```bash
curl -X POST http://localhost:8000/api/v1/user-management/companies/1/block \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Host: admincrm.localhost" \
  -d '{"reason": "Payment overdue"}'
```

---

### Phase 2: Role-Based Access

**Test Super Admin Only Route:**
```bash
curl -X GET http://localhost:8000/api/v1/test/super-admin-only \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Host: admincrm.localhost"
```

**Test Multi-Role Route (Admin or Super Admin):**
```bash
curl -X GET http://localhost:8000/api/v1/test/admin-or-super \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Host: admincrm.localhost"
```

---

## 🔍 Verify in Database

**Check user status:**
```sql
SELECT id, email, status, status_reason, blocked_at 
FROM users 
WHERE id = 2;
```

**Check user roles:**
```sql
SELECT u.id, u.email, r.name as role
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.id = 1;
```

---

## 📖 Full Testing Guide

For comprehensive testing instructions, see: **TESTING_GUIDE_PHASE1_PHASE2.md**

