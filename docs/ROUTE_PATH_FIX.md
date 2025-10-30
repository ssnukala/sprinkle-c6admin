# Route Path Fix - Remove Duplicate c6/admin Prefix

## Issue
Routes were appearing as `c6/admin/c6/admin/groups` instead of `c6/admin/groups` when integrated into a parent application.

## Root Cause
The route files were defining paths with the full `c6/admin/` prefix (e.g., `path: 'c6/admin/groups'`), but when integrated into a parent application, they were being nested under another route group with the same `c6/admin` path, causing duplication:

```typescript
// In parent application router
{
  path: 'c6/admin',
  children: [...C6AdminRoutes]  // These already had c6/admin/ in their paths!
}
```

Result: `c6/admin/` + `c6/admin/groups` = `c6/admin/c6/admin/groups` ❌

## Solution
Changed all route files to use **relative paths** without the `c6/admin` prefix. The parent application is responsible for adding the prefix when nesting the routes.

### Changes Made

#### Route Files Updated
All route files now use relative paths:

1. **DashboardRoutes.ts**
   - Before: `path: 'c6/admin/dashboard'`
   - After: `path: 'dashboard'`

2. **GroupsRoutes.ts**
   - Before: `path: 'c6/admin/groups'`
   - After: `path: 'groups'`

3. **UserRoutes.ts**
   - Before: `path: 'c6/admin/users'`
   - After: `path: 'users'`

4. **RolesRoutes.ts**
   - Before: `path: 'c6/admin/roles'`
   - After: `path: 'roles'`

5. **PermissionsRoutes.ts**
   - Before: `path: 'c6/admin/permissions'`
   - After: `path: 'permissions'`

6. **ActivitiesRoutes.ts**
   - Before: `path: 'c6/admin/activities'`
   - After: `path: 'activities'`

7. **ConfigRoutes.ts**
   - Before: `path: 'c6/admin/config'`
   - After: `path: 'config'`

## Integration

### Correct Integration Pattern
When integrating C6Admin routes into a parent application, nest them under a `c6/admin` parent route:

```typescript
import C6AdminRoutes from '@ssnukala/sprinkle-c6admin/routes'

// In your router configuration
const routes = [
  // ... other routes
  {
    path: 'c6/admin',
    children: C6AdminRoutes
  }
]
```

This creates the correct route structure:
- `/c6/admin/dashboard` ✅
- `/c6/admin/groups` ✅
- `/c6/admin/users` ✅
- etc.

### Workflow Update
Updated `.github/workflows/integration-test.yml` to use the correct nesting pattern:

```bash
# Add C6AdminRoutes nested under c6/admin path
sed -i '/\.\.\.AdminRoutes,/a \            {\n                path: '\''c6\/admin'\'',\n                children: C6AdminRoutes\n            },' app/assets/router/index.ts
```

## Benefits
1. **No Path Duplication**: Routes appear correctly as `/c6/admin/*` not `/c6/admin/c6/admin/*`
2. **Standard Vue Router Pattern**: Uses the standard parent-child route pattern
3. **Cleaner Route Definitions**: Route files contain only relative paths
4. **Flexible Integration**: Parent application controls the base path
5. **Easier Testing**: Tests can verify relative paths work correctly

## Testing
The existing tests in `app/assets/tests/router/routes.test.ts` already expected relative paths:

```typescript
expect(routePaths).toContain('dashboard')  // NOT 'c6/admin/dashboard'
```

These tests now pass with the updated route definitions.

## Files Modified
- `app/assets/routes/DashboardRoutes.ts`
- `app/assets/routes/GroupsRoutes.ts`
- `app/assets/routes/UserRoutes.ts`
- `app/assets/routes/RolesRoutes.ts`
- `app/assets/routes/PermissionsRoutes.ts`
- `app/assets/routes/ActivitiesRoutes.ts`
- `app/assets/routes/ConfigRoutes.ts`
- `.github/workflows/integration-test.yml`
- `docs/ROUTE_PATH_FIX.md` (this file)
