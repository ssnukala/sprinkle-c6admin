# CRUD6 PR #152 Schema Updates

## Overview

This document describes the critical schema updates made to C6Admin schema files to comply with the architectural changes introduced in [sprinkle-crud6 PR #152](https://github.com/ssnukala/sprinkle-crud6/pull/152).

## Critical Changes in CRUD6 PR #152

### What Changed

CRUD6 has been refactored to be **100% generic** and **JSON schema-driven**:

- ❌ **Removed** all hardcoded relationship logic (especially for "permissions")
- ❌ **Removed** hardcoded table names like `permission_role` and `role_permission`
- ✅ **Made** system fully JSON schema-driven
- ✅ **Required** all relationships to be explicitly defined in schema files

### Why This Matters

Previously, CRUD6 had special hardcoded logic to handle the `users → roles → permissions` relationship. This meant the schema files didn't need to explicitly define the permissions relationship for users.

**After PR #152**, this hardcoded logic was removed to make the system truly generic and reusable for any relationship pattern. Now, ALL relationships must be explicitly defined in the schema files.

## Schema Files Updated

### 1. `app/schema/crud6/users.json`

**Added explicit `permissions` relationship:**

```json
{
  "name": "permissions",
  "type": "belongs_to_many_through",
  "through": "UserFrosting\\Sprinkle\\Account\\Database\\Models\\Role",
  "first_pivot_table": "role_user",
  "first_foreign_key": "user_id",
  "first_related_key": "role_id",
  "second_pivot_table": "permission_role",
  "second_foreign_key": "role_id",
  "second_related_key": "permission_id",
  "title": "PERMISSION.2"
}
```

**Relationship Path:** `users → role_user → roles → permission_role → permissions`

**Why:** Users don't have direct permissions; they have permissions through their roles. This is a belongs-to-many-through relationship that requires two pivot tables.

### 2. `app/schema/crud6/permissions.json`

**Added explicit `users` relationship:**

```json
{
  "name": "users",
  "type": "belongs_to_many_through",
  "through": "UserFrosting\\Sprinkle\\Account\\Database\\Models\\Role",
  "first_pivot_table": "permission_role",
  "first_foreign_key": "permission_id",
  "first_related_key": "role_id",
  "second_pivot_table": "role_user",
  "second_foreign_key": "role_id",
  "second_related_key": "user_id",
  "title": "PERMISSION.USERS"
}
```

**Relationship Path:** `permissions → permission_role → roles → role_user → users`

**Why:** Permissions don't directly belong to users; users have permissions through roles. This is the reverse of the users-to-permissions relationship.

## Relationship Types

### Many-to-Many (`many_to_many`)

For direct relationships through a single pivot table.

**Example:** `users ↔ roles` via `role_user` pivot table

**Required Configuration:**
- `pivot_table`: Name of the pivot/junction table
- `foreign_key`: Column in pivot table referencing the parent model
- `related_key`: Column in pivot table referencing the related model

### Belongs-to-Many-Through (`belongs_to_many_through`)

For nested relationships through an intermediate model and two pivot tables.

**Example:** `users → roles → permissions` via `role_user` and `permission_role` pivot tables

**Required Configuration:**
- `through`: Fully qualified class name of the intermediate model
- `first_pivot_table`: First pivot table (parent → through)
- `first_foreign_key`: Column in first pivot for parent ID
- `first_related_key`: Column in first pivot for through model ID
- `second_pivot_table`: Second pivot table (through → related)
- `second_foreign_key`: Column in second pivot for through model ID
- `second_related_key`: Column in second pivot for related model ID

### One-to-Many (Default)

Simple foreign key relationships defined in the `details` array.

**Example:** `groups → users` where users have a `group_id` foreign key

**Configuration:** Only defined in `details` array with `foreign_key`.

## Validation

All schema files have been validated for JSON syntax:

```bash
✓ activities.json valid
✓ groups.json valid
✓ permissions.json valid
✓ roles.json valid
✓ users.json valid
```

## Testing

These changes ensure that:

1. **Users API** (`/api/crud6/users/1/permissions`) correctly fetches permissions through roles
2. **Permissions API** (`/api/crud6/permissions/1/users`) correctly fetches users that have the permission
3. **Roles API** continues to work with existing many-to-many relationships
4. **Groups API** continues to work with simple one-to-many relationships

## References

- [CRUD6 PR #152](https://github.com/ssnukala/sprinkle-crud6/pull/152)
- [CRUD6 Relationship Configuration Guide](https://github.com/ssnukala/sprinkle-crud6/blob/main/.archive/RELATIONSHIP_CONFIGURATION_GUIDE.md)

## Migration Notes

If you have custom models that previously relied on hardcoded relationship logic, you must now:

1. Add explicit relationship definitions in the `relationships` array
2. Use the correct relationship type (`many_to_many` or `belongs_to_many_through`)
3. Provide all required configuration parameters
4. Test the API endpoints to ensure relationships work correctly

## Impact

These changes have **NO breaking impact** on the frontend. The API endpoints remain the same:
- `GET /api/crud6/users/{id}/permissions`
- `GET /api/crud6/permissions/{id}/users`

The only change is that the backend now uses the explicit schema configuration instead of hardcoded logic.
