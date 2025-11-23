# Schema Updates Based on CRUD6 Enhancements

## Overview
This document outlines the updates made to c6admin schema files to align with the latest CRUD6 enhancements and conventions (November 2024).

## CRUD6 Recent Enhancements

### 1. Auto-Inference System (Phase 2 - November 20, 2024)
CRUD6 now includes an intelligent auto-inference system that minimizes schema verbosity by automatically inferring action properties:

- **Field Inference**: Actions with keys ending in `_action` (e.g., `password_action`) automatically infer the field name
- **Icon Inference**: Icons are automatically assigned based on action patterns and field types
- **Label Inference**: Labels are auto-generated from translation keys or field names
- **Style Inference**: Button styles are automatically determined from action patterns

**Key Files Added:**
- `app/assets/utils/actionInference.ts` - Core inference logic
- `.archive/SCHEMA_UPDATES_NEW_CONVENTIONS.md` - Migration guide

### 2. Convention Updates

#### Action Key Naming: `{fieldname}_action` Pattern
- **Pattern**: Use `{fieldname}_action` for field update actions
- **Example**: `password_action` instead of `change_password`
- **Benefit**: Auto-inference of field, icon, label, and style

#### Auto-Inferred Endpoints for API Calls
- **Old**: `/api/crud6/{model}/{id}/actions/{actionKey}`
- **New**: `/api/crud6/{model}/{id}/a/{actionKey}`
- **Benefit**: Shorter URLs, auto-generated if not specified

#### Virtual/Computed Fields
- **Requirement**: Mark non-database fields with `"computed": true`
- **Examples**: `role_ids`, `permission_ids` for many-to-many relationships

#### Password Field Validation
- **Required**: Add `"validation": { "match": true }` for confirmation
- **Length**: Set both `min` and `max` (e.g., `min: 8, max: 255`)

## Updates Made to C6Admin Schemas

### users.json

#### 1. Added Missing Fields
```json
"theme": {
    "type": "string",
    "label": "Theme",
    "required": false,
    "show_in": ["form", "detail"],
    "validation": {
        "length": {
            "max": 100
        }
    }
}
```

```json
"last_activity_id": {
    "type": "integer",
    "label": "Last Activity ID",
    "required": false,
    "show_in": [],
    "readonly": true
}
```

#### 2. Enhanced Password Validation
```json
"password": {
    "validation": {
        "length": {
            "min": 8,
            "max": 255  // Added
        },
        "match": true
    }
}
```

#### 3. Action Labels (Previously Added)
All actions already have explicit `label` properties for i18n support:
- `toggle_enabled` → `USER.ADMIN.TOGGLE_ENABLED`
- `toggle_verified` → `USER.ADMIN.TOGGLE_VERIFIED`
- `reset_password` → `USER.ADMIN.PASSWORD_RESET`
- `password_action` → `USER.ADMIN.CHANGE_PASSWORD`
- `disable_user` → `USER.DISABLE`
- `enable_user` → `USER.ENABLE`

**Note**: While CRUD6 examples removed explicit labels to demonstrate auto-inference, we're keeping them in c6admin for clarity and explicit i18n control.

### roles.json
✅ Already has `"computed": true` on `permission_ids` field

### permissions.json
✅ Already has `"computed": true` on `role_ids` field

### groups.json
✅ No multiselect fields requiring `computed` flag

### activities.json
✅ No multiselect fields requiring `computed` flag

## C6Admin vs CRUD6 Examples Comparison

| Feature | CRUD6 Example | C6Admin | Recommendation |
|---------|---------------|---------|----------------|
| Action Labels | Removed (auto-inferred) | Explicit | ✅ Keep explicit for clarity |
| Action Icons | Removed (auto-inferred) | Not added | ✅ Can be auto-inferred |
| Action Styles | Removed (auto-inferred) | Not added | ✅ Can be auto-inferred |
| Field Visibility | `editable/viewable/listable` | `show_in` array | ✅ Keep `show_in` (more flexible) |
| Computed Fields | ✓ | ✓ | ✅ Already implemented |
| Password Validation | `min: 8, max: 255, match: true` | `min: 8, max: 255, match: true` | ✅ Now matches |
| Theme Field | ✓ | ✓ | ✅ Now added |
| Last Activity ID | ✓ | ✓ | ✅ Now added |
| Relationship Actions | Not shown | ✓ | ✅ C6Admin enhancement |

## Key Architectural Decisions

### 1. Explicit vs Auto-Inferred Labels
**Decision**: Keep explicit labels in c6admin schemas

**Rationale**:
- Better IDE autocomplete and type safety
- Clearer documentation of translation keys
- No performance difference (inference happens at runtime anyway)
- Easier debugging when translation keys are missing

**CRUD6 Approach**: Demonstrates auto-inference capability by removing labels from examples

**C6Admin Approach**: Leverages auto-inference as a fallback but maintains explicit labels for production use

### 2. Field Visibility Pattern
**Decision**: Continue using `show_in` array instead of boolean flags

