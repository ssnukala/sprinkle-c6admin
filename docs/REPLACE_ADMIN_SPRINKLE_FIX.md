# Replace Admin Sprinkle with C6Admin in Integration Test

## Problem Summary

The integration test was succeeding, but screenshots showed the following error:

```
Cannot register two routes matching "/api/dashboard" for method Get
```

This error occurred because both `userfrosting/sprinkle-admin` (from the default UserFrosting installation) and `ssnukala/sprinkle-c6admin` were being registered together, causing route conflicts.

## Root Cause

The integration test workflow was **adding** C6Admin sprinkle alongside the default Admin sprinkle, rather than **replacing** it:

1. **MyApp.php**: Both `Admin::class` and `C6Admin::class` were in the sprinkles array
2. **main.ts**: Both `AdminSprinkle` and `C6AdminSprinkle` were being registered
3. **router/index.ts**: Both `AdminRoutes` and `C6AdminRoutes` were in the routes array

Since both sprinkles register the same routes (like `/api/dashboard`), this created conflicts.

## Solution

Modified the integration test to **replace** the default Admin sprinkle with C6Admin instead of adding it alongside:

### 1. Remove sprinkle-admin from Composer

Added step to remove the default admin package before installation:

```bash
composer remove userfrosting/sprinkle-admin --no-update
```

### 2. Remove sprinkle-admin from NPM

Added step to remove the default admin package from npm:

```bash
npm uninstall @userfrosting/sprinkle-admin || true
```

### 3. Replace in MyApp.php

**Before** (adding):
```bash
sed -i '/Admin::class,/a \            CRUD6::class,\n            C6Admin::class,' app/src/MyApp.php
```

**After** (replacing):
```bash
sed -i '/use UserFrosting\\Sprinkle\\Admin\\Admin;/d' app/src/MyApp.php
sed -i 's/Admin::class,/CRUD6::class,\n            C6Admin::class,/' app/src/MyApp.php
```

### 4. Replace in router/index.ts

**Before** (adding):
```bash
sed -i "/import AdminRoutes from '@userfrosting\/sprinkle-admin\/routes'/a import C6AdminRoutes from '@ssnukala\/sprinkle-c6admin\/routes'" app/assets/router/index.ts
sed -i '/\.\.\.AccountRoutes,/a \            ...C6AdminRoutes,' app/assets/router/index.ts
```

**After** (replacing):
```bash
sed -i "s|import AdminRoutes from '@userfrosting/sprinkle-admin/routes'|import AdminRoutes from '@ssnukala/sprinkle-c6admin/routes'|" app/assets/router/index.ts
```

Note: We keep the variable name as `AdminRoutes` to avoid changing the rest of the file.

### 5. Replace in main.ts

**Before** (adding):
```bash
sed -i "/import AdminSprinkle from '@userfrosting\/sprinkle-admin'/a import C6AdminSprinkle from '@ssnukala\/sprinkle-c6admin'" app/assets/main.ts
sed -i "/app.use(AdminSprinkle)/a app.use(C6AdminSprinkle)" app/assets/main.ts
```

**After** (replacing):
```bash
sed -i "s|import AdminSprinkle from '@userfrosting/sprinkle-admin'|import AdminSprinkle from '@ssnukala/sprinkle-c6admin'|" app/assets/main.ts
```

Note: We keep the variable name as `AdminSprinkle` to avoid changing the rest of the file.

## Changes Made

### File: `.github/workflows/integration-test.yml`

1. **Line 60-61**: Added composer remove command
2. **Line 78-79**: Added npm uninstall command  
3. **Lines 82-90**: Changed MyApp.php configuration from adding to replacing
4. **Lines 92-98**: Changed router/index.ts configuration from adding to replacing
5. **Lines 100-105**: Changed main.ts configuration from adding to replacing

## Key Improvements

1. **No route conflicts**: Only C6Admin routes are registered, eliminating the duplicate route error
2. **Clean installation**: Admin sprinkle is fully removed from both Composer and NPM
3. **Proper replacement**: C6Admin truly replaces Admin sprinkle rather than supplementing it
4. **Simpler code**: Using simple string replacement instead of complex sed append operations
5. **Better documentation**: Comments clearly state we're replacing, not adding

## Why This Approach

C6Admin is designed as a **drop-in replacement** for sprinkle-admin, not a supplement. It provides:

- All the same frontend pages and components
- All the same API endpoints (via CRUD6)
- All the same functionality

The integration test should reflect this by completely removing the original Admin sprinkle and replacing it with C6Admin.

## Testing

To verify the fix:

1. Run the integration test workflow
2. Check that screenshots no longer show route conflict errors
3. Verify all admin routes work correctly
4. Confirm no duplicate route registrations in logs

## Expected Behavior

After this fix:

- ✅ No route conflicts (`/api/dashboard` registered only once)
- ✅ C6Admin routes work correctly
- ✅ Frontend loads without errors
- ✅ Screenshots show working admin interface
- ✅ All integration tests pass

## Technical Context

### UserFrosting Installation Structure

When creating a UserFrosting 6 project with `composer create-project userfrosting/userfrosting`, it includes:

- `userfrosting/sprinkle-core` - Core framework
- `userfrosting/sprinkle-account` - User authentication and management
- `userfrosting/sprinkle-admin` - Admin interface (default)

### C6Admin as Replacement

C6Admin is specifically designed to replace `sprinkle-admin` by:

1. Providing the same UI/UX (Vue.js components)
2. Using CRUD6 for backend operations (instead of custom controllers)
3. Offering the same routes and functionality
4. Working with the same schemas (users, groups, roles, permissions, activities)

Since they provide identical functionality through different implementations, they cannot coexist without conflicts.

## Related Documentation

- README.md: Documents C6Admin as a replacement for sprinkle-admin
- Integration test workflow: Implements the actual replacement during CI testing
- Problem statement: Original issue description requesting this fix
