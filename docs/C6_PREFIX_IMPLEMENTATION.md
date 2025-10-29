# C6 Prefix Implementation

## Overview

This document describes the implementation of the `/c6/` prefix for all C6Admin routes, allowing C6Admin to coexist with the standard UserFrosting sprinkle-admin without route conflicts.

## Problem Statement

Previously, C6Admin was designed as a drop-in replacement for sprinkle-admin, which meant it could not run alongside the original admin sprinkle. This caused route conflicts when both sprinkles were registered in the same application.

## Solution

Add a `/c6/` prefix to all C6Admin routes to make them unique:
- Backend API routes: `/api/c6/*`
- Frontend routes: `/c6/admin/*`

## Changes Made

### 1. Backend API Routes (PHP)

All backend routes now use the `/api/c6` prefix:

#### DashboardRoutes.php
- **Before**: `/api/dashboard`
- **After**: `/api/c6/dashboard`
- **Route name**: `c6.api.dashboard`

#### ConfigRoutes.php
- **Before**: `/api/config/info` and `/api/cache`
- **After**: `/api/c6/config/info` and `/api/c6/cache`
- **Route names**: `c6.api.config.info` and `c6.api.cache.clear`

#### UsersRoutes.php
- **Before**: `/api/users/{id}/password-reset`
- **After**: `/api/c6/users/{id}/password-reset`
- **Route name**: `c6.api.users.password-reset`

### 2. Frontend Routes (TypeScript)

All frontend routes now use the `c6/admin` prefix:

#### DashboardRoutes.ts
- **Before**: `dashboard`
- **After**: `c6/admin/dashboard`
- **Route name**: `c6admin.dashboard`

#### GroupsRoutes.ts
- **Before**: `groups`
- **After**: `c6/admin/groups`
- **Route names**: `c6admin.groups`, `c6admin.group`

#### UserRoutes.ts
- **Before**: `users`
- **After**: `c6/admin/users`
- **Route names**: `c6admin.users`, `c6admin.user`

#### RolesRoutes.ts
- **Before**: `roles`
- **After**: `c6/admin/roles`
- **Route names**: `c6admin.roles`, `c6admin.role`

#### PermissionsRoutes.ts
- **Before**: `permissions`
- **After**: `c6/admin/permissions`
- **Route names**: `c6admin.permissions`, `c6admin.permission`

#### ActivitiesRoutes.ts
- **Before**: `activities`
- **After**: `c6/admin/activities`
- **Route name**: `c6admin.activities`

#### ConfigRoutes.ts
- **Before**: `config`
- **After**: `c6/admin/config`
- **Route names**: `c6admin.config`, `c6admin.config.info`, `c6admin.config.cache`

### 3. API Composables (TypeScript)

All API composables updated to use the new `/api/c6` prefix:

#### useDashboardApi.ts
- **Endpoint**: `GET /api/c6/dashboard`

#### useConfigSystemInfoApi.ts
- **Endpoint**: `GET /api/c6/config/info`

#### useConfigCacheApi.ts
- **Endpoint**: `DELETE /api/c6/cache`

#### useUserPasswordResetApi.ts
- **Endpoint**: `POST /api/c6/users/{id}/password-reset`

### 4. Integration Test Workflow

Updated `.github/workflows/integration-test.yml` to:

1. **Keep sprinkle-admin**: No longer removes the default admin package
2. **Add C6Admin alongside**: Both sprinkles now run together
3. **Test both routes**: Tests verify both `/admin/*` and `/c6/admin/*` work
4. **Test both APIs**: Tests verify both `/api/*` and `/api/c6/*` work
5. **Take screenshots**: Captures screenshots of both admin interfaces

#### Key workflow changes:

**Composer Configuration**:
```bash
# Keep sprinkle-admin and add C6Admin alongside it
composer config repositories.sprinkle-crud6 vcs https://github.com/ssnukala/sprinkle-crud6
composer config repositories.local-c6admin path ../sprinkle-c6admin
composer require ssnukala/sprinkle-c6admin:@dev --no-update
```

**MyApp.php Configuration**:
```bash
# Add CRUD6 and C6Admin imports after Admin import
sed -i '/use UserFrosting\\Sprinkle\\Admin\\Admin;/a use UserFrosting\\Sprinkle\\CRUD6\\CRUD6;\nuse UserFrosting\\Sprinkle\\C6Admin\\C6Admin;' app/src/MyApp.php

# Add CRUD6::class and C6Admin::class after Admin::class
sed -i '/Admin::class,/a \            CRUD6::class,\n            C6Admin::class,' app/src/MyApp.php
```

