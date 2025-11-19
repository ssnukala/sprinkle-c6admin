# Role and Permission Detail Pages - Investigation and Fix

## Issue

GitHub Actions workflow run #19516594703 showed 500 Internal Server Errors when accessing:
- `/c6/admin/roles/{id}` - Role detail page
- `/c6/admin/permissions/{id}` - Permission detail page

The browser console showed:
```
[Browser ERROR]: Failed to load resource: the server responded with a status of 500 (Internal Server Error)
```

## Investigation

### Initial Hypothesis
The errors appeared to be caused by the `details` sections in roles.json and permissions.json schemas, which attempted to display related data through many-to-many relationships.

### Analysis

**Working Examples (no errors in logs):**
- **Group detail page**: Uses `details` with `foreign_key` for has-many relationship with users
- **User detail page**: Uses `details` with BOTH:
  - `foreign_key` for has-many relationship (activities)
  - NO `foreign_key` for many-to-many relationships (roles, permissions)

**Failing Examples (500 errors in logs):**
- **Role detail page**: Uses `details` without `foreign_key` for many-to-many relationships (users, permissions)
- **Permission detail page**: Uses `details` without `foreign_key` for many-to-many relationships (users, roles)

### Key Finding

Since the **user detail page works** with many-to-many relationships in its `details` section (roles and permissions), the schema configuration is NOT the root cause. The issue must be in:
1. CRUD6 v0.6.1.4 handling of specific models (roles/permissions)
2. Database structure or model relationships
3. Permission checks on these specific endpoints

## Current Status

**Details sections have been RESTORED** to both schemas as they are required to display all components:

### app/schema/crud6/roles.json
```json
"details": [
    {
        "model": "users",
        "list_fields": ["user_name", "first_name", "last_name", "email", "flag_enabled"],
        "title": "ROLE.USERS"
    },
    {
        "model": "permissions",
        "list_fields": ["slug", "name", "description"],
        "title": "ROLE.PERMISSIONS"
    }
]
```

### app/schema/crud6/permissions.json
```json
"details": [
    {
        "model": "users",
        "list_fields": ["user_name", "first_name", "last_name", "email"],
        "title": "PERMISSION.USERS"
    },
    {
        "model": "roles",
        "list_fields": ["name", "slug", "description"],
        "title": "ROLE.2"
    }
]
```

## CRUD6 Version

Testing is being done with **CRUD6 v0.6.1.4** (the latest version).

The constraint in composer.json is `"ssnukala/sprinkle-crud6": "^0.6.1"` which allows versions from 0.6.1 up to (but not including) 0.7.0.

## Next Steps

1. **Verify CRUD6 v0.6.1.4** properly handles role and permission detail pages with many-to-many relationships
2. **Test the endpoints** directly:
   - GET `/api/crud6/roles/1`
   - GET `/api/crud6/roles/1/permissions`
   - GET `/api/crud6/permissions/1`
   - GET `/api/crud6/permissions/1/roles`
3. **Review CRUD6 v0.6.1.4 changes** to see if the detail page handling has been updated
4. **Check permission requirements** for viewing role and permission details

## Testing

After restoring the details sections:
1. Navigate to `/c6/admin/roles` - list page should load
2. Click on a role to view `/c6/admin/roles/{id}` - verify detail page loads
3. Check if related users and permissions are displayed
4. Navigate to `/c6/admin/permissions` - list page should load
5. Click on a permission to view `/c6/admin/permissions/{id}` - verify detail page loads
6. Check if related users and roles are displayed

## Related Files

- `app/schema/crud6/roles.json` - Roles schema (details restored)
- `app/schema/crud6/permissions.json` - Permissions schema (details restored)
- `app/schema/crud6/groups.json` - Groups schema (working example with has-many)
- `app/schema/crud6/users.json` - Users schema (working example with many-to-many)
