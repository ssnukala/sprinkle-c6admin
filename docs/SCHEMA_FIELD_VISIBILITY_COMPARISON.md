# Schema Field Visibility Comparison

This document shows the before/after comparison of field visibility in C6Admin schemas to match sprinkle-admin.

## Users Table

### Before (Incorrect - Too Many Fields)
Fields shown in table:
- ✅ id
- ✅ user_name
- ✅ first_name
- ✅ last_name
- ✅ email
- ✅ locale
- ✅ group_id
- ✅ flag_verified
- ✅ flag_enabled
- ❌ password (already hidden)
- ❌ deleted_at (already hidden)
- ✅ created_at
- ❌ updated_at (already hidden)

### After (Correct - Matches sprinkle-admin)
Fields shown in table:
- ❌ id (hidden)
- ✅ user_name (shown in combined name column)
- ✅ first_name (shown in combined name column)
- ✅ last_name (shown in combined name column)
- ✅ email (shown below name)
- ❌ locale (hidden)
- ❌ group_id (hidden)
- ✅ flag_verified (shown in combined status column)
- ✅ flag_enabled (shown in combined status column)
- ❌ password (hidden - security)
- ❌ deleted_at (hidden)
- ❌ created_at (hidden)
- ❌ updated_at (hidden)

**Sprinkle-Admin Columns**: Name (user_name + first_name + last_name), Email, Last Activity, Status (flag_verified + flag_enabled), Actions

---

## Groups Table

### Before (Incorrect - Too Many Fields)
Fields shown in table:
- ✅ id
- ✅ slug
- ✅ name
- ✅ description
- ✅ icon
- ✅ created_at
- ❌ updated_at (already hidden)

### After (Correct - Matches sprinkle-admin)
Fields shown in table:
- ❌ id (hidden)
- ❌ slug (hidden - used in links)
- ✅ name (shown with icon inline)
- ✅ description
- ❌ icon (hidden - shown inline with name)
- ❌ created_at (hidden)
- ❌ updated_at (hidden)

**Sprinkle-Admin Columns**: Group (name with icon), Description, Actions

---

## Roles Table

### Before (Incorrect - Too Many Fields)
Fields shown in table:
- ✅ id
- ✅ slug
- ✅ name
- ✅ description
- ✅ created_at
- ❌ updated_at (already hidden)

### After (Correct - Matches sprinkle-admin)
Fields shown in table:
- ❌ id (hidden)
- ❌ slug (hidden - used in links)
- ✅ name
- ✅ description
- ❌ created_at (hidden)
- ❌ updated_at (hidden)

**Sprinkle-Admin Columns**: Role (name), Description, Actions

---

## Permissions Table

### Before (Incorrect - Too Many Fields)
Fields shown in table:
- ✅ id
- ✅ slug
- ✅ name
- ✅ conditions
- ✅ description
- ✅ created_at
- ❌ updated_at (already hidden)

### After (Correct - Matches sprinkle-admin)
Fields shown in table:
- ❌ id (hidden)
- ✅ slug (shown in combined properties column)
- ✅ name
- ✅ conditions (shown in combined properties column)
- ✅ description (shown in combined properties column)
- ❌ created_at (hidden)
- ❌ updated_at (hidden)

**Sprinkle-Admin Columns**: Permission (name), Properties (slug + conditions + description), Via Roles (optional)

---

## Activities Table

### Before (Incorrect - Too Many Fields)
Fields shown in table:
- ✅ id
- ✅ ip_address
- ✅ user_id
- ✅ type
- ✅ occurred_at
- ✅ description

### After (Correct - Matches sprinkle-admin)
Fields shown in table:
- ❌ id (hidden)
- ❌ ip_address (hidden - shown with description)
- ❌ user_id (hidden - user object shown instead)
- ❌ type (hidden)
- ✅ occurred_at
- ✅ description (shown with ip_address)

**Sprinkle-Admin Columns**: Time (occurred_at), User (optional), Description (description + ip_address)

---

## Summary of Changes

### Total Fields Changed
- **Users**: 4 fields hidden (id, locale, group_id, created_at)
- **Groups**: 4 fields hidden (id, slug, icon, created_at)
- **Roles**: 3 fields hidden (id, slug, created_at)
- **Permissions**: 2 fields hidden (id, created_at)
- **Activities**: 4 fields hidden (id, ip_address, user_id, type)

### Benefits
1. **Security**: Prevents display of sensitive fields (password already hidden, but now consistent)
2. **UI Consistency**: Matches the official UserFrosting sprinkle-admin interface
3. **Cleaner Tables**: Reduces clutter by hiding technical fields (id, slug) and audit fields (created_at)
4. **Better UX**: Shows only fields users need to see at a glance

### How CRUD6 Handles This
The `listable` attribute in schema files controls which fields appear in list views:
- `"listable": true` - Field appears as a column in the table
- `"listable": false` - Field is hidden from the table (but still available in detail/edit views)

CRUD6's dynamic templates respect this attribute and automatically show/hide columns based on the schema configuration.