**Rationale**:
- More flexible - supports multiple contexts (`list`, `form`, `detail`, `create`, `edit`)
- Aligns with modern UserFrosting 6 patterns
- Better granularity for field visibility control

### 3. Relationship Actions
**Decision**: Maintain relationship actions in c6admin (not in CRUD6 examples)

**Rationale**:
- Automatic cascade operations essential for data integrity
- Sync operations handle form submissions properly
- Examples in CRUD6 repo focus on basic schema, c6admin is production-ready

## Auto-Inference System Benefits

The CRUD6 auto-inference system provides:

1. **Less Verbose Schemas**: Reduce JSON by ~30% by omitting inferable properties
2. **Convention over Configuration**: Follow patterns, get automatic behavior
3. **Flexibility**: Can still override with explicit values when needed
4. **Maintainability**: Changes to conventions apply automatically

### Example: Before and After

**Before (Verbose)**:
```json
{
    "key": "password_action",
    "type": "field_update",
    "field": "password",
    "label": "USER.ADMIN.CHANGE_PASSWORD",
    "icon": "key",
    "style": "warning",
    "permission": "update_user_field"
}
```

**After (with Auto-Inference)**:
```json
{
    "key": "password_action",
    "type": "field_update",
    "permission": "update_user_field"
    // field, icon, label, style all auto-inferred
}
```

**C6Admin Approach (Hybrid)**:
```json
{
    "key": "password_action",
    "type": "field_update",
    "label": "USER.ADMIN.CHANGE_PASSWORD",  // Explicit for clarity
    "permission": "update_user_field"
    // field, icon, style can be auto-inferred
}
```

## Testing Recommendations

After these schema updates:

1. ✅ Validate JSON syntax
2. ✅ Test theme field appears in user forms
3. ✅ Test password confirmation modal works
4. ✅ Test password validation (min 8, max 255)
5. ✅ Test action buttons display with proper labels
6. ✅ Test auto-inferred properties work as expected

## Summary of Changes

### Files Modified
- `app/schema/crud6/users.json`
  - Added `theme` field
  - Added `last_activity_id` field
  - Updated password validation max length to 255
  - Already has action labels (from previous fix)
  - Already has `computed: true` on role_ids
  - Already has `match: true` on password validation

### Files Already Compliant
- `app/schema/crud6/roles.json` - Has `computed: true` on permission_ids
- `app/schema/crud6/permissions.json` - Has `computed: true` on role_ids
- `app/schema/crud6/groups.json` - No changes needed
- `app/schema/crud6/activities.json` - No changes needed

## References

- CRUD6 Action Inference: `app/assets/utils/actionInference.ts`
- CRUD6 Schema Updates: `.archive/SCHEMA_UPDATES_NEW_CONVENTIONS.md`
- CRUD6 Examples: `examples/schema/c6admin-*.json`
- November 20, 2024 commits: 454d2b2, 8cdd657


## Schema Files Compared

| Schema | C6Admin Path | CRUD6 Example Path |
|--------|-------------|-------------------|
| Users | `app/schema/crud6/users.json` | `examples/schema/c6admin-users.json` |
| Activities | `app/schema/crud6/activities.json` | `examples/schema/c6admin-activities.json` |
| Groups | `app/schema/crud6/groups.json` | `examples/schema/c6admin-groups.json` |
| Roles | `app/schema/crud6/roles.json` | `examples/schema/c6admin-roles.json` |
| Permissions | `app/schema/crud6/permissions.json` | `examples/schema/c6admin-permissions.json` |

## Key Architectural Differences

### Field Visibility Approach

**CRUD6 Examples:**
Uses individual boolean flags for field visibility:
```json
{
    "editable": false,
    "viewable": true,
    "listable": false
}
```

**C6Admin (Current):**
Uses `show_in` array for more flexible context-based visibility:
```json
{
    "show_in": ["list", "form", "detail"]
}
```

**Rationale:** The `show_in` array approach is more flexible and aligns with modern CRUD6 patterns, allowing finer control over field visibility in different contexts (list view, create form, edit form, detail view).

## Schema-by-Schema Analysis

### 1. Users Schema

#### Fields Comparison

| Field | CRUD6 Example | C6Admin | Status |
|-------|---------------|---------|--------|
| id | ✓ | ✓ | Match |
| user_name | ✓ | ✓ | Match |
| first_name | ✓ | ✓ | Match |
| last_name | ✓ | ✓ | Match |
| email | ✓ | ✓ | Match (c6admin has type: "email") |
| locale | ✓ | ✓ | Match |
| **theme** | ✓ | **✓ (Added)** | **Updated** |
| group_id | ✓ | ✓ | Match |
| **last_activity_id** | ✓ | **✓ (Added)** | **Updated** |
| flag_verified | ✓ | ✓ | Match |
| flag_enabled | ✓ | ✓ | Match |
| **role_ids** | ✗ | ✓ | **C6Admin Enhancement** |
| password | ✓ | ✓ | Match |
| deleted_at | ✓ | ✓ | Match |
| created_at | ✓ | ✓ | Match |
| updated_at | ✓ | ✓ | Match |

