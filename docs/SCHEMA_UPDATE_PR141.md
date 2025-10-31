# Schema Update for CRUD6 PR #141 Compliance

**Date**: October 31, 2024  
**Related PR**: [sprinkle-crud6 #141](https://github.com/ssnukala/sprinkle-crud6/pull/141)  
**Repository**: ssnukala/sprinkle-c6admin

---

## Summary

Updated all C6Admin CRUD6 schemas to comply with the new schema format introduced in [sprinkle-crud6 PR #141](https://github.com/ssnukala/sprinkle-crud6/pull/141). This PR introduced the `viewable` attribute for fine-grained field visibility control and deprecated the `readonly` attribute in favor of `editable: false`.

---

## Changes Made

### 1. Replaced `readonly: true` with `editable: false`

The `readonly` attribute is now deprecated. Fields should use `editable: false` instead.

**Before:**
```json
"created_at": {
    "type": "datetime",
    "label": "Created At",
    "readonly": true,
    "listable": false
}
```

**After:**
```json
"created_at": {
    "type": "datetime",
    "label": "Created At",
    "editable": false,
    "viewable": true,
    "listable": false
}
```

### 2. Added `viewable` Attribute

The new `viewable` attribute controls whether a field appears in detail/view pages. This provides complete visibility control alongside `listable` (for list/table views) and `editable` (for form editability).

**Field Visibility Matrix:**
- **listable** - Controls visibility in list/table views
- **viewable** - Controls visibility in detail/view pages (NEW)
- **editable** - Controls whether field can be edited in forms

---

## Schema-Specific Changes

### users.json (5 fields updated)

| Field | editable | viewable | Reason |
|-------|----------|----------|--------|
| `id` | false | true | Show ID in detail view but not editable |
| `password` | false | true | Visible but not editable (security) |
| `deleted_at` | false | false | Hide soft delete timestamp from detail view |
| `created_at` | false | true | Show timestamp but not editable |
| `updated_at` | false | true | Show timestamp but not editable |

### groups.json (3 fields updated)

| Field | editable | viewable | Reason |
|-------|----------|----------|--------|
| `id` | false | true | Show ID in detail view but not editable |
| `created_at` | false | true | Show timestamp but not editable |
| `updated_at` | false | true | Show timestamp but not editable |

### roles.json (3 fields updated)

| Field | editable | viewable | Reason |
|-------|----------|----------|--------|
| `id` | false | true | Show ID in detail view but not editable |
| `created_at` | false | true | Show timestamp but not editable |
| `updated_at` | false | true | Show timestamp but not editable |

### permissions.json (3 fields updated)

| Field | editable | viewable | Reason |
|-------|----------|----------|--------|
| `id` | false | true | Show ID in detail view but not editable |
| `created_at` | false | true | Show timestamp but not editable |
| `updated_at` | false | true | Show timestamp but not editable |

### activities.json (1 field updated)

| Field | editable | viewable | Reason |
|-------|----------|----------|--------|
| `id` | false | true | Show ID in detail view but not editable |

---

## Pattern Applied

### Standard Pattern for Common Fields

**ID Fields (auto_increment):**
```json
"id": {
    "type": "integer",
    "label": "ID",
    "auto_increment": true,
    "editable": false,
    "viewable": true,
    "listable": false
}
```

**Timestamp Fields (created_at, updated_at):**
```json
"created_at": {
    "type": "datetime",
    "label": "Created At",
    "editable": false,
    "viewable": true,
    "listable": false,
    "date_format": "Y-m-d H:i:s"
}
```

**Soft Delete Fields (deleted_at):**
```json
"deleted_at": {
    "type": "datetime",
    "label": "Deleted At",
    "editable": false,
    "viewable": false,
    "listable": false,
    "date_format": "Y-m-d H:i:s"
}
```

**Password Fields:**
```json
"password": {
    "type": "string",
    "label": "Password",
    "required": false,
    "listable": false,
    "viewable": true,
    "editable": false
}
```

---

## Validation

All JSON schemas were validated for syntax correctness:

```bash
✓ app/schema/crud6/activities.json - Valid JSON
✓ app/schema/crud6/groups.json - Valid JSON
✓ app/schema/crud6/permissions.json - Valid JSON
✓ app/schema/crud6/roles.json - Valid JSON
✓ app/schema/crud6/users.json - Valid JSON
```

---

## Benefits

1. **Fine-grained visibility control**: Separate control over list view, detail view, and editability
2. **Security**: Can hide sensitive fields (like `deleted_at`) from detail views while still tracking them
3. **Better UX**: Show readonly fields (like timestamps and IDs) in detail views without allowing editing
4. **Standards compliance**: Aligns with CRUD6 PR #141 schema format
5. **Future-proof**: Uses non-deprecated attributes (`editable: false` instead of `readonly: true`)

---

## Backward Compatibility

The changes are backward compatible with existing CRUD6 installations:

- The `viewable` attribute defaults to `true` if not specified
- The `editable: false` is checked before the deprecated `readonly: true` in UpdateFieldAction
- Existing functionality is preserved while using the new schema format

---

## Related Documentation

- [CRUD6 PR #141](https://github.com/ssnukala/sprinkle-crud6/pull/141) - Schema structure analysis and viewable attribute
- [CRUD6 README](https://github.com/ssnukala/sprinkle-crud6#field-properties) - Field properties documentation
- [UserFrosting 6 Schema Documentation](https://github.com/userfrosting/sprinkle-admin/tree/6.0/app/schema/requests) - Related schema patterns

---

## Testing Recommendations

To validate these schema changes:

1. **View detail pages** for each model (users, groups, roles, permissions, activities)
   - Verify ID fields are visible
   - Verify timestamp fields are visible
   - Verify `deleted_at` is NOT visible in users detail view
   - Verify password field is visible in users detail view

2. **Attempt to edit readonly fields**
   - Try editing ID fields (should fail)
   - Try editing timestamp fields (should fail)
   - Try editing password field (should fail)

3. **Check list views**
   - Verify `listable: false` fields don't appear in tables
   - Verify `listable: true` fields do appear in tables

4. **Test form editing**
   - Verify `editable: true` fields can be edited
   - Verify `editable: false` fields cannot be edited

---

## Files Changed

- `app/schema/crud6/users.json`
- `app/schema/crud6/groups.json`
- `app/schema/crud6/roles.json`
- `app/schema/crud6/permissions.json`
- `app/schema/crud6/activities.json`

**Total changes**: 15 fields updated across 5 schema files

---

## Commit

```
commit cd7f721
Author: Copilot
Date:   Fri Oct 31 2024

    Update CRUD6 schemas to comply with PR #141 format changes
    
    - Replace readonly: true with editable: false
    - Add viewable attribute for field visibility control
    - Update all 5 CRUD6 schemas (users, groups, roles, permissions, activities)
    - Validate all JSON schemas for correctness
```
