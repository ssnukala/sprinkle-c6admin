# Dashboard Left Panel Fix

## Issue
After PR #29, the left sidebar panel disappeared from the C6Admin dashboard (`/c6/admin/dashboard`) and all other admin pages.

## Root Cause
PR #29 introduced a new route configuration pattern using the `createC6AdminRoutes()` factory function. This function accepts a `layoutComponent` parameter that wraps the admin routes with a layout (like `LayoutDashboard`) which provides the left sidebar navigation.

The issue occurred because:
1. The integration test workflow was using the default export (`C6AdminRoutes`) instead of `createC6AdminRoutes()`
2. The default export calls `createC6AdminRoutes()` without any arguments
3. Without a `layoutComponent`, no layout wrapper is created
4. Without the layout wrapper, the sidebar navigation doesn't render

## Solution
Updated the integration test workflow to use the correct pattern:

**Before (broken):**
```typescript
import C6AdminRoutes from '@ssnukala/sprinkle-c6admin/routes'

const routes = [
  ...C6AdminRoutes  // No layout component - sidebar missing!
]
```

**After (fixed):**
```typescript
import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'
import LayoutDashboard from '@userfrosting/theme-pink-cupcake/layouts/LayoutDashboard.vue'

const routes = [
  ...createC6AdminRoutes({
    layoutComponent: LayoutDashboard  // Layout provides sidebar
  })
]
```

## Files Changed
1. `.github/workflows/integration-test.yml` - Updated to use `createC6AdminRoutes()` with `LayoutDashboard`
2. `README.md` - Added warning about default export not including layout component

## User Impact
Users who were using the default export pattern will need to update their route configuration to use one of the recommended patterns:

### Option 1: Using createC6AdminRoutes() (Recommended)
```typescript
import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'
import LayoutDashboard from '@userfrosting/theme-pink-cupcake/layouts/LayoutDashboard.vue'

const routes = [
  ...createC6AdminRoutes({
    layoutComponent: LayoutDashboard
  })
]
```

### Option 2: Using C6AdminChildRoutes
```typescript
import { C6AdminChildRoutes } from '@ssnukala/sprinkle-c6admin/routes'

const routes = [
  {
    path: '/c6/admin',
    component: () => import('../layouts/LayoutDashboard.vue'),
    children: C6AdminChildRoutes,
    meta: { title: 'C6ADMIN_PANEL' }
  }
]
```

## Testing
- ✅ All existing tests pass (12 tests)
- ✅ Integration test workflow updated
- ✅ Documentation updated with clear warnings

## Related
- PR #29: "Add route factory and comprehensive integration testing for C6Admin"
- Issue: "the c6/admin/dashboard used to show the left panel before this PR"