**Router Configuration**:
```bash
# Add C6AdminRoutes import after AdminRoutes
sed -i "/import AdminRoutes from '@userfrosting\/sprinkle-admin\/routes'/a import C6AdminRoutes from '@ssnukala\/sprinkle-c6admin\/routes'" app/assets/router/index.ts

# Add C6AdminRoutes to routes array
sed -i '/\.\.\.AdminRoutes,/a \            ...C6AdminRoutes,' app/assets/router/index.ts
```

**Main.ts Configuration**:
```bash
# Add C6AdminSprinkle import after AdminSprinkle
sed -i "/import AdminSprinkle from '@userfrosting\/sprinkle-admin'/a import C6AdminSprinkle from '@ssnukala\/sprinkle-c6admin'" app/assets/main.ts

# Add C6AdminSprinkle registration
sed -i '/app\.use(AdminSprinkle)/a app.use(C6AdminSprinkle)' app/assets/main.ts
```

## Route Mapping

### API Routes

| Purpose | sprinkle-admin | sprinkle-c6admin |
|---------|---------------|------------------|
| Dashboard | `/api/dashboard` | `/api/c6/dashboard` |
| System Info | `/api/config/info` | `/api/c6/config/info` |
| Clear Cache | `/api/cache` | `/api/c6/cache` |
| Password Reset | `/api/users/{id}/password-reset` | `/api/c6/users/{id}/password-reset` |

### Frontend Routes

| Purpose | sprinkle-admin | sprinkle-c6admin |
|---------|---------------|------------------|
| Dashboard | `/admin/dashboard` | `/c6/admin/dashboard` |
| Groups List | `/admin/groups` | `/c6/admin/groups` |
| Group Detail | `/admin/groups/{id}` | `/c6/admin/groups/{id}` |
| Users List | `/admin/users` | `/c6/admin/users` |
| User Detail | `/admin/users/{id}` | `/c6/admin/users/{id}` |
| Roles List | `/admin/roles` | `/c6/admin/roles` |
| Role Detail | `/admin/roles/{id}` | `/c6/admin/roles/{id}` |
| Permissions List | `/admin/permissions` | `/c6/admin/permissions` |
| Permission Detail | `/admin/permissions/{id}` | `/c6/admin/permissions/{id}` |
| Activities | `/admin/activities` | `/c6/admin/activities` |
| System Info | `/admin/config/info` | `/c6/admin/config/info` |
| Cache Management | `/admin/config/cache` | `/c6/admin/config/cache` |

## Benefits

1. **No Route Conflicts**: C6Admin and sprinkle-admin can run side-by-side
2. **Clear Separation**: Users can easily distinguish between the two admin interfaces
3. **Testing**: Integration tests can verify both interfaces work correctly
4. **Flexibility**: Users can choose which admin interface to use or use both
5. **Migration Path**: Users can migrate from sprinkle-admin to C6Admin gradually

## Testing

The integration test workflow now tests:

1. **CRUD6 API Routes**: `/api/crud6/groups`, `/api/crud6/groups/1`
2. **C6Admin API Routes**: `/api/c6/dashboard`
3. **Admin API Routes**: `/api/dashboard`
4. **C6Admin Frontend Routes**: `/c6/admin/dashboard`, `/c6/admin/groups`, `/c6/admin/users`
5. **Admin Frontend Routes**: `/admin/dashboard`, `/admin/groups`, `/admin/users`

All tests verify that:
- Routes are accessible (return 200 or 401 for authenticated routes)
- No route conflicts occur
- Screenshots can be captured successfully

## Files Modified

### Backend (PHP)
- `app/src/Routes/DashboardRoutes.php`
- `app/src/Routes/ConfigRoutes.php`
- `app/src/Routes/UsersRoutes.php`

### Frontend (TypeScript)
- `app/assets/routes/DashboardRoutes.ts`
- `app/assets/routes/GroupsRoutes.ts`
- `app/assets/routes/UserRoutes.ts`
- `app/assets/routes/RolesRoutes.ts`
- `app/assets/routes/PermissionsRoutes.ts`
- `app/assets/routes/ActivitiesRoutes.ts`
- `app/assets/routes/ConfigRoutes.ts`
- `app/assets/routes/index.ts`
- `app/assets/composables/useDashboardApi.ts`
- `app/assets/composables/useConfigSystemInfoApi.ts`
- `app/assets/composables/useConfigCacheApi.ts`
- `app/assets/composables/useUserPasswordResetApi.ts`

### CI/CD
- `.github/workflows/integration-test.yml`

### Configuration
- `.gitignore` (added `package-lock.json`)

## Future Considerations

1. **Documentation Update**: Update README.md to reflect the new routes
2. **User Guide**: Create documentation explaining how to use both interfaces
3. **Navigation**: Consider adding a link to switch between admin interfaces
4. **Permissions**: Ensure C6-specific permissions are properly configured
5. **Theming**: Consider visual distinction between the two admin interfaces
