# Schema Updates: Toggle Buttons and Pivot Table Management

**Date:** 2025-11-12  
**Related PR:** Based on [sprinkle-crud6 PR #165](https://github.com/ssnukala/sprinkle-crud6/pull/165)

## Overview

This update implements two key features from CRUD6 in the C6Admin schema files:

1. **Boolean Toggle Switches** - Modern UI for boolean fields
2. **Automatic Pivot Table Management** - Schema-driven relationship actions

## Changes Made

### 1. Boolean Toggle Switches

#### Updated Fields

**users.json:**
- `flag_verified`: Changed from `boolean` to `boolean-tgl`
- `flag_enabled`: Changed from `boolean` to `boolean-tgl`

#### Visual Changes

**Before (Checkbox):**
```
☐ Verified
☐ Enabled
```

**After (Toggle Switch):**
```
[████████○] Enabled
[○────────] Disabled
```

#### Benefits
- Modern, intuitive UI
- Clear "Enabled/Disabled" labels
- Better visual feedback
- Improved accessibility

### 2. Pivot Table Management

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

Added multiselect fields for relationship management:

### users.json
```json
{
  "role_ids": {
    "type": "multiselect",
    "label": "Roles",
    "description": "User roles (used for sync on update)",
    "required": false,
    "editable": true,
    "viewable": false,
    "listable": false
  }
}
```

### roles.json
```json
{
  "permission_ids": {
    "type": "multiselect",
    "label": "Permissions",
    "description": "Role permissions (used for sync on update)",
    "required": false,
    "editable": true,
    "viewable": false,
    "listable": false
  }
}
```

### permissions.json
```json
{
  "role_ids": {
    "type": "multiselect",
    "label": "Roles",
    "description": "Permission roles (used for sync on update)",
    "required": false,
    "editable": true,
    "viewable": false,
    "listable": false
  }
}
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
