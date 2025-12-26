# Employee Invitation System Implementation

## Overview
Secure employee invitation system for Internal CRM (`crm.metatech.ae`) that allows Super Admins to send invitation links to new employees. Employees can accept invitations, set their password, and activate their accounts.

## Features

### ✅ User Stories Implemented
- **As a Super Admin, I want to send an invite link to an employee so they can activate their account securely.**
- **As an employee, I want to accept my invite, set my password, and start using `crm.metatech.ae`.**

### ✅ Security Features
1. **Expiring Invitations**: Invitations expire after 7 days
2. **Single-Use Tokens**: Each invitation can only be accepted once
3. **Secure Token Generation**: 64-character random tokens hashed with SHA256
4. **Role-Based Authorization**: Only authorized users can send invitations

## Database Schema

### `employee_invitations` Table
- `id`: Primary key
- `email`: Unique email address of invitee
- `token`: SHA256 hash of the invitation token
- `invited_by`: Foreign key to users table (who sent the invitation)
- `role`: Role to be assigned (user, admin, super_admin)
- `department`: Optional department
- `designation`: Optional designation
- `joined_date`: Optional joined date
- `first_name`: Optional first name (can be set during invitation or acceptance)
- `last_name`: Optional last name (can be set during invitation or acceptance)
- `accepted`: Boolean flag indicating if invitation was accepted
- `accepted_at`: Timestamp when invitation was accepted
- `expires_at`: Timestamp when invitation expires (7 days from creation)
- `ip_address`: IP address of the invitation request
- `created_at`, `updated_at`: Timestamps

## API Endpoints

### Send Employee Invitation
**POST** `/api/v1/internal-employee/invite`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "email": "employee@metatech.ae",
  "role": "user",
  "department": "Sales",
  "designation": "Sales Manager",
  "joined_date": "2025-01-01",
  "first_name": "John",
  "last_name": "Doe"
}
```

**Response (201):**
```json
{
  "message": "Invitation sent successfully",
  "data": {
    "id": 1,
    "email": "employee@metatech.ae",
    "role": "user",
    "department": "Sales",
    "designation": "Sales Manager",
    "expires_at": "2025-12-29T12:00:00Z",
    "created_at": "2025-12-22T12:00:00Z"
  }
}
```

### Get Pending Invitations
**GET** `/api/v1/internal-employee/invitations`

**Headers:**
- `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "email": "employee@metatech.ae",
      "role": "user",
      "department": "Sales",
      "designation": "Sales Manager",
      "invited_by": "Admin User",
      "expires_at": "2025-12-29T12:00:00Z",
      "created_at": "2025-12-22T12:00:00Z"
    }
  ]
}
```

### Cancel Invitation
**DELETE** `/api/v1/internal-employee/invitations/{id}`

**Headers:**
- `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "message": "Invitation cancelled successfully"
}
```

## Web Routes

### Invitation Acceptance (Public)
- `GET /employee/invite/accept?email={email}&token={token}` - Show invitation acceptance form
- `POST /employee/invite/accept` - Accept invitation and create account

## Components

### Models
- **EmployeeInvitation**: Model for employee invitations with helper methods:
  - `isExpired()`: Check if invitation is expired
  - `isAccepted()`: Check if invitation is accepted
  - `isValid()`: Check if invitation is valid (not accepted and not expired)
  - `markAsAccepted()`: Mark invitation as accepted

### Services
- **EmployeeInvitationService**: Handles all invitation logic:
  - `createInvitation()`: Creates a new invitation
  - `verifyInvitation()`: Verifies invitation token validity
  - `acceptInvitation()`: Accepts invitation and creates user account
  - `getInvitationUrl()`: Generates invitation URL
  - `getPendingInvitations()`: Gets all pending invitations
  - `cancelInvitation()`: Cancels an invitation

### Controllers
- **EmployeeInvitationController** (Web): Handles invitation acceptance:
  - `showAcceptForm()`: Display invitation acceptance form
  - `acceptInvitation()`: Process invitation acceptance
- **InternalEmployeeController** (API): Handles invitation management:
  - `sendInvitation()`: Send invitation via API
  - `getInvitations()`: Get pending invitations
  - `cancelInvitation()`: Cancel an invitation

### Email
- **EmployeeInvitationMail**: Mailable class that sends invitation emails with:
  - Invitation link with token
  - Expiration notice (7 days)
  - Employee information (department, designation, role)

### Views
- `auth/employee-invite/accept.blade.php`: Invitation acceptance form
- `emails/employee-invitation.blade.php`: Email template

## Workflow

### For Super Admin (Sending Invitation)

1. Super Admin calls API endpoint to send invitation
2. System creates invitation record with:
   - Secure token (64 characters, hashed)
   - Expiration date (7 days from now)
   - Employee details (role, department, designation)
3. Email is sent to employee with invitation link
4. Employee receives email and clicks link

### For Employee (Accepting Invitation)

1. Employee clicks invitation link in email
2. Link opens acceptance form at `crm.metatech.ae/employee/invite/accept`
3. Employee fills in:
   - First Name (optional if provided in invitation)
   - Last Name (optional if provided in invitation)
   - Password
   - Password Confirmation
4. System validates invitation (not expired, not accepted)
5. System creates user account with:
   - Email from invitation
   - Hashed password
   - Role from invitation
   - Department/Designation from invitation
   - `is_metatech_employee` = true
   - `email_verified_at` = now() (marked as verified)
6. Invitation is marked as accepted
7. Employee is automatically logged in
8. Employee is redirected to Internal CRM dashboard

## Authorization

### Who Can Send Invitations?
- Product Owner (System Super Admin)
- Internal Super Admin
- Internal Admin

### Restrictions
- Only Internal Super Admin can invite other Super Admins
- Cannot invite users that already exist
- Cannot create duplicate pending invitations

## Security Measures

1. **Token Hashing**: Tokens are hashed using SHA256 before storage
2. **Expiration**: Invitations automatically expire after 7 days
3. **Single-Use**: Invitations can only be accepted once
4. **Email Verification**: Accounts are marked as verified upon invitation acceptance
5. **IP Tracking**: IP addresses are logged for audit purposes

## Usage Examples

### Send Invitation via API

```bash
curl -X POST http://crm.localhost:8000/api/v1/internal-employee/invite \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "newemployee@metatech.ae",
    "role": "user",
    "department": "Development",
    "designation": "Junior Developer"
  }'
```

### Accept Invitation

1. Employee receives email with link like:
   `http://crm.localhost:8000/employee/invite/accept?email=newemployee@metatech.ae&token={token}`

2. Employee fills form and submits

3. Account is created and employee is logged in

## Testing

### Manual Testing Steps

1. **Send Invitation**:
   - Login as Internal Super Admin
   - Call `/api/v1/internal-employee/invite` with employee details
   - Check email inbox for invitation

2. **Accept Invitation**:
   - Click invitation link from email
   - Fill in acceptance form
   - Verify account is created and user is logged in

3. **Test Expiration**:
   - Create invitation
   - Wait 7+ days (or manually set expires_at in database)
   - Try to accept (should fail)

4. **Test Single-Use**:
   - Accept an invitation
   - Try to accept same invitation again (should fail)

5. **Test Duplicate Prevention**:
   - Send invitation for email that already exists (should fail)
   - Send duplicate invitation for same email (should fail)

## Notes

- Invitation expiration: 7 days (configurable in `EmployeeInvitationService::$expirationDays`)
- All invitations are single-use only
- Employee accounts are automatically marked as email verified upon acceptance
- Invitation URLs work on `crm.metatech.ae` subdomain
- Employees are automatically logged in after accepting invitation