#### Actions Comparison

| Action | CRUD6 Example | C6Admin | Status |
|--------|---------------|---------|--------|
| toggle_enabled | ✓ (no label) | ✓ (with label) | **Fixed** |
| toggle_verified | ✓ (no label) | ✓ (with label) | **Fixed** |
| reset_password | ✓ (no label) | ✓ (with label) | **Fixed** |
| password_action | ✓ (no label) | ✓ (with label) | **Fixed** |
| disable_user | ✓ (no label) | ✓ (with label) | **Fixed** |
| enable_user | ✓ (no label) | ✓ (with label) | **Fixed** |

**Note:** CRUD6 examples had the same missing action labels issue that we fixed in c6admin.

#### C6Admin Enhancements
- **Relationship Actions**: Users schema includes relationship actions for roles (attach on create, sync on update, detach on delete)
- **Multiselect Field**: `role_ids` field for managing user roles in forms
- **Enhanced Validation**: More detailed validation rules including `no_leading_whitespace`, `no_trailing_whitespace`
- **UI Type**: Boolean fields use `"ui": "toggle"` for better UX

### 2. Activities Schema

#### Differences
- **Field Visibility**: Only difference is the visibility approach (editable/viewable/listable vs show_in)
- **Structure**: Otherwise identical

### 3. Groups Schema

#### C6Admin Enhancements
- **Enhanced Validation**: 
  - `no_leading_whitespace` and `no_trailing_whitespace` for name field
  - `no_whitespace` for slug field
- **Field Visibility**: Uses `show_in` array approach

### 4. Roles Schema

#### C6Admin Enhancements
- **Relationship Actions**: 
  - Permissions: sync on update, detach on delete
  - Users: detach on delete
- **Multiselect Field**: `permission_ids` for managing role permissions
- **Enhanced Validation**: Similar to groups schema
- **Field Visibility**: Uses `show_in` array approach

### 5. Permissions Schema

#### C6Admin Enhancements
- **Relationship Actions**:
  - Roles: sync on update, detach on delete
- **Multiselect Field**: `role_ids` for managing permission roles
- **Enhanced Validation**: Similar to groups and roles
- **Field Visibility**: Uses `show_in` array approach

## Updates Made to C6Admin Schemas

### Users Schema (`app/schema/crud6/users.json`)

#### 1. Added Missing Fields
```json
"theme": {
    "type": "string",
    "label": "Theme",
    "required": false,
    "show_in": ["form", "detail"],
    "validation": {
        "length": {
            "max": 100
        }
    }
}
```

```json
"last_activity_id": {
    "type": "integer",
    "label": "Last Activity ID",
    "required": false,
    "show_in": [],
    "readonly": true
}
```

#### 2. Added Action Labels (Previously Fixed)
All 6 actions now have proper `label` properties with translation keys:
- `toggle_enabled` → `USER.ADMIN.TOGGLE_ENABLED`
- `toggle_verified` → `USER.ADMIN.TOGGLE_VERIFIED`
- `reset_password` → `USER.ADMIN.PASSWORD_RESET`
- `password_action` → `USER.ADMIN.CHANGE_PASSWORD`
- `disable_user` → `USER.DISABLE`
- `enable_user` → `USER.ENABLE`

## Summary of C6Admin Advantages

The c6admin schemas are **more feature-complete** than the CRUD6 examples:

1. **Relationship Actions**: Automatic cascade operations (attach, sync, detach)
2. **Multiselect Fields**: Better UX for managing relationships
3. **Enhanced Validation**: More comprehensive validation rules
4. **Modern Visibility Pattern**: `show_in` array is more flexible than boolean flags
5. **UI Enhancements**: Toggle UI for boolean fields
6. **Action Labels**: Properly labeled actions for i18n support

## Recommendations

### For C6Admin
- ✅ **Keep** the current enhanced implementation
- ✅ **Add** missing fields (`theme`, `last_activity_id`) - **DONE**
- ✅ **Keep** action labels - **DONE**
- ✅ **Maintain** relationship actions and multiselect fields

### For CRUD6 Repository
The CRUD6 example schemas should be updated to include:
1. Action labels for all user actions
2. Consider migrating to `show_in` pattern for field visibility
3. Add relationship actions examples
4. Add multiselect field examples

## Testing Recommendations

After these schema updates, test:
1. User detail page displays correctly with theme field
2. User forms show theme input field
3. All action buttons display with proper labels
4. Relationship management (roles, permissions) works correctly
5. Field visibility follows the `show_in` configuration

## References

- CRUD6 Repository: https://github.com/ssnukala/sprinkle-crud6
- CRUD6 Examples: https://github.com/ssnukala/sprinkle-crud6/tree/main/examples/schema
- UserFrosting 6 Documentation: https://learn.userfrosting.com/
