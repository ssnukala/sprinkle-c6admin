# Schema Updates: Toggle Buttons and Pivot Table Management

**Date:** 2025-11-12  
**Related PRs:**
- [sprinkle-crud6 PR #165](https://github.com/ssnukala/sprinkle-crud6/pull/165) - Toggle buttons and pivot actions
- [sprinkle-crud6 PR #166](https://github.com/ssnukala/sprinkle-crud6/pull/166) - ORM-aligned refactoring

## Overview

This update implements modern schema patterns from CRUD6:

1. **Boolean Toggle Switches** - Modern UI for boolean fields with separated type and presentation
2. **Automatic Pivot Table Management** - Schema-driven relationship actions
3. **Nested Lookup Objects** - Cleaner organization of lookup configuration
4. **Context-Specific Visibility** - Precise control over when fields appear

## Changes Made

### 1. Boolean Toggle Switches

#### Updated Pattern (PR #166)

**Before:**
```json
{
  "flag_enabled": {
    "type": "boolean-tgl",
    "label": "Enabled"
  }
}
```

**After:**
```json
{
  "flag_enabled": {
    "type": "boolean",
    "ui": "toggle",
    "label": "Enabled"
  }
}
```

#### Benefits
- Separates data type from UI presentation
- Allows multiple UI options: `"toggle"`, `"checkbox"`, `"select"`
- More flexible and ORM-aligned

### 2. Nested Lookup Objects

#### Updated Pattern (PR #166)

**Before:**
```json
{
  "role_ids": {
    "type": "multiselect",
    "lookup_model": "roles",
    "lookup_id": "id",
    "lookup_desc": "name"
  }
}
```

**After:**
```json
{
  "role_ids": {
    "type": "multiselect",
    "lookup": {
      "model": "roles",
      "id": "id",
      "desc": "name"
    }
  }
}
```

#### Benefits
- Cleaner, more organized structure
- Groups related configuration
- Familiar pattern from other frameworks (Prisma, TypeORM)

### 3. Context-Specific Visibility

#### Updated Pattern (PR #166)

**Before:**
```json
{
  "user_name": {
    "type": "string",
    "editable": true,
    "viewable": true,
    "listable": true
  }
}
```

**After:**
```json
{
  "user_name": {
    "type": "string",
    "show_in": ["list", "form", "detail"]
  }
}
```

**Supported Contexts:**
- `list` - Table/grid view
- `create` - Create form (when adding new records)
- `edit` - Edit form (when modifying existing records)
- `detail` - Read-only detail/view page
- `form` - Shorthand for both create and edit

#### Benefits
- Clear, explicit visibility control
- Context-based instead of overlapping flags
- Better security (e.g., password only in forms, not in list/detail)
- More intuitive than editable/viewable/listable

#### Examples

**Password Field - Form Only:**
```json
{
  "password": {
    "type": "password",
    "show_in": ["create", "edit"]  // Hidden from list and detail for security
  }
}
```

**ID Field - Detail Only:**
```json
{
  "id": {
    "type": "integer",
    "show_in": ["detail"]  // Not needed in forms or lists
  }
}
```

**Toggle Field - Everywhere:**
```json
{
  "flag_enabled": {
    "type": "boolean",
    "ui": "toggle",
    "show_in": ["list", "form", "detail"]
  }
}
```

### 4. Pivot Table Management

Automatic management of pivot table entries through relationship actions defined in schemas.

#### users.json - Role Management

```json
{
  "relationships": [{
    "name": "roles",
    "actions": {
      "on_create": {
        "attach": [{
          "related_id": 2,
          "description": "Assign default 'User' role to new users",
          "pivot_data": {
            "created_at": "now",
            "updated_at": "now"
          }
        }]
      },
      "on_update": {
        "sync": "role_ids",
        "description": "Sync user roles from form input"
      },
      "on_delete": {
        "detach": "all",
        "description": "Remove all role associations when user is deleted"
      }
    }
  }]
}
```

**What This Does:**
- **Creating a user:** Automatically assigns role ID 2 (default "User" role)
- **Updating a user:** Syncs roles from the `role_ids` field in the form
- **Deleting a user:** Removes all entries from `role_users` pivot table

#### roles.json - Permission Management

```json
{
  "relationships": [{
    "name": "permissions",
    "actions": {
      "on_update": {
        "sync": "permission_ids",
        "description": "Sync role permissions from form input"
      },
      "on_delete": {
        "detach": "all",
        "description": "Remove all permission associations when role is deleted"
      }
    }
  }]
}
```

**What This Does:**
- **Updating a role:** Syncs permissions from the `permission_ids` field
- **Deleting a role:** Removes all entries from `permission_roles` pivot table

#### permissions.json - Role Management

```json
{
  "relationships": [{
    "name": "roles",
    "actions": {
      "on_update": {
        "sync": "role_ids",
        "description": "Sync permission roles from form input"
      },
      "on_delete": {
        "detach": "all",
        "description": "Remove all role associations when permission is deleted"
      }
    }
  }]
}
```

**What This Does:**
- **Updating a permission:** Syncs roles from the `role_ids` field
- **Deleting a permission:** Removes all entries from `permission_roles` pivot table

## Action Types

### on_create
Triggered after creating a new record.

**Actions:**
- `attach`: Add related records to pivot table

**Example:**
```json
{
  "on_create": {
    "attach": [{
      "related_id": 2,
      "pivot_data": {
        "created_at": "now"
      }
    }]
  }
}
```

### on_update
Triggered after updating a record.

**Actions:**
- `sync`: Synchronize related records from form field

**Example:**
```json
{
  "on_update": {
    "sync": "role_ids",
    "description": "Sync roles from form"
  }
}
```

### on_delete
Triggered before deleting a record.

**Actions:**
- `detach`: Remove related records (use `"all"` or array of IDs)

**Example:**
```json
{
  "on_delete": {
    "detach": "all"
  }
}
```

## Special Pivot Data Values

The system processes special placeholder values in `pivot_data`:

| Value | Replaced With | Use Case |
|-------|--------------|----------|
| `"now"` | Current timestamp | `created_at`, `updated_at` |
| `"current_user"` | Authenticated user ID | `assigned_by`, `created_by` |
| `"current_date"` | Current date (Y-m-d) | `assignment_date` |

**Example:**
```json
{
  "pivot_data": {
    "created_at": "now",
    "assigned_by": "current_user",
    "assignment_date": "current_date"
  }
}
```

## New Form Fields

Added multiselect fields for relationship management. These fields populate their options by querying the related CRUD6 model.

### users.json
```json
{
  "role_ids": {
    "type": "multiselect",
    "label": "Roles",
    "description": "User roles (used for sync on update)",
    "lookup_model": "roles",
    "lookup_id": "id",
    "lookup_desc": "name",
    "required": false,
    "editable": true,
    "viewable": false,
    "listable": false
  }
}
```

**Data Source Configuration:**
- `lookup_model: "roles"` - Queries `/api/crud6/roles` endpoint
- `lookup_id: "id"` - Uses the `id` field as the value
- `lookup_desc: "name"` - Displays the `name` field in the multiselect options

### roles.json
```json
{
  "permission_ids": {
    "type": "multiselect",
    "label": "Permissions",
    "description": "Role permissions (used for sync on update)",
    "lookup_model": "permissions",
    "lookup_id": "id",
    "lookup_desc": "name",
    "required": false,
    "editable": true,
    "viewable": false,
    "listable": false
  }
}
```

**Data Source Configuration:**
- `lookup_model: "permissions"` - Queries `/api/crud6/permissions` endpoint
- Displays permission names in the multiselect dropdown

### permissions.json
```json
{
  "role_ids": {
    "type": "multiselect",
    "label": "Roles",
    "description": "Permission roles (used for sync on update)",
    "lookup_model": "roles",
    "lookup_id": "id",
    "lookup_desc": "name",
    "required": false,
    "editable": true,
    "viewable": false,
    "listable": false
  }
}
```

**Data Source Configuration:**
- `lookup_model: "roles"` - Queries `/api/crud6/roles` endpoint
- Displays role names in the multiselect dropdown

### How Multiselect Data Population Works

The multiselect fields use CRUD6's automatic lookup system:

1. **Initial Load:** When the form loads, it queries `/api/crud6/{lookup_model}` to get all available options
2. **Display:** Shows the `lookup_desc` field (e.g., "name") to the user
3. **Value:** Stores the `lookup_id` field (e.g., "id") as the selected value(s)
4. **Sync:** The selected IDs are used by the `on_update` sync action to update the pivot table

**Example API Call:**
```
GET /api/crud6/roles
Response: {
  "rows": [
    {"id": 1, "name": "Admin"},
    {"id": 2, "name": "User"},
    {"id": 3, "name": "Guest"}
  ]
}
```

The multiselect will display: "Admin", "User", "Guest" as options, and store [1, 2, 3] as values when selected.
```

## User Workflow Examples

### Creating a New User

1. Admin fills in user form (name, email, password)
2. Admin clicks "Create"
3. **Automatic:** User is created AND assigned role ID 2 (User role)
4. No manual role assignment needed!

### Updating User Roles

1. Admin opens user edit form
2. Admin selects roles in "Roles" multiselect field
3. Admin clicks "Save"
4. **Automatic:** `role_users` table is synced
   - New roles are added
   - Unselected roles are removed
   - Selected roles remain

### Deleting a User

1. Admin clicks "Delete" on user
2. **Automatic:** All entries in `role_users` are removed first
3. Then user record is deleted
4. No orphaned pivot table entries!

## Benefits

### Before (Manual Approach)

❌ Required custom controllers for each model  
❌ Code duplication across models  
❌ Manual pivot table management  
❌ Risk of orphaned records  
❌ Difficult to maintain  

### After (Schema-Based)

✅ No custom controllers needed  
✅ Configuration in schema files  
✅ Automatic pivot table management  
✅ Transaction-safe operations  
✅ Easy to maintain and modify  
✅ Consistent pattern across all models  

## Testing

### Manual Testing Checklist

**Toggle Switches:**
- [ ] User create form shows toggle switches for flag_verified and flag_enabled
- [ ] Toggle switches show "Enabled/Disabled" labels
- [ ] Clicking toggle switches changes state smoothly
- [ ] Toggle state saves correctly

**User Role Management:**
- [ ] Creating a new user automatically assigns role ID 2
- [ ] Check `role_users` table has entry after user creation
- [ ] Updating user roles syncs correctly in `role_users` table
- [ ] Deleting user removes all `role_users` entries

**Role Permission Management:**
- [ ] Updating role permissions syncs correctly in `permission_roles` table
- [ ] Deleting role removes all `permission_roles` entries

**Permission Role Management:**
- [ ] Updating permission roles syncs correctly in `permission_roles` table
- [ ] Deleting permission removes all `permission_roles` entries

### Validation

All JSON schemas have been validated:
```bash
✓ activities.json - Valid
✓ groups.json - Valid
✓ permissions.json - Valid
✓ roles.json - Valid
✓ users.json - Valid
```

## Backward Compatibility

✅ **100% Backward Compatible**

- Existing functionality unchanged
- Toggle switches work with existing boolean fields
- Relationship actions are optional (won't break existing code)
- All changes are additive

## Requirements

This feature requires:
- **sprinkle-crud6** with PR #165 merged
- UserFrosting 6 application context
- CRUD6 dependency installed

## Troubleshooting

### Toggle Switches Not Showing

**Check:**
1. Is sprinkle-crud6 updated with PR #165 changes?
2. Is field type exactly `"boolean-tgl"`?
3. Clear browser cache and reload

### Relationship Actions Not Working

**Check:**
1. Is sprinkle-crud6 updated with PR #165 changes?
2. Is `actions` property inside the relationship definition?
3. Is the multiselect field (`role_ids`, etc.) defined in fields?
4. Check application logs for errors

### User Not Getting Default Role

**Check:**
1. Does role ID 2 exist in your database?
2. Is `on_create` action properly configured in users.json?
3. Check `role_users` table after creating user
4. Review application debug logs

## References

- [CRUD6 PR #165](https://github.com/ssnukala/sprinkle-crud6/pull/165) - Original implementation
- [CRUD6 Documentation](https://github.com/ssnukala/sprinkle-crud6/blob/main/docs/RELATIONSHIP_ACTIONS.md) - Full relationship actions guide
- [CRUD6 Quick Reference](https://github.com/ssnukala/sprinkle-crud6/blob/main/docs/RELATIONSHIP_ACTIONS_QUICK_REF.md) - Quick reference guide

## Summary

These schema updates bring two powerful features to C6Admin:

1. **Modern Toggle Switches** for better UX on boolean fields
2. **Automatic Pivot Table Management** for cleaner, more maintainable code

All changes are backward compatible and require no custom controller code. Simply update the schemas and the functionality works automatically through CRUD6.
