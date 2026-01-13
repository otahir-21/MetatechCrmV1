# Internal-Only Items & Fine-Grained Sharing Implementation

## Overview
This document outlines the implementation of internal-only content marking and fine-grained sharing controls in the Metatech CRM system.

## Features Implemented

### 1. Internal-Only Content Marking
- Employees can mark tasks and comments as "internal-only"
- Internal-only items are automatically hidden from client users
- Only internal team members can see internal-only content

### 2. Fine-Grained Sharing Controls
- Admins can share specific documents/resources with specific client users
- Granular permissions: view, comment, edit, download
- Expiration dates for temporary access
- Bulk sharing capabilities

## Database Schema

### New Tables Created:

#### `document_shares`
- `id` - Primary key
- `document_type` - Type of document (task_attachment, project_file, etc.)
- `document_id` - ID of the document
- `shared_with_user_id` - User receiving access
- `shared_by_user_id` - User granting access
- `permission` - Access level (view, edit, download)
- `expires_at` - Optional expiration date
- `created_at`, `updated_at`

#### `project_shares`
- `id` - Primary key
- `project_id` - Project containing the resource
- `resource_type` - Type of resource (task, comment, file, milestone)
- `resource_id` - ID of the specific resource
- `shared_with_user_id` - User receiving access
- `shared_by_user_id` - User granting access
- `permission` - Access level (view, comment, edit)
- `notes` - Optional sharing notes
- `expires_at` - Optional expiration date
- `created_at`, `updated_at`

### Modified Tables:

#### `tasks` table
- Added `is_internal_only` boolean column (default: false)

#### `task_comments` table
- Added `is_internal_only` boolean column (default: false)

## Models

### New Models:
- `DocumentShare` - Manages document sharing permissions
- `ProjectShare` - Manages project resource sharing permissions

### Updated Models:
- `Task` - Added `is_internal_only` field and visibility scopes
- `TaskComment` - Added `is_internal_only` field and visibility scopes

## Services

### `SharingService`
Handles all sharing-related business logic:

**Methods:**
- `shareDocument()` - Share a document with a user
- `shareProjectResource()` - Share a project resource with a user
- `revokeDocumentAccess()` - Revoke document access
- `revokeProjectResourceAccess()` - Revoke project resource access
- `getDocumentShares()` - Get list of users with document access
- `getProjectResourceShares()` - Get list of users with resource access
- `hasDocumentAccess()` - Check if user has document access
- `hasProjectResourceAccess()` - Check if user has resource access
- `toggleTaskInternalStatus()` - Toggle task internal-only status
- `bulkShareProjectResource()` - Share resource with multiple users

### Updated Services:
- `TaskService` - Updated to filter internal-only items for client users

## API Endpoints

### Sharing Endpoints
Base path: `/api/v1/sharing`

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/project-resource/share` | Share a project resource |
| POST | `/project-resource/revoke` | Revoke resource access |
| GET | `/project-resource/list` | Get list of resource shares |
| POST | `/project-resource/bulk-share` | Bulk share with multiple users |

### Task Endpoints (Updated)
| Method | Endpoint | Description |
|--------|----------|-------------|
| PATCH | `/tasks/{id}/toggle-internal` | Toggle task internal-only status |

## Request/Response Examples

### Share a Project Resource
```json
POST /api/v1/sharing/project-resource/share
{
  "project_id": 1,
  "resource_type": "task",
  "resource_id": 123,
  "user_id": 456,
  "permission": "view",
  "notes": "Shared for review",
  "expires_at": "2026-02-01T00:00:00Z"
}
```

### Toggle Task Internal Status
```json
PATCH /api/v1/tasks/123/toggle-internal
Response:
{
  "message": "Task internal status updated",
  "data": {
    "task_id": 123,
    "is_internal_only": true
  }
}
```

## Security & Access Control

### Visibility Rules:
1. **Internal Employees**: Can see everything (internal and client-visible)
2. **Client Users**: Can only see:
   - Non-internal tasks and comments
   - Resources explicitly shared with them

### Permission Checks:
- Internal-only marking: Only internal employees
- Sharing resources: Only internal employees (admins/managers)
- Viewing shares: Only internal employees
- Accessing resources: Based on sharing rules

## Audit Logging

All sharing activities are logged:
- When items are marked as internal-only
- When resources are shared
- When access is revoked
- Includes: actor, target user, resource details, timestamp, IP address

## Use Cases

### Use Case 1: Internal Discussion
```
Scenario: Team wants to discuss budget internally
1. Employee creates task "Review client budget"
2. Marks as "Internal Only"
3. Team discusses in comments (also internal)
Result: Client never sees this task or discussions
```

### Use Case 2: Selective Document Sharing
```
Scenario: Share final invoice only with client's finance person
1. Admin uploads "Invoice_Q1_2026.pdf"
2. Shares with john@clientcompany.com (view only)
3. Sets expiration: 30 days
Result: Only John can view, expires automatically
```

### Use Case 3: Gradual Access
```
Scenario: Project has internal planning phase
1. Create project tasks (all internal-only)
2. Team plans and works internally
3. When ready, share specific tasks with client
4. Client sees curated project view
Result: Professional, controlled client experience
```

## Frontend Integration (To Be Implemented)

### UI Components Needed:
1. **Internal-Only Checkbox**
   - On task creation form
   - On comment form
   - Toggle on existing items

2. **Sharing Modal**
   - User selection dropdown
   - Permission level selector
   - Expiration date picker
   - Notes field

3. **Visual Indicators**
   - Badge/icon for internal-only items
   - "Shared with X users" display
   - Lock icon for internal content

4. **Share Management**
   - List of shared users
   - Revoke access button
   - Expiration status

## Testing Checklist

- [ ] Client users cannot see internal-only tasks
- [ ] Client users cannot see internal-only comments
- [ ] Sharing grants correct access
- [ ] Revocation removes access
- [ ] Expired shares are not accessible
- [ ] Bulk sharing works correctly
- [ ] Audit logs are created
- [ ] API returns filtered data correctly
- [ ] Internal employees see everything
- [ ] Client users see only shared items

## Migration Instructions

### To Apply These Changes:
```bash
# Run migrations
php artisan migrate

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Database Backup Recommended
```bash
# Backup before migration
mysqldump -u root -p metatech_crm > backup_before_sharing.sql
```

## Performance Considerations

- Indexes added on `is_internal_only` columns for fast filtering
- Composite indexes on sharing tables for efficient lookups
- Active shares scope prevents checking expired shares

## Future Enhancements

1. **Notifications**: Notify users when resources are shared with them
2. **Share History**: Track historical shares (currently logs in audit)
3. **Batch Operations**: UI for bulk sharing multiple resources
4. **Share Templates**: Save common sharing configurations
5. **Client Portal**: Dedicated view showing only shared items

## Support & Troubleshooting

### Common Issues:

**Client sees internal items:**
- Check `is_internal_only` flag in database
- Verify `visibleToClients()` scope is applied in queries

**Sharing not working:**
- Check user has `is_metatech_employee = 1`
- Verify share record exists in `project_shares` table
- Check expiration date

**Performance slow:**
- Ensure indexes exist on `is_internal_only` columns
- Review query execution plans

## Credits

Implemented: January 2026
Version: 1.0.0
Status: Backend Complete, Frontend Pending
