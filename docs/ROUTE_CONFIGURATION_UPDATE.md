# Route Configuration Update - Parent Route Integration

## Issue

Previously, users had to manually wrap C6Admin routes with a parent route configuration in their UserFrosting 6 application:

```typescript
// In UserFrosting 6 app/assets/routes/index.ts
import C6AdminRoutes from '@ssnukala/sprinkle-c6admin/routes'

const routes = [
  {
    path: '/c6/admin',
    component: () => import('../layouts/LayoutDashboard.vue'),
    children: [...C6AdminRoutes],
    meta: {
      title: 'C6ADMIN_PANEL'
    }
  }
]
```

This required users to:
1. Import the routes
2. Manually create the parent route object
3. Specify the layout component path
4. Set the meta title
5. Nest the C6Admin routes as children

## Solution

The sprinkle now provides a factory function `createC6AdminRoutes()` that handles all of this internally.

## New API

### 1. `createC6AdminRoutes(options)` - Factory Function (Recommended)

```typescript
import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'

const routes = [
  ...createC6AdminRoutes({
    layoutComponent: () => import('../layouts/LayoutDashboard.vue')
  })
]
```

**Options:**
- `layoutComponent`: The layout component to use (optional)
- `basePath`: Custom base path (default: `'/c6/admin'`)
- `title`: Custom meta title (default: `'C6ADMIN_PANEL'`)

**Examples:**

```typescript
// Simple usage
...createC6AdminRoutes({
  layoutComponent: () => import('../layouts/LayoutDashboard.vue')
})

// Custom path and title
...createC6AdminRoutes({
  layoutComponent: () => import('../layouts/LayoutDashboard.vue'),
  basePath: '/admin/c6',
  title: 'Admin Panel'
})

// Without layout component (component will be undefined)
...createC6AdminRoutes()
```

### 2. `C6AdminChildRoutes` - Child Routes Array

For users who need full control over the parent route:

```typescript
import { C6AdminChildRoutes } from '@ssnukala/sprinkle-c6admin/routes'

const routes = [
  {
    path: '/c6/admin',
    component: () => import('../layouts/LayoutDashboard.vue'),
    children: C6AdminChildRoutes,
    meta: { 
      title: 'C6ADMIN_PANEL',
      // Add custom meta properties
      requiresAuth: true
    }
  }
]
```

### 3. Default Export - Legacy Support

The default export still works but doesn't include a layout component by default:

```typescript
import C6AdminRoutes from '@ssnukala/sprinkle-c6admin/routes'

const routes = [
  ...C6AdminRoutes  // Parent route without layout component
]
```

## Migration Guide

### Option A: Use the factory function (Recommended)

**Before:**
```typescript
import C6AdminRoutes from '@ssnukala/sprinkle-c6admin/routes'

{
  path: '/c6/admin',
  component: () => import('../layouts/LayoutDashboard.vue'),
  children: [...C6AdminRoutes],
  meta: { title: 'C6ADMIN_PANEL' }
}
```

**After:**
```typescript
import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'

...createC6AdminRoutes({
  layoutComponent: () => import('../layouts/LayoutDashboard.vue')
})
```

### Option B: Use child routes for custom control

**Before:**
```typescript
import C6AdminRoutes from '@ssnukala/sprinkle-c6admin/routes'

{
  path: '/c6/admin',
  component: () => import('../layouts/LayoutDashboard.vue'),
  children: [...C6AdminRoutes],
  meta: { title: 'C6ADMIN_PANEL', custom: 'value' }
}
```

**After:**
```typescript
import { C6AdminChildRoutes } from '@ssnukala/sprinkle-c6admin/routes'

{
  path: '/c6/admin',
  component: () => import('../layouts/LayoutDashboard.vue'),
  children: C6AdminChildRoutes,
  meta: { title: 'C6ADMIN_PANEL', custom: 'value' }
}
```

## Benefits

1. **Simpler Integration**: One import instead of manual wrapping
2. **Less Boilerplate**: No need to create parent route object
3. **Flexibility**: Still supports full customization when needed
4. **Type Safety**: TypeScript interfaces for options
5. **Backward Compatible**: Existing code continues to work

## Testing

All changes are covered by comprehensive tests:

- ✅ Parent route wrapper validation
- ✅ Factory function with default options
- ✅ Factory function with custom layout
- ✅ Factory function with custom base path
- ✅ Factory function with custom title
- ✅ Factory function with all options combined
- ✅ Child routes structure
- ✅ Child routes paths
- ✅ ID-based parameter validation

Run tests with: `npm test`

## Security

CodeQL security scanning completed with no vulnerabilities found.

## Files Changed

1. `app/assets/routes/index.ts` - Added factory function and new exports
2. `app/assets/tests/router/routes.test.ts` - Updated tests for new API
3. `README.md` - Updated documentation with new usage patterns
4. `docs/ROUTE_CONFIGURATION_UPDATE.md` - This file

## Implementation Details

The `createC6AdminRoutes()` factory function:
- Accepts optional configuration via `C6AdminRoutesOptions` interface
- Returns a single-element array with the parent route configuration
- Uses `C6AdminChildRoutes` internally as the children
- Defaults: basePath='/c6/admin', title='C6ADMIN_PANEL', layoutComponent=undefined

The approach allows:
- Consuming applications to provide their own layout component
- Full customization of the parent route when needed
- Simple one-liner import for common cases
- No breaking changes to existing code
