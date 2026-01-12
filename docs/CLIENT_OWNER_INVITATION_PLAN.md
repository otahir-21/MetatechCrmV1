# Client Owner Invitation System - Implementation Plan

## Overview
Convert the current company creation flow from direct user creation to an **invitation-based system** where:
- Product Owner creates a company and invites the Company Owner (Super Admin)
- Company Owner receives an email invitation
- Company Owner clicks link, sets password, and activates account
- Company Owner can then access their company portal (e.g., `elite.localhost:8000`)

## Current Flow (Direct Creation)
1. Product Owner fills form: company_name, subdomain, email, password, first_name, last_name
2. System creates User directly with password
3. Company record is created
4. Company Owner can login immediately

## New Flow (Invitation-Based)
1. Product Owner fills form: company_name, subdomain, email, first_name, last_name (NO PASSWORD)
2. System creates Company record
3. System sends invitation email to Company Owner
4. Company Owner receives email with invitation link
5. Company Owner clicks link → Sets password → Account activated
6. Company Owner can login and access company portal

## Implementation Steps

### Phase 1: Create Company Owner Invitation System

#### 1.1 Database Table
**New Table:** `company_owner_invitations`
- `id`
- `email` (unique)
- `token` (hashed, unique)
- `company_name`
- `subdomain`
- `first_name`
- `last_name`
- `invited_by` (Product Owner user_id)
- `accepted` (boolean)
- `accepted_at` (timestamp, nullable)
- `expires_at` (timestamp, nullable)
- `ip_address` (nullable)
- `created_at`, `updated_at`

#### 1.2 Model
- `app/Models/CompanyOwnerInvitation.php`
- Relationships: `inviter()` → User

#### 1.3 Service
- `app/Services/CompanyOwnerInvitationService.php`
- Methods:
  - `createInvitation()` - Create invitation when company is created
  - `verifyInvitation()` - Verify token and check validity
  - `acceptInvitation()` - Accept invitation, create user, set password
  - `getInvitationUrl()` - Generate invitation URL with correct subdomain

#### 1.4 Mail
- `app/Mail/CompanyOwnerInvitationMail.php`
- Email template: `resources/views/emails/company-owner-invitation.blade.php`
- Contains: Company name, invitation link, expiry info

#### 1.5 Controllers
- **API Controller:** `app/Http/Controllers/Api/V1/CompanyOwnerInvitationController.php`
  - `sendInvitation()` - Send invitation (called during company creation)
  - `getInvitationDetails()` - Get invitation details
  
- **Web Controller:** `app/Http/Controllers/CompanyOwnerInvitationViewController.php`
  - `showAcceptForm()` - Show password setup form
  - `acceptInvitation()` - Handle invitation acceptance

#### 1.6 Request Validation
- `app/Http/Requests/CompanyOwnerInvitationCreateRequest.php`
- `app/Http/Requests/CompanyOwnerInvitationAcceptRequest.php`

### Phase 2: Modify Company Creation Flow

#### 2.1 Update CompanyService
- Modify `createCompanySuperAdmin()` → `createCompanyAndSendInvitation()`
- Remove password field requirement
- Create Company record first
- Create invitation
- Send invitation email
- Return company info (not user info)

#### 2.2 Update Company Creation Form
- Remove password and password_confirmation fields
- Update UI text: "Create Company & Send Invitation"

#### 2.3 Update API Endpoint
- `POST /api/v1/company/create` - Now creates company and sends invitation

### Phase 3: Invitation Acceptance Flow

#### 3.1 Routes
```php
// Public routes (no auth required)
Route::get('/company-invite/accept', [CompanyOwnerInvitationViewController::class, 'showAcceptForm'])->name('company.invite.show');
Route::post('/company-invite/accept', [CompanyOwnerInvitationViewController::class, 'acceptInvitation'])->name('company.invite.accept');
```

#### 3.2 Invitation Acceptance Page
- URL: `http://{subdomain}.localhost:8000/company-invite/accept?email=xxx&token=xxx`
- Form fields:
  - First Name (pre-filled, editable)
  - Last Name (pre-filled, editable)
  - Password
  - Password Confirmation
  - Company Name (display only)
  - Subdomain (display only)

