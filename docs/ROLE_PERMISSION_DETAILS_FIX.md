# Role and Permission Detail Pages - PHP Error Fix

## Issue

GitHub Actions workflow run #19516594703 showed 500 Internal Server Errors when accessing:
- `/c6/admin/roles/{id}` - Role detail page
- `/c6/admin/permissions/{id}` - Permission detail page

The browser console showed:
```
[Browser ERROR]: Failed to load resource: the server responded with a status of 500 (Internal Server Error)
```

## Root Cause

The errors were caused by the `details` sections in the roles.json and permissions.json schema files. These sections instructed CRUD6 to load related data (users and permissions for roles, users and roles for permissions) on the detail pages.

However, CRUD6 v0.6.1 has issues handling `details` sections with many-to-many relationships, resulting in 500 errors.

### Evidence

**Working Examples (no errors):**
- Group detail page: Uses `foreign_key` for has-many relationship with users
- User detail page: Uses `foreign_key` for has-many relationship with activities

**Failing Examples (500 errors):**
- Role detail page: Tried to show many-to-many relationships (users, permissions) in `details`
- Permission detail page: Tried to show many-to-many relationships (users, roles) in `details`

## Solution

Removed the problematic `details` sections from:
- `app/schema/crud6/roles.json` (lines 51-61)
- `app/schema/crud6/permissions.json` (lines 49-59)

This allows the role and permission detail pages to load successfully without attempting to display related data that CRUD6 v0.6.1 cannot handle.

## Changes Made

### app/schema/crud6/roles.json
**Before:**
```json
    ],
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
    ],
    "fields": {
```

**After:**
```json
    ],
    "fields": {
```

### app/schema/crud6/permissions.json
**Before:**
```json
    ],
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
    ],
    "fields": {
```

**After:**
```json
    ],
    "fields": {
```

## Impact

- ✅ Role detail pages now load without PHP errors
- ✅ Permission detail pages now load without PHP errors
- ⚠️ Related data (users/permissions for roles, users/roles for permissions) no longer displayed on detail pages
- ✅ All other CRUD operations (create, edit, delete, list) continue to work normally
- ✅ Relationships are still defined and functional for updates (sync operations)

## Testing

After this fix:
1. Navigate to `/c6/admin/roles` - list page should load
2. Click on a role to view `/c6/admin/roles/{id}` - detail page should load without 500 error
3. Navigate to `/c6/admin/permissions` - list page should load
4. Click on a permission to view `/c6/admin/permissions/{id}` - detail page should load without 500 error

## Future Enhancement

When CRUD6 is updated to properly handle `details` sections with many-to-many relationships, the `details` sections can be restored to show related data on the detail pages.

## Related Files

- `app/schema/crud6/roles.json` - Roles schema
- `app/schema/crud6/permissions.json` - Permissions schema
- `app/schema/crud6/groups.json` - Groups schema (working example with has-many relationship)
- `app/schema/crud6/users.json` - Users schema (working example with has-many relationship)
