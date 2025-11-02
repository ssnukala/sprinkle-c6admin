# Schema Table Names Update

**Date:** 2025-11-02  
**Issue:** Update schema files to use correct UserFrosting 6 table names

## Summary

Updated all schema files in `app/schema/crud6/` to use the correct table names as defined in UserFrosting 6 database migrations, specifically for pivot tables in many-to-many relationships.

## Problem

The schema files were using singular naming for pivot tables:
- `permission_role` (incorrect)
- `role_user` (incorrect)

However, UserFrosting 6 migrations define these tables with plural naming:
- `permission_roles` (correct)
- `role_users` (correct)

## Changes Made

### 1. app/schema/crud6/roles.json

Updated two pivot table references:

```diff
- "pivot_table": "permission_role"
+ "pivot_table": "permission_roles"

- "pivot_table": "role_user"
+ "pivot_table": "role_users"
```

**Relationships affected:**
- roles ↔ permissions (many-to-many)
- roles ↔ users (many-to-many)

### 2. app/schema/crud6/permissions.json

Updated three pivot table references:

```diff
- "pivot_table": "permission_role"
+ "pivot_table": "permission_roles"

- "first_pivot_table": "permission_role"
+ "first_pivot_table": "permission_roles"

- "second_pivot_table": "role_user"
+ "second_pivot_table": "role_users"
```

**Relationships affected:**
- permissions ↔ roles (many-to-many)
- permissions → users (through roles, many-to-many-through)

### 3. Other Schema Files

The following files were already correct or did not require changes:

- **users.json**: Already using correct table names (updated in previous PR)
  - Uses `role_users` ✓
  - Uses `permission_roles` ✓
  
- **groups.json**: No pivot tables, no changes needed
  
- **activities.json**: No pivot tables, no changes needed

## UserFrosting 6 Table Structure

Based on the official UserFrosting 6 migrations from `userfrosting/sprinkle-account@6.0`:

### Main Tables
- `users` - User accounts
- `roles` - User roles
- `groups` - User groups
- `permissions` - Access permissions
- `activities` - User activity log

### Pivot Tables
- `role_users` - Many-to-many relationship between roles and users
- `permission_roles` - Many-to-many relationship between permissions and roles

## Verification

All schema files have been validated:

```bash
# JSON syntax validation
✓ activities.json
✓ groups.json
✓ permissions.json
✓ roles.json
✓ users.json

# Pivot table names verification
✓ All pivot tables now use correct UserFrosting 6 names
✓ All relationships properly configured
```

## Testing

To verify the schema files in a UserFrosting 6 application:

1. Ensure the application has the correct database migrations applied
2. The CRUD6 sprinkle will now correctly reference the pivot tables
3. Many-to-many relationships should work properly with:
   - Users ↔ Roles
   - Roles ↔ Permissions
   - Users → Permissions (through Roles)

## References

- UserFrosting 6 Migrations: https://github.com/userfrosting/sprinkle-account/tree/6.0/app/src/Database/Migrations/v400
- Specific migration files reviewed:
  - `RoleUsersTable.php` - Defines `role_users` table
  - `PermissionRolesTable.php` - Defines `permission_roles` table
  - `UsersTable.php` - Users table structure
  - `RolesTable.php` - Roles table structure
  - `PermissionsTable.php` - Permissions table structure
  - `GroupsTable.php` - Groups table structure
  - `ActivitiesTable.php` - Activities table structure

## Impact

This change ensures that C6Admin's schema definitions align perfectly with UserFrosting 6's actual database structure, preventing potential runtime errors when CRUD6 attempts to query these relationships.

**Breaking Change:** No - This is a bug fix that aligns the schemas with the actual database structure. Systems using the incorrect table names would have already been experiencing issues.
