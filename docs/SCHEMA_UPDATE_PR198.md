# Schema Updates Based on CRUD6 PR #198

## Overview

This document summarizes the updates made to C6Admin JSON schema files to align with the major schema convention changes introduced in [sprinkle-crud6 PR #198](https://github.com/ssnukala/sprinkle-crud6/pull/198).

**Date:** 2025-11-20  
**Related PR:** [ssnukala/sprinkle-crud6#198](https://github.com/ssnukala/sprinkle-crud6/pull/198)

## Summary of Changes

The CRUD6 PR #198 introduced several important schema conventions and optimizations:

1. **Virtual Fields Handling**: Marks fields like `role_ids` and `permission_ids` as `computed: true` to prevent them from being inserted/updated in database operations
2. **Password Field Enhancement**: Adds `validation.match: true` to password fields to enable confirmation input in the UI
3. **Action Simplification**: Removes redundant properties from actions (label, icon, style, success_message) which are now auto-inferred
4. **New Password Action**: Adds `password_action` for direct password changes with confirmation modal
5. **Route Convention**: Uses `/a/` shortcode instead of `/actions/` for cleaner URLs (endpoint auto-generation)

## Files Updated

### 1. `app/schema/crud6/users.json` ✅

#### Actions Optimized
**Before (9 properties per action):**
```json
{
    "key": "toggle_enabled",
    "label": "USER.ADMIN.TOGGLE_ENABLED",
    "icon": "power-off",
    "type": "field_update",
    "field": "flag_enabled",
    "toggle": true,
    "style": "default",
    "permission": "update_user_field",
    "success_message": "USER.ADMIN.TOGGLE_ENABLED_SUCCESS"
}
```

**After (4-5 properties, 44-56% reduction):**
```json
{
    "key": "toggle_enabled",
    "type": "field_update",
    "field": "flag_enabled",
    "toggle": true,
    "permission": "update_user_field"
}
```

#### Actions Changed:
- `toggle_enabled` - Removed label, icon, style, success_message (auto-inferred)
- `toggle_verified` - Removed label, icon, style, success_message (auto-inferred)
- `reset_password` - Removed endpoint (auto-generated), label, icon, style, success_message
- **NEW** `password_action` - Added for direct password changes with confirmation
- `disable_user` - Removed label, icon, style, success_message (auto-inferred)
- `enable_user` - Removed label, icon, style, success_message (auto-inferred)

#### Fields Updated:
- `role_ids` - Added `"computed": true` to mark as virtual field (prevents DB insertion)
- `password` - Added `"match": true` to validation for confirmation input

**Changes:**
```json
"role_ids": {
    "type": "multiselect",
    "computed": true,  // ← ADDED
    ...
}

"password": {
    "type": "password",
    "validation": {
        "length": { "min": 8 },
        "match": true  // ← ADDED
    }
}
```

---

### 2. `app/schema/crud6/roles.json` ✅

#### Fields Updated:
- `permission_ids` - Added `"computed": true` to mark as virtual field

**Changes:**
```json
"permission_ids": {
    "type": "multiselect",
    "computed": true,  // ← ADDED
    ...
}
```

---

### 3. `app/schema/crud6/permissions.json` ✅

#### Fields Updated:
- `role_ids` - Added `"computed": true` to mark as virtual field

**Changes:**
```json
"role_ids": {
    "type": "multiselect",
    "computed": true,  // ← ADDED
    ...
}
```

---

### 4. `app/schema/crud6/groups.json` ✅

No changes required - no virtual fields or actions defined.

---

### 5. `app/schema/crud6/activities.json` ✅

No changes required - no virtual fields or actions defined.

---

## New Conventions from PR #198

### 1. Virtual Field Detection
Fields marked with `computed: true` are now excluded from INSERT/UPDATE database operations:
- `role_ids` in users and permissions schemas
- `permission_ids` in roles schema

These are UI-only fields used for managing many-to-many relationships via sync operations.

### 2. Password Confirmation
Fields with `validation.match: true` trigger confirmation input in the UI:
```json
"password": {
    "type": "password",
    "validation": {
        "match": true  // Shows confirmation field in FieldEditModal
    }
}
```

### 3. Action Property Auto-Inference
The following properties are now auto-inferred if not explicitly set:
- **field**: Inferred from action key pattern `{fieldname}_action`
- **icon**: Inferred from action type/field type (e.g., password → "key", toggle → "power-off")
- **label**: Inferred from translation key or field label
- **style**: Inferred from action pattern (e.g., delete → "danger", password → "warning")
- **success_message**: Auto-generated if not specified
- **endpoint**: Auto-generated for api_call actions as `/api/crud6/{model}/{id}/a/{actionKey}`

### 4. New Action Types
- **password_action**: Replaces deprecated `password_update` type
- Uses standard `field_update` type with `validation.match` flag

### 5. Route Conventions
- Custom action endpoints use `/a/` shortcode: `/api/crud6/{model}/{id}/a/{actionKey}`
- Endpoints are auto-generated if not specified

## Benefits Achieved

### ✅ Reduced Schema Verbosity
- **44-56% reduction** in properties per action
- Cleaner, more maintainable schema files
- Less typing, fewer decisions for developers

### ✅ Consistent UX
- Predictable icons and styles across all models
- Uniform confirmation patterns
- Better user experience with proper modals instead of native alerts

### ✅ Virtual Field Safety
- Prevents SQL errors from attempting to insert/update virtual fields
- Explicit marking with `computed: true` improves clarity

### ✅ Better Password Handling
- Generic FieldEditModal works for any field type with confirmation
- No need for password-specific modals
- Automatic confirmation field display when `validation.match: true`

### ✅ Backward Compatibility
- Existing explicit properties still work (override inferred values)
- No breaking changes to existing functionality
- Gradual adoption possible

## Validation Results

All schema files validated successfully:
```
✓ app/schema/crud6/users.json - Valid JSON
✓ app/schema/crud6/roles.json - Valid JSON  
✓ app/schema/crud6/permissions.json - Valid JSON
✓ app/schema/crud6/groups.json - Valid JSON
✓ app/schema/crud6/activities.json - Valid JSON
```

## Testing Recommendations

After these changes, test the following scenarios:

1. **User Creation with Roles**
   - Create a new user with roles selected
   - Verify no SQL error occurs (role_ids excluded from INSERT)
   - Verify roles are properly attached via relationship actions

2. **Password Actions**
   - Test "Reset Password" action (api_call)
   - Test "Password Action" (field_update with confirmation)
   - Verify FieldEditModal shows confirmation field
   - Verify password validation works

3. **Toggle Actions**
   - Test "Toggle Enabled" and "Toggle Verified"
   - Verify correct icons display (power-off, check-circle)
   - Verify toggle functionality works

4. **Permission and Role Management**
   - Update role permissions (permission_ids sync)
   - Update permission roles (role_ids sync)
   - Verify no SQL errors for virtual fields

## References

- **CRUD6 PR #198**: https://github.com/ssnukala/sprinkle-crud6/pull/198
- **CRUD6 Schema Conventions**: See `.archive/SCHEMA_UPDATES_NEW_CONVENTIONS.md` in CRUD6 repo
- **Phase 2 Optimizations**: See `.archive/PHASE_2_IMPLEMENTATION_SUMMARY.md` in CRUD6 repo
- **Action Inference**: See `app/assets/utils/actionInference.ts` in CRUD6 repo

## Migration Notes

### For Future Schema Updates

When creating or updating schemas:

1. **Virtual Fields**: Always mark multiselect relationship fields with `"computed": true`
2. **Password Fields**: Add `"match": true` to validation for confirmation
3. **Action Keys**: Use `{fieldname}_action` pattern for field update actions
4. **Action Properties**: Only specify properties that need custom values (icon, label, style, etc.)
5. **Endpoints**: Omit endpoint for standard api_call patterns (will auto-generate)

### Example Template

```json
{
    "actions": [
        {
            "key": "fieldname_action",
            "type": "field_update",
            "permission": "update_{model}_field"
            // field, icon, label, style auto-inferred
        }
    ],
    "fields": {
        "password": {
            "type": "password",
            "validation": {
                "match": true
            }
        },
        "relationship_ids": {
            "type": "multiselect",
            "computed": true
        }
    }
}
```

## Conclusion

The C6Admin schema files are now fully aligned with the CRUD6 PR #198 conventions, providing:
- ✅ Simplified action definitions with auto-inference
- ✅ Safe virtual field handling
- ✅ Enhanced password field functionality
- ✅ Consistent UX across all models
- ✅ Backward compatibility maintained
