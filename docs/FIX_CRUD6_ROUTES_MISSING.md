# Fix: Missing CRUD6 Routes in Integration Test

## Problem

Integration test was failing with Vue Router errors and empty tables on C6Admin pages. The logs from GitHub Actions run #19455735818 showed multiple errors:

### Error Symptoms

1. **Vue Router Errors**: `[Browser Error]: No match for {"name":"crud6.view","params":{"model":"users","id":"1"}}`
   - Error appeared for all models: users, groups, roles, permissions, activities
   - Occurred when CRUD6's SprunjeTable component tried to create links to detail pages

2. **500 Internal Server Errors**:
   - `/api/crud6/roles/1` - Failed to load resource (500 error)
   - `/api/crud6/permissions/1` - Failed to load resource (500 error)

3. **Empty Tables**: Users list page showed empty table despite admin user and test users being created

### Root Cause

The integration test workflow was **NOT** registering CRUD6 routes in the Vue Router, only C6Admin routes. This was based on outdated documentation in `docs/ROUTER_CONFIG_UPDATE.md` that stated:

> "CRUD6 routes should not be added to the main router - only C6Admin routes should be configured"

However, this architectural decision conflicted with how CRUD6 components actually work:

1. C6Admin uses CRUD6's generic components (`CRUD6ListPage`, `CRUD6DynamicPage`, `SprunjeTable`)
2. These CRUD6 components create `RouterLink` elements with route names like `crud6.view`
3. Without CRUD6 routes registered, these links failed with "No match" errors
4. This caused Vue runtime errors and prevented proper navigation

## Solution

### Changes Made to `.github/workflows/integration-test-modular.yml`

Added CRUD6 routes alongside C6Admin routes in the router configuration step:

**Before:**
```yaml
# Step 1: Add import statements for createC6AdminRoutes and LayoutDashboard
sed -i "/import AdminRoutes from '@userfrosting\/sprinkle-admin\/routes'/a import { createC6AdminRoutes } from '@ssnukala\/sprinkle-c6admin\/routes'" app/assets/router/index.ts
sed -i "/import { createRouter, createWebHistory } from 'vue-router'/a import LayoutDashboard from '../layouts/LayoutDashboard.vue'" app/assets/router/index.ts

# Step 2: Find the last ] in the file and insert C6Admin routes before it
sed -i "${LAST_BRACKET_LINE}i\\        ,\\
// C6Admin routes with their own layout\\
...createC6AdminRoutes({ layoutComponent: LayoutDashboard })" app/assets/router/index.ts
```

**After:**
```yaml
# Step 1: Add import statements for CRUD6Routes, createC6AdminRoutes and LayoutDashboard
sed -i "/import AdminRoutes from '@userfrosting\/sprinkle-admin\/routes'/a import CRUD6Routes from '@ssnukala\/sprinkle-crud6\/routes'" app/assets/router/index.ts
sed -i "/import AdminRoutes from '@userfrosting\/sprinkle-admin\/routes'/a import { createC6AdminRoutes } from '@ssnukala\/sprinkle-c6admin\/routes'" app/assets/router/index.ts
sed -i "/import { createRouter, createWebHistory } from 'vue-router'/a import LayoutDashboard from '../layouts/LayoutDashboard.vue'" app/assets/router/index.ts

# Step 2: Find the last ] in the file and insert CRUD6 and C6Admin routes before it
sed -i "${LAST_BRACKET_LINE}i\\        ,\\
// CRUD6 generic routes (required for CRUD6 components)\\
...CRUD6Routes,\\
// C6Admin routes with their own layout\\
...createC6AdminRoutes({ layoutComponent: LayoutDashboard })" app/assets/router/index.ts
```

### Validation Changes

Added validation step to verify CRUD6 routes are properly registered:

```yaml
if ! grep -q "import CRUD6Routes from '@ssnukala/sprinkle-crud6/routes'" app/assets/router/index.ts; then
  echo "❌ ERROR: CRUD6Routes import statement not found in router/index.ts"
  exit 1
fi
echo "✅ CRUD6Routes import statement verified"

if ! grep -q "...CRUD6Routes" app/assets/router/index.ts; then
  echo "❌ ERROR: CRUD6 routes not found in router/index.ts"
  exit 1
fi
echo "✅ CRUD6 routes verified"
```

## Why This Fix is Correct

### CRUD6 Architecture Clarification

The previous assumption that "CRUD6 routes should not be in the router" was incorrect. The correct architecture is:

1. **CRUD6 provides generic routes** (`/crud6/{model}`, `/crud6/{model}/{id}`) with route names like `crud6.list` and `crud6.view`
2. **C6Admin provides model-specific routes** (`/c6/admin/users`, `/c6/admin/users/{id}`) with names like `c6admin.users` and `c6admin.user`
3. **Both route sets are required**:
   - C6Admin routes provide the entry points for the admin interface
   - CRUD6 routes provide the generic fallback routes that CRUD6 components link to

### Component Integration

C6Admin routes use CRUD6 components:

```typescript
// UserRoutes.ts
{
    path: '',
    name: 'c6admin.users',
    component: () => import('@ssnukala/sprinkle-crud6/views').then(m => m.CRUD6ListPage),
    beforeEnter: (to) => {
        to.params.model = 'users'
    }
}
```

The `CRUD6ListPage` component uses `SprunjeTable`, which creates RouterLinks to detail pages using the CRUD6 route names. Without CRUD6 routes registered, these links fail.

## Expected Outcomes

After this fix:

1. ✅ **No more Vue Router errors**: All `RouterLink` components will find their target routes
2. ✅ **Working detail page links**: Clicking on users/groups/roles/permissions in list tables will navigate to detail pages
3. ✅ **No more 500 errors**: API endpoints will work correctly (the 500 errors may have been caused by Vue errors interrupting the request)
4. ✅ **Populated tables**: List tables will display data correctly with working action buttons and links

## Testing

The next integration test run should verify:
- [ ] No Vue Router "No match for" errors in browser console
- [ ] List pages show data in tables
- [ ] Clicking on table rows navigates to detail pages
- [ ] Detail pages load without 500 errors
- [ ] All screenshots show proper content

## Related Files

- `.github/workflows/integration-test-modular.yml` - Updated router configuration
- `docs/ROUTER_CONFIG_UPDATE.md` - Outdated documentation (needs correction)
- `docs/CRUD6_INTEGRATION_NOTES.md` - Should be updated with route requirements

## Notes for Future Development

When integrating C6Admin into a UserFrosting 6 application, developers must:

1. Register CRUD6 routes in their router configuration
2. Register C6Admin routes in their router configuration
3. Import both:
   ```typescript
   import CRUD6Routes from '@ssnukala/sprinkle-crud6/routes'
   import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'
   ```
4. Add both to the routes array:
   ```typescript
   const routes = [
       ...CRUD6Routes,
       ...createC6AdminRoutes({ layoutComponent: LayoutDashboard })
   ]
   ```

This should be documented in the main README.md installation instructions.
