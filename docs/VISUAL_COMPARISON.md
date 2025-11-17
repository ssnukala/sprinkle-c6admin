# Visual Comparison: CRUD6 vs C6Admin Integration Test Configuration

## Before Fix (C6Admin - Broken)

```
Integration Test Seed Configuration
═══════════════════════════════════

┌─────────────────────────────────┐
│   Account Sprinkle Seeds        │
│   • DefaultGroups               │
│   • DefaultPermissions          │
│   • DefaultRoles                │
│   • UpdatePermissions           │
└─────────────────────────────────┘
           ↓
┌─────────────────────────────────┐
│   C6Admin Sprinkle Seeds        │
│   • TestGroups                  │
│   • TestUsers                   │
└─────────────────────────────────┘
           ↓
┌─────────────────────────────────┐
│   Create Admin User             │
│   • Username: admin             │
│   • Role: site-admin            │
└─────────────────────────────────┘
           ↓
❌ PROBLEM: site-admin role has NO CRUD6 permissions!
           ↓
┌─────────────────────────────────┐
│   Login Test Result             │
│   ⚠️  Login succeeds BUT...     │
│   ⚠️  All pages redirect to     │
│      login (permission denied)  │
│   ❌ 0/12 screenshots captured  │
└─────────────────────────────────┘
```

## Reference: CRUD6 (Working)

```
Integration Test Seed Configuration
═══════════════════════════════════

┌─────────────────────────────────┐
│   Account Sprinkle Seeds        │
│   • DefaultGroups               │
│   • DefaultPermissions          │
│   • DefaultRoles                │
│   • UpdatePermissions           │
└─────────────────────────────────┘
           ↓
┌─────────────────────────────────┐
│   CRUD6 Sprinkle Seeds ✓        │
│   • DefaultRoles                │
│     - Creates crud6-admin role  │
│   • DefaultPermissions          │
│     - Creates 6 CRUD6 perms     │
│     - Assigns to site-admin ✓   │
└─────────────────────────────────┘
           ↓
┌─────────────────────────────────┐
│   Create Admin User             │
│   • Username: admin             │
│   • Role: site-admin            │
│   • Has CRUD6 permissions ✓     │
└─────────────────────────────────┘
           ↓
✅ site-admin role HAS all CRUD6 permissions!
           ↓
┌─────────────────────────────────┐
│   Login Test Result             │
│   ✅ Login succeeds             │
│   ✅ Navigation works           │
│   ✅ 2/2 screenshots captured   │
└─────────────────────────────────┘
```

## After Fix (C6Admin - Fixed)

```
Integration Test Seed Configuration
═══════════════════════════════════

┌─────────────────────────────────┐
│   Account Sprinkle Seeds        │
│   • DefaultGroups               │
│   • DefaultPermissions          │
│   • DefaultRoles                │
│   • UpdatePermissions           │
└─────────────────────────────────┘
           ↓
┌─────────────────────────────────┐
│   CRUD6 Sprinkle Seeds ✓        │  ← ADDED THIS!
│   • DefaultRoles                │
│     - Creates crud6-admin role  │
│   • DefaultPermissions          │
│     - Creates 6 CRUD6 perms     │
│     - Assigns to site-admin ✓   │
└─────────────────────────────────┘
           ↓
┌─────────────────────────────────┐
│   C6Admin Sprinkle Seeds        │
│   • TestGroups                  │
│   • TestUsers                   │
└─────────────────────────────────┘
           ↓
┌─────────────────────────────────┐
│   Create Admin User             │
│   • Username: admin             │
│   • Role: site-admin            │
│   • Has CRUD6 permissions ✓     │
└─────────────────────────────────┘
           ↓
✅ site-admin role HAS all CRUD6 permissions!
           ↓
┌─────────────────────────────────┐
│   Expected Login Test Result    │
│   ✅ Login succeeds             │
│   ✅ Navigation works           │
│   ✅ 12/12 screenshots captured │
└─────────────────────────────────┘
```

## Key Differences

| Aspect | Before (Broken) | After (Fixed) | CRUD6 (Reference) |
|--------|----------------|---------------|-------------------|
| CRUD6 Seeds | ❌ Missing | ✅ Included | ✅ Included |
| site-admin permissions | ❌ No CRUD6 perms | ✅ Has CRUD6 perms | ✅ Has CRUD6 perms |
| Login navigation | ❌ Redirects to login | ✅ Should work | ✅ Works |
| Screenshots captured | ❌ 0/12 | ✅ Expected 12/12 | ✅ 2/2 |
| Approach matches CRUD6 | ❌ No | ✅ Yes | N/A |

## The 6 CRUD6 Permissions

These permissions are now assigned to the `site-admin` role:

1. **`create_crud6`** - Permission to create records via CRUD6
2. **`delete_crud6`** - Permission to delete records via CRUD6
3. **`update_crud6_field`** - Permission to update record fields via CRUD6
4. **`uri_crud6`** - Permission to access CRUD6 routes
5. **`uri_crud6_list`** - Permission to list records via CRUD6
6. **`view_crud6_field`** - Permission to view record fields via CRUD6

Without these permissions, the admin user cannot access C6Admin pages because C6Admin uses CRUD6 routes for all operations.
