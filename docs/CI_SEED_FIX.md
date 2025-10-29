# CI Workflow Seed Fix

## Issue
The CI workflow was failing with the following error:
```
Error: ] Class is not a valid seed :
         UserFrosting\Sprinkle\CRUD6\Database\Seeds\DefaultRoles
```

## Root Cause
The workflow was attempting to seed CRUD6-specific permissions and roles:
```bash
php bakery seed UserFrosting\\Sprinkle\\CRUD6\\Database\\Seeds\\DefaultRoles --force
php bakery seed UserFrosting\\Sprinkle\\CRUD6\\Database\\Seeds\\DefaultPermissions --force
```

These seeders:
1. Create generic CRUD6 permissions (create_crud6, delete_crud6, update_crud6_field, etc.)
2. Create a "crud6-admin" role
3. Are not necessary for C6Admin functionality

## Solution
Removed the CRUD6 seed commands from `.github/workflows/integration-test.yml`.

## Why This Fix Is Correct

### C6Admin Architecture
According to the project's architecture (as documented in `.github/copilot-instructions.md`):

> C6Admin is a **frontend-focused sprinkle** that provides:
> - **Complete Vue.js admin interface**: 14 pages, 14 composables, 20+ TypeScript interfaces
> - **JSON schema definitions**: For users, roles, groups, permissions, activities
> - **Utility controllers**: Dashboard API and system configuration utilities
> - **CRUD delegation to CRUD6**: All CRUD operations handled by sprinkle-crud6

Key points:
- C6Admin provides the **admin interface and schemas**
- CRUD6 provides the **CRUD operations and API endpoints**
- All CRUD routes are at `/api/crud6/{model}` endpoints
- Controllers in C6Admin are limited to Dashboard and Config utilities

### Permissions Model
C6Admin uses the standard UserFrosting Account sprinkle permissions:
- Users, Groups, Roles, Permissions from Account sprinkle
- No need for CRUD6-specific permissions
- The Account sprinkle seeds provide all necessary base permissions

### What CRUD6 Seeders Create
The CRUD6 seeders create generic permissions that are not specific to C6Admin:
- `create_crud6` - Create a new crud6
- `delete_crud6` - Delete a crud6
- `update_crud6_field` - Edit basic properties of any crud6
- `uri_crud6` - View the crud6 page of any crud6
- `uri_crud6_list` - View a page containing a list of crud6s
- `view_crud6_field` - View certain properties of any crud6

These are placeholder permissions from CRUD6's example data, not actual permissions needed by C6Admin.

## Updated Workflow
The workflow now only seeds Account sprinkle data:
```bash
cd userfrosting
# Seed Account sprinkle data (required base data)
php bakery seed UserFrosting\\Sprinkle\\Account\\Database\\Seeds\\DefaultGroups --force
php bakery seed UserFrosting\\Sprinkle\\Account\\Database\\Seeds\\DefaultPermissions --force
php bakery seed UserFrosting\\Sprinkle\\Account\\Database\\Seeds\\DefaultRoles --force
php bakery seed UserFrosting\\Sprinkle\\Account\\Database\\Seeds\\UpdatePermissions --force
```

This provides all the necessary permissions and roles for C6Admin to function correctly.

## Files Changed
- `.github/workflows/integration-test.yml` - Removed CRUD6 seed commands (lines 149-151)

## Related Information
- GitHub Actions Run: https://github.com/ssnukala/sprinkle-c6admin/actions/runs/18896347370/job/53934666348
- CRUD6 Repository: https://github.com/ssnukala/sprinkle-crud6
- C6Admin Architecture: See `.github/copilot-instructions.md`
