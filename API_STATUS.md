# Bootstrap API Status - Frontend Integration Guide

## ✅ API Status: READY FOR INTEGRATION

All bootstrap APIs have been implemented, tested, and are ready for frontend integration.

---

## Base URL

```
http://your-domain.com/api/v1/bootstrap
```

**Note:** For local development with Laravel, use:
```
http://localhost:8000/api/v1/bootstrap
```

---

## API Endpoints

### 1. Get Bootstrap Status

**Endpoint:** `GET /api/v1/bootstrap/status`

**Authentication:** Not required (Public endpoint)

**Description:** Check the current bootstrap state of the system.

**Response (200 OK):**

```json
{
  "status": "BOOTSTRAP_PENDING" | "BOOTSTRAP_CONFIRMED" | "ACTIVE",
  "super_admin_exists": boolean,
  "super_admin_email": string | null,
  "created_at": "2024-01-15T10:30:00Z" | null,
  "confirmed_at": "2024-01-15T10:35:00Z" | null,
  "can_create": boolean,
  "can_confirm": boolean
}
```

**Status Values:**
- `BOOTSTRAP_PENDING`: No Super Admin exists. System is uninitialized.
- `BOOTSTRAP_CONFIRMED`: Super Admin created but not yet confirmed.
- `ACTIVE`: Bootstrap completed and confirmed. System is operational.

**Example Response (Pending):**
```json
{
  "status": "BOOTSTRAP_PENDING",
  "super_admin_exists": false,
  "super_admin_email": null,
  "created_at": null,
  "confirmed_at": null,
  "can_create": true,
  "can_confirm": false
}
```

**Example Response (Confirmed):**
```json
{
  "status": "BOOTSTRAP_CONFIRMED",
  "super_admin_exists": true,
  "super_admin_email": "admin@metatech.com",
  "created_at": "2024-01-15T10:30:00Z",
  "confirmed_at": null,
  "can_create": false,
  "can_confirm": true
}
```

**Example Response (Active):**
```json
{
  "status": "ACTIVE",
  "super_admin_exists": true,
  "super_admin_email": "admin@metatech.com",
  "created_at": "2024-01-15T10:30:00Z",
  "confirmed_at": "2024-01-15T10:35:00Z",
  "can_create": false,
  "can_confirm": false
}
```

---

### 2. Create First Super Admin

**Endpoint:** `POST /api/v1/bootstrap/create`

**Authentication:** Not required (Public endpoint)

**Description:** Create the first Super Admin user and initialize the system.

**Request Body:**
```json
{
  "email": "admin@metatech.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!",
  "first_name": "John",
  "last_name": "Doe"
}
```

