# Router Configuration Update for Integration Test

## Changes Made

This update modifies the `.github/workflows/integration-test-modular.yml` workflow file to remove CRUD6 route configuration and properly configure C6Admin routes.

## Problem Statement

The integration test workflow was configuring both CRUD6 and C6Admin routes in the UserFrosting 6 application's `app/assets/router/index.ts` file. Based on the new architectural approach, CRUD6 routes should not be added to the main router - only C6Admin routes should be configured.

Additionally, the last integration test failed with a login screen issue, which was related to how the routes were being configured.

## Solution

### Removed CRUD6 Route Configuration

**Before:**
```bash
# Import both CRUD6Routes and createC6AdminRoutes
sed -i "..." import CRUD6Routes from '@ssnukala/sprinkle-crud6/routes'
sed -i "..." import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'

# Add CRUD6Routes after AccountRoutes
sed -i '/\.\.\.AccountRoutes,/a \            ...CRUD6Routes,' app/assets/router/index.ts

# Add C6Admin routes after CRUD6Routes
sed -i '/\.\.\.CRUD6Routes,/a \            ...createC6AdminRoutes({ layoutComponent: LayoutDashboard }),' app/assets/router/index.ts
```

**After:**
```bash
# Import only createC6AdminRoutes and LayoutDashboard
sed -i "..." import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'
sed -i "..." import LayoutDashboard from '@userfrosting/theme-pink-cupcake/layouts/LayoutDashboard.vue'

# Add C6Admin routes after the AdminRoutes section
awk '/title: .ADMIN_PANEL./ {found=1} found && /^        },/ && !done {print; print "        // C6Admin routes with their own layout"; print "        ...createC6AdminRoutes({ layoutComponent: LayoutDashboard }),"; done=1; next} {print}' app/assets/router/index.ts > app/assets/router/index.ts.tmp
mv app/assets/router/index.ts.tmp app/assets/router/index.ts
```

### Placement After AdminRoutes Section

The C6Admin routes are now added after the existing `/admin` route block that contains `title: 'ADMIN_PANEL'`, as specified in the problem statement:

```typescript
{
    path: '/admin',
    component: () => import('../layouts/LayoutDashboard.vue'),
    children: [...AdminRoutes],
    meta: {
        title: 'ADMIN_PANEL'
    }
},
// C6Admin routes with their own layout
...createC6AdminRoutes({ layoutComponent: LayoutDashboard }),
```

## Why These Changes

1. **CRUD6 Routes Not Needed in Router**: CRUD6 is still a dependency of C6Admin (at the PHP and Vue levels), but its routes are handled differently in the new architecture. They don't need to be explicitly added to the main router configuration.

2. **C6Admin Routes with Layout**: Using `createC6AdminRoutes({ layoutComponent: LayoutDashboard })` creates a complete parent route that:
   - Has the `/c6/admin` base path
   - Includes the LayoutDashboard component for proper layout rendering (including sidebar)
   - Has authentication meta `{ auth: {} }` for protected routes
   - Contains all C6Admin child routes

3. **Placement**: The C6Admin routes are placed after the standard AdminRoutes section to keep the router configuration organized.

4. **Awk Instead of Sed**: The awk command provides more reliable multi-line pattern matching compared to complex sed expressions. It:
   - Finds the line containing `title: 'ADMIN_PANEL'`
   - Continues until it finds the closing brace `},` after that line
   - Inserts the C6Admin routes after the closing brace
   - Only does this once using a `done` flag

## Impact

### What Changed
- ✅ Removed CRUD6Routes import from router/index.ts
- ✅ Removed CRUD6Routes spread from router array
- ✅ C6Admin routes now placed after AdminRoutes section
- ✅ More reliable multi-line editing with awk

### What Stayed the Same
- ✅ CRUD6 is still registered in MyApp.php (PHP backend)
- ✅ CRUD6Sprinkle is still registered in main.ts (Vue frontend)
- ✅ CRUD6 NPM package is still installed
- ✅ CRUD6 is still a dependency of C6Admin

### Expected Benefits
- ✅ Fixes the integration test login failure
- ✅ Cleaner router configuration (no CRUD6 routes in main router)
- ✅ C6Admin routes have proper layout and authentication
- ✅ Follows the new architectural approach

## Files Modified

- `.github/workflows/integration-test-modular.yml` - Lines 81-95 (router configuration step)

## Related

- PR #42: Fixed login failure and missing sidebar by correcting route component handling
- Issue: Integration test failing with login screen appearing
- Problem statement: Remove CRUD6 route additions and add C6Admin routes after AdminRoutes section