#### 3.3 On Acceptance
- Create User account with:
  - Email from invitation
  - Password from form
  - Company name, subdomain
  - Role: `super_admin`
  - `is_metatech_employee`: false
  - `status`: active
  - `email_verified_at`: now()
- Mark invitation as accepted
- Log audit event (invitation_accepted)
- Redirect to login page with success message

### Phase 4: Update Audit Logging

#### 4.1 Log Events
- `company_owner_invitation_sent` - When Product Owner creates company
- `company_owner_invitation_accepted` - When Company Owner accepts
- Include: company_name, subdomain, email, invited_by

## Data Flow Diagram

```
Product Owner Dashboard
    ↓
Create Company Form (no password)
    ↓
CompanyService::createCompanyAndSendInvitation()
    ↓
Create Company Record
    ↓
Create CompanyOwnerInvitation Record
    ↓
Send Email (CompanyOwnerInvitationMail)
    ↓
Company Owner receives email
    ↓
Clicks invitation link
    ↓
CompanyOwnerInvitationViewController::showAcceptForm()
    ↓
User fills password form
    ↓
CompanyOwnerInvitationService::acceptInvitation()
    ↓
Create User Account
    ↓
Mark invitation accepted
    ↓
Redirect to Login
    ↓
Company Owner can login at: {subdomain}.localhost:8000
```

## Key Differences from Employee Invitation

| Feature | Employee Invitation | Company Owner Invitation |
|---------|-------------------|-------------------------|
| Subdomain | Always `crm.localhost` | Uses company subdomain (e.g., `elite.localhost`) |
| Company Record | No company | Creates company record first |
| User Role | user/admin/super_admin | Always `super_admin` |
| User Type | `is_metatech_employee = true` | `is_metatech_employee = false` |
| Login URL | `crm.localhost:8000/login` | `{subdomain}.localhost:8000/login` |
| Dashboard | `/internal/dashboard` | `/company-dashboard` |

## Security Considerations

1. **Token Security:**
   - Use SHA256 hashed tokens (like employee invitations)
   - Tokens expire after 7 days
   - Single-use tokens (cannot be reused)

2. **Subdomain Validation:**
   - Invitation link must match company subdomain
   - Redirect if accessed from wrong subdomain

3. **Email Uniqueness:**
   - Cannot create invitation if email already exists as user
   - Cannot create invitation if pending invitation exists

4. **Audit Logging:**
   - Log all invitation events
   - Track who created company
   - Track when invitation accepted

## Files to Create/Modify

### New Files:
- `database/migrations/xxxx_create_company_owner_invitations_table.php`
- `app/Models/CompanyOwnerInvitation.php`
- `app/Services/CompanyOwnerInvitationService.php`
- `app/Http/Controllers/Api/V1/CompanyOwnerInvitationController.php`
- `app/Http/Controllers/CompanyOwnerInvitationViewController.php`
- `app/Http/Requests/CompanyOwnerInvitationCreateRequest.php`
- `app/Http/Requests/CompanyOwnerInvitationAcceptRequest.php`
- `app/Mail/CompanyOwnerInvitationMail.php`
- `resources/views/company-invite/accept.blade.php`
- `resources/views/emails/company-owner-invitation.blade.php`

### Modified Files:
- `app/Services/CompanyService.php` - Update createCompanySuperAdmin method
- `app/Http/Controllers/Api/V1/CompanyController.php` - Update create method
- `resources/views/company/create.blade.php` - Remove password fields
- `routes/api.php` - Add invitation routes if needed
- `routes/web.php` - Add invitation acceptance routes
- `app/Services/AuditLogService.php` - Add company owner invitation logging

## Testing Checklist

- [ ] Product Owner can create company without password
- [ ] Invitation email is sent correctly
- [ ] Invitation link uses correct subdomain
- [ ] Company Owner can access invitation acceptance page
- [ ] Password validation works correctly
- [ ] User account created with correct company/subdomain
- [ ] Company Owner can login after acceptance
- [ ] Invitation tokens expire correctly
- [ ] Cannot reuse invitation tokens
- [ ] Audit logs are created for all events

