# CRUD6 Routes Integration Fix

## Issue Summary

Integration testing revealed Vue routing errors, empty user tables, and API errors in the C6Admin sprinkle when deployed in a UserFrosting 6 application.

**GitHub Actions Run**: [#19455735818](https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19455735818)

## Symptoms

### 1. Vue Routing Errors
Browser console showed Vue warnings when navigating through C6Admin pages:

```
[Browser Error]: No match for
 {"name":"crud6.view","params":{"model":"users","id":"1"}}
```

This error occurred on:
- Users list page (`/c6/admin/users`)
- Groups list page (`/c6/admin/groups`)
- Roles list page (`/c6/admin/roles`)
- Permissions list page (`/c6/admin/permissions`)
- Activities page (`/c6/admin/activities`)

### 2. API Errors
Some detail pages returned 500 Internal Server Error:
- `/api/crud6/roles/1` - 500 error
- `/api/crud6/permissions/1` - 500 error

### 3. Empty Tables
Despite users existing in the database (verified with direct SQL queries), the users list page showed an empty table.

## Root Cause Analysis

C6Admin sprinkle uses **CRUD6's Vue components** but was **not registering CRUD6's routes**:

### Architecture Understanding

1. **C6Admin Purpose**: Admin interface for UserFrosting 6
2. **CRUD6 Purpose**: Generic CRUD operations and reusable components
3. **Relationship**: C6Admin depends on CRUD6 and reuses its components

### The Problem

C6Admin's route configuration (e.g., `UserRoutes.ts`) uses CRUD6 components:

```typescript
// app/assets/routes/UserRoutes.ts
{
    path: 'users',
    children: [
        {
            path: '',
            name: 'c6admin.users',
            component: () => import('@ssnukala/sprinkle-crud6/views').then(m => m.CRUD6ListPage),
        },
        {
            path: ':id',
            name: 'c6admin.user',
            component: () => import('@ssnukala/sprinkle-crud6/views').then(m => m.CRUD6DynamicPage),
        }
    ]
}
```

**However**, the CRUD6 components internally create links using CRUD6 route names:
- `crud6.view` - For detail/view pages
- `crud6.edit` - For edit pages
- `crud6.list` - For list pages

These routes were **never registered** in the integration test setup, causing:
1. Vue router to throw "No match" errors
2. Links in tables to be broken
3. Navigation between pages to fail

## Solution

The integration test workflow needed to register **both CRUD6 and C6Admin** at three levels:

### 1. PHP Backend (MyApp.php)

**Before:**
```php
use UserFrosting\Sprinkle\Admin\Admin;
use UserFrosting\Sprinkle\C6Admin\C6Admin;

public function getSprinkles(): array
{
    return [
        Core::class,
        Account::class,
        Admin::class,
        C6Admin::class,  // ❌ Missing CRUD6
    ];
}
```

**After:**
```php
use UserFrosting\Sprinkle\Admin\Admin;
use UserFrosting\Sprinkle\CRUD6\CRUD6;
use UserFrosting\Sprinkle\C6Admin\C6Admin;

public function getSprinkles(): array
{
    return [
        Core::class,
        Account::class,
        Admin::class,
        CRUD6::class,      // ✅ Added CRUD6
        C6Admin::class,
    ];
}
```

### 2. Vue Router (router/index.ts)

**Before:**
```typescript
import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'
import LayoutDashboard from '../layouts/LayoutDashboard.vue'

const routes = [
    ...AccountRoutes,
    ...AdminRoutes,
    // ❌ Missing CRUD6 routes
    ...createC6AdminRoutes({ layoutComponent: LayoutDashboard })
]
```

**After:**
```typescript
import { createCRUD6Routes } from '@ssnukala/sprinkle-crud6/routes'
import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'
import LayoutDashboard from '../layouts/LayoutDashboard.vue'

const routes = [
    ...AccountRoutes,
    ...AdminRoutes,
    // ✅ Added CRUD6 routes FIRST (dependency order)
    ...createCRUD6Routes({ layoutComponent: LayoutDashboard }),
    ...createC6AdminRoutes({ layoutComponent: LayoutDashboard })
]
```

### 3. Vue App (main.ts)

**Before:**
```typescript
import AdminSprinkle from '@userfrosting/sprinkle-admin'
import C6AdminSprinkle from '@ssnukala/sprinkle-c6admin'

app.use(AccountSprinkle)
app.use(AdminSprinkle)
// ❌ Missing CRUD6Sprinkle
app.use(C6AdminSprinkle)
```

**After:**
```typescript
import AdminSprinkle from '@userfrosting/sprinkle-admin'
import CRUD6Sprinkle from '@ssnukala/sprinkle-crud6'
import C6AdminSprinkle from '@ssnukala/sprinkle-c6admin'

app.use(AccountSprinkle)
app.use(AdminSprinkle)
// ✅ Added CRUD6Sprinkle FIRST (dependency order)
app.use(CRUD6Sprinkle)
app.use(C6AdminSprinkle)
```

## Implementation Details

### Workflow Changes

File: `.github/workflows/integration-test-modular.yml`

#### Step 1: Configure MyApp.php
```yaml
- name: Configure MyApp.php with both CRUD6 and C6Admin sprinkles
  run: |
    cd userfrosting
    # Add CRUD6 and C6Admin imports
    sed -i '/use UserFrosting\\Sprinkle\\Admin\\Admin;/a use UserFrosting\\Sprinkle\\CRUD6\\CRUD6;' app/src/MyApp.php
    sed -i '/use UserFrosting\\Sprinkle\\Admin\\Admin;/a use UserFrosting\\Sprinkle\\C6Admin\\C6Admin;' app/src/MyApp.php
    # Add CRUD6::class and C6Admin::class after Admin::class
    sed -i '/Admin::class,/a \             CRUD6::class,' app/src/MyApp.php
    sed -i '/CRUD6::class,/a \             C6Admin::class,' app/src/MyApp.php
```

#### Step 2: Configure router/index.ts
```yaml
- name: Configure router/index.ts
  run: |
    cd userfrosting
    # Add import statements
    sed -i "/import AdminRoutes from '@userfrosting\/sprinkle-admin\/routes'/a import { createCRUD6Routes } from '@ssnukala\/sprinkle-crud6\/routes'" app/assets/router/index.ts
    sed -i "/import AdminRoutes from '@userfrosting\/sprinkle-admin\/routes'/a import { createC6AdminRoutes } from '@ssnukala\/sprinkle-c6admin\/routes'" app/assets/router/index.ts
    sed -i "/import { createRouter, createWebHistory } from 'vue-router'/a import LayoutDashboard from '../layouts/LayoutDashboard.vue'" app/assets/router/index.ts
    
    # Add routes
    LAST_BRACKET_LINE=$(grep -n '\]' app/assets/router/index.ts | tail -1 | cut -d: -f1)
    sed -i "${LAST_BRACKET_LINE}i\\        ,\\
    // CRUD6 routes for generic CRUD operations\\
    ...createCRUD6Routes({ layoutComponent: LayoutDashboard }),\\
    // C6Admin routes with their own layout\\
    ...createC6AdminRoutes({ layoutComponent: LayoutDashboard })" app/assets/router/index.ts
```

#### Step 3: Configure main.ts
```yaml
- name: Configure main.ts with CRUD6 and C6Admin sprinkles
  run: |
    cd userfrosting
    # Add imports
    sed -i "/import AdminSprinkle from '@userfrosting\/sprinkle-admin'/a import CRUD6Sprinkle from '@ssnukala\/sprinkle-crud6'" app/assets/main.ts
    sed -i "/import AdminSprinkle from '@userfrosting\/sprinkle-admin'/a import C6AdminSprinkle from '@ssnukala\/sprinkle-c6admin'" app/assets/main.ts
    # Add sprinkle registration (CRUD6 must come before C6Admin)
    sed -i '/app\.use(AdminSprinkle)/a app.use(CRUD6Sprinkle)' app/assets/main.ts
    sed -i '/app\.use(CRUD6Sprinkle)/a app.use(C6AdminSprinkle)' app/assets/main.ts
```

## Expected Results

After applying this fix, the integration test should show:

### ✅ No Vue Routing Errors
- CRUD6 components can now resolve `crud6.view`, `crud6.edit`, etc. route names
- Links in tables work correctly
- Navigation between list and detail pages works

### ✅ Working User Tables
- Users list page shows data loaded from `/api/crud6/users`
- Table rows have working links to user detail pages
- Pagination, sorting, and filtering work correctly

### ✅ Working Detail Pages
- All detail pages load without 500 errors
- Role detail pages work: `/c6/admin/roles/:id`
- Permission detail pages work: `/c6/admin/permissions/:id`

### ✅ Proper Route Hierarchy
The final route structure includes:
- Account routes (login, register, etc.)
- Admin routes (original UserFrosting admin)
- **CRUD6 routes** (`/crud6/*` - generic CRUD operations)
- **C6Admin routes** (`/c6/admin/*` - C6Admin interface)

## Dependency Order

**Critical**: CRUD6 must always come **before** C6Admin in all three configurations:

1. **PHP**: `Admin::class, CRUD6::class, C6Admin::class`
2. **Router**: `...createCRUD6Routes(), ...createC6AdminRoutes()`
3. **Vue App**: `app.use(CRUD6Sprinkle), app.use(C6AdminSprinkle)`

This ensures that CRUD6's routes and components are registered before C6Admin tries to use them.

## Testing

To verify the fix works:

1. **Run the integration test workflow**:
   ```bash
   # Trigger the workflow manually or push to main/develop
   ```

2. **Check for Vue routing errors**:
   - Look for "No match for crud6.view" in browser console
   - Should be **absent** after the fix

3. **Check users table**:
   - Navigate to `/c6/admin/users`
   - Table should show users from database
   - Clicking a user should navigate to detail page

4. **Check detail pages**:
   - Navigate to `/c6/admin/roles/1`
   - Navigate to `/c6/admin/permissions/1`
   - Both should load without 500 errors

5. **Download screenshots**:
   - Go to GitHub Actions run artifacts
   - Download `integration-test-screenshots-c6admin`
   - Verify tables are not empty

## Related Issues

- **Admin User Creation**: Fixed separately (admin created BEFORE seeds)
- **API 500 Errors**: May be resolved by proper route registration
- **Empty Tables**: Fixed by ensuring CRUD6 routes exist for data fetching

## Commit

```
Add CRUD6 routes and sprinkle registration to integration test

Fix missing CRUD6 routes that caused Vue routing errors and empty tables.
The issue was that C6Admin uses CRUD6 components but wasn't registering
CRUD6 routes, causing "No match for crud6.view" errors.
```

## References

- Integration test run with errors: [#19455735818](https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19455735818)
- CRUD6 repository: https://github.com/ssnukala/sprinkle-crud6
- C6Admin repository: https://github.com/ssnukala/sprinkle-c6admin