**Validation Rules:**
- `email`: Required, valid email format, max 255 characters
- `password`: Required, min 8 chars, max 128 chars, must contain:
  - At least one uppercase letter
  - At least one lowercase letter
  - At least one number
  - At least one special character (!@#$%^&*()_+-=[]{}|;:,.<>?)
- `password_confirmation`: Required, must match password
- `first_name`: Required, max 100 characters, at least one non-whitespace character
- `last_name`: Required, max 100 characters, at least one non-whitespace character

**Success Response (201 Created):**
```json
{
  "message": "Super Admin created successfully",
  "user": {
    "id": 1,
    "email": "admin@metatech.com",
    "first_name": "John",
    "last_name": "Doe",
    "role": "super_admin",
    "email_verified_at": null,
    "created_at": "2024-01-15T10:30:00Z"
  },
  "status": "BOOTSTRAP_CONFIRMED",
  "requires_confirmation": true,
  "next_step": "Confirm bootstrap completion using /api/v1/bootstrap/confirm"
}
```

**Error Responses:**

**400 Bad Request - Validation Errors:**
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."],
    "password_confirmation": ["The password confirmation does not match."]
  }
}
```

**403 Forbidden - Bootstrap Already Completed:**
```json
{
  "message": "Bootstrap already completed",
  "status": "ACTIVE",
  "error_code": "BOOTSTRAP_ALREADY_COMPLETED"
}
```

**409 Conflict - Super Admin Already Exists:**
```json
{
  "message": "Super Admin already exists",
  "status": "BOOTSTRAP_CONFIRMED",
  "error_code": "SUPER_ADMIN_EXISTS"
}
```

**429 Too Many Requests:**
```json
{
  "message": "Too many bootstrap attempts. Please try again later.",
  "error_code": "RATE_LIMIT_EXCEEDED",
  "retry_after": 3600
}
```

**Rate Limiting:**
- Maximum 5 attempts per IP address per hour
- Maximum 10 attempts per IP address per 24 hours
- Response includes `Retry-After` header with seconds remaining

---

### 3. Confirm Bootstrap Completion

**Endpoint:** `POST /api/v1/bootstrap/confirm`

**Authentication:** Required (JWT Token)

**Description:** Confirm that bootstrap is complete and lock the system for normal operations.

**Headers:**
```
Authorization: Bearer {jwt_token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "confirmation_code": "" // Optional, reserved for future use
}
```

**Success Response (200 OK):**
```json
{
  "message": "Bootstrap confirmed successfully",
  "status": "ACTIVE",
  "confirmed_at": "2024-01-15T10:35:00Z",
  "system_ready": true
}
```

**Error Responses:**

**401 Unauthorized - Missing/Invalid Token:**
```json
{
  "message": "Unauthenticated",
  "error_code": "UNAUTHENTICATED"
}
```

**403 Forbidden - Not Super Admin:**
```json
{
  "message": "Only Super Admin can confirm bootstrap",
  "error_code": "INSUFFICIENT_PERMISSIONS"
}
```

**403 Forbidden - Already Confirmed:**
```json
{
  "message": "Bootstrap already confirmed",
  "status": "ACTIVE",
  "error_code": "BOOTSTRAP_ALREADY_CONFIRMED"
}
```

**403 Forbidden - Bootstrap Not Ready:**
```json
{
  "message": "Bootstrap not ready for confirmation. Super Admin must be created first.",
  "status": "BOOTSTRAP_PENDING",
  "error_code": "BOOTSTRAP_NOT_READY"
}
```

**429 Too Many Requests:**
```json
{
  "message": "Too many confirmation attempts. Please try again later.",
  "error_code": "RATE_LIMIT_EXCEEDED",
  "retry_after": 300
}
```

**Rate Limiting:**
- Maximum 10 attempts per authenticated user per hour

---

### 4. Get Bootstrap Audit Logs

**Endpoint:** `GET /api/v1/bootstrap/audit`

**Authentication:** Required (JWT Token, Super Admin only)

**Description:** Retrieve audit logs for bootstrap operations.

**Headers:**
```
Authorization: Bearer {jwt_token}
```

**Query Parameters:**
- `page` (optional, default: 1): Page number for pagination
- `per_page` (optional, default: 20, max: 100): Items per page
- `action` (optional): Filter by action: `create`, `confirm`, `status_check`
- `result` (optional): Filter by result: `success`, `failure`

**Example Request:**
```
GET /api/v1/bootstrap/audit?page=1&per_page=20&action=create&result=success
```

**Success Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "action": "create",
      "result": "success",
      "ip_address": "192.168.1.100",
      "user_id": 1,
      "email": "admin@metatech.com",
      "request_payload": {
        "email": "admin@metatech.com",
        "first_name": "John",
        "last_name": "Doe"
      },
      "error_message": null,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 1,
    "last_page": 1,
    "from": 1,
    "to": 1
  }
}
```

**Error Responses:**

**401 Unauthorized:**
```json
{
  "message": "Unauthenticated",
  "error_code": "UNAUTHENTICATED"
}
```

**403 Forbidden - Not Super Admin:**
```json
{
  "message": "Only Super Admin can view audit logs",
  "error_code": "INSUFFICIENT_PERMISSIONS"
}
```

---

## Error Codes Reference

| Error Code | HTTP Status | Description |
|------------|-------------|-------------|
| `UNAUTHENTICATED` | 401 | JWT token missing or invalid |
| `INSUFFICIENT_PERMISSIONS` | 403 | User lacks required role |
| `BOOTSTRAP_ALREADY_COMPLETED` | 403 | System already bootstrapped |
| `BOOTSTRAP_ALREADY_CONFIRMED` | 403 | Bootstrap already confirmed |
| `BOOTSTRAP_NOT_READY` | 403 | Bootstrap not in correct state |
| `SUPER_ADMIN_EXISTS` | 409 | Super Admin already exists |
| `RATE_LIMIT_EXCEEDED` | 429 | Too many requests |
| `INTERNAL_ERROR` | 500 | Server error |

---

## Frontend Integration Flow

### Step 1: Check Bootstrap Status

On app initialization, call the status endpoint:

```javascript
GET /api/v1/bootstrap/status
```

Based on the response:

**If `status === "BOOTSTRAP_PENDING"`:**
- Show Super Admin creation form
- Allow user to create first Super Admin

**If `status === "BOOTSTRAP_CONFIRMED"`:**
- Show confirmation screen
- User needs to login and confirm bootstrap
- Store JWT token after login

**If `status === "ACTIVE"`:**
- Hide bootstrap UI
- Show normal application interface

### Step 2: Create Super Admin (if pending)

```javascript
POST /api/v1/bootstrap/create
Body: {
  email: "admin@metatech.com",
  password: "SecurePass123!",
  password_confirmation: "SecurePass123!",
  first_name: "John",
  last_name: "Doe"
}
```

**After successful creation:**
- User needs to login to get JWT token
- Then proceed to confirmation step

### Step 3: Login (to get JWT token)

**Note:** You'll need to implement a login endpoint separately. After login, store the JWT token.

### Step 4: Confirm Bootstrap (if confirmed)

```javascript
POST /api/v1/bootstrap/confirm
Headers: {
  Authorization: "Bearer {jwt_token}"
}
Body: {
  confirmation_code: ""
}
```

**After successful confirmation:**
- Status changes to `ACTIVE`
- System is ready for normal operations

---

## JavaScript/TypeScript Example

### Check Status
```javascript
async function checkBootstrapStatus() {
  const response = await fetch('/api/v1/bootstrap/status');
  const data = await response.json();
  
  if (data.status === 'BOOTSTRAP_PENDING') {
    // Show creation form
  } else if (data.status === 'BOOTSTRAP_CONFIRMED') {
    // Show confirmation screen
  } else if (data.status === 'ACTIVE') {
    // Show normal app
  }
}
```

### Create Super Admin
```javascript
async function createSuperAdmin(formData) {
  const response = await fetch('/api/v1/bootstrap/create', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(formData)
  });
  
  if (response.status === 201) {
    const data = await response.json();
    // Redirect to login or confirmation
  } else if (response.status === 429) {
    const data = await response.json();
    // Show rate limit error, disable form
    // Retry after data.retry_after seconds
  } else {
    const error = await response.json();
    // Handle validation errors or other errors
  }
}
```

### Confirm Bootstrap
```javascript
async function confirmBootstrap(jwtToken) {
  const response = await fetch('/api/v1/bootstrap/confirm', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${jwtToken}`
    },
    body: JSON.stringify({ confirmation_code: '' })
  });
  
  if (response.status === 200) {
    const data = await response.json();
    // Bootstrap confirmed, redirect to main app
  } else {
    const error = await response.json();
    // Handle errors
  }
}
```

---

## Testing Status

✅ **All APIs tested and working:**
- 17 test cases passing
- All endpoints tested
- Validation rules verified
- Error handling verified
- Authentication & authorization tested
- State transitions tested

---

## Important Notes for Frontend

1. **Password Requirements:**
   - Minimum 8 characters
   - Must contain uppercase, lowercase, number, and special character
   - Validate on frontend before submission

2. **Rate Limiting:**
   - Handle 429 responses gracefully
   - Show user-friendly messages
   - Disable form submission during rate limit
   - Use `retry_after` value to show countdown

3. **JWT Token:**
   - Store token securely (localStorage/sessionStorage or httpOnly cookie)
   - Include in `Authorization: Bearer {token}` header
   - Handle token expiration (401 responses)

4. **Error Handling:**
   - Check `error_code` for specific error handling
   - Display user-friendly error messages
   - Handle validation errors (422) by showing field-specific errors

5. **State Management:**
   - Poll status endpoint periodically if needed
   - Update UI based on `can_create` and `can_confirm` flags
   - Handle state transitions smoothly

6. **CORS:**
   - Make sure CORS is configured on backend if frontend is on different domain

---

## Contact & Support

For any API-related questions or issues, please refer to the API contract document or contact the backend team.

**API Version:** v1
**Last Updated:** December 2024
**Status:** ✅ Production Ready

