# sprinkle-c6admin

Complete admin interface for UserFrosting 6, powered by [sprinkle-crud6](https://github.com/ssnukala/sprinkle-crud6).

## Overview

`sprinkle-c6admin` replicates all functionality of the official [userfrosting/sprinkle-admin](https://github.com/userfrosting/sprinkle-admin) while leveraging sprinkle-crud6 for CRUD operations. It provides:

- **CRUD6 Dynamic Templates**: Schema-driven views using common templates
- **JSON Schema-based Backend**: Model definitions that work with CRUD6
- **ID-based Lookups**: Consistent use of `id` instead of slug/user_name
- **RESTful API via CRUD6**: All CRUD at `/api/crud6/{model}` endpoints
- **Drop-in Replacement**: Same features and UI as sprinkle-admin

## Features

### Backend
- **JSON Schemas** for admin models (users, roles, groups, permissions, activities)
- **Dashboard API** with statistics and recent users
- **Config Utilities** for system info and cache management
- **All CRUD** handled by CRUD6 generic controllers

### Frontend
- **CRUD6 Package Integration**: Uses `@ssnukala/sprinkle-crud6` for all CRUD views
- **Schema-Driven Rendering**: All CRUD models rendered from JSON schemas
- **14 API Composables**: Full CRUD operations for all models
- **20+ TypeScript Interfaces**: Type-safe API communication
- **Vue Router Integration**: Clean routing with permissions
- **i18n Support**: English and French translations
- **Email Templates**: User creation notifications

## Requirements

- PHP 8.1 or higher
- UserFrosting 6.0 or higher
- sprinkle-crud6 1.0 or higher
- Node.js & npm (for frontend build)

## Installation

### Step 1: Configure Composer Repositories

Since sprinkle-crud6 is not yet published on Packagist, you need to add its repository to your project's `composer.json`:

```bash
composer config repositories.sprinkle-crud6 git https://github.com/ssnukala/sprinkle-crud6.git
```

Or manually add to your `composer.json`:

```json
{
    "repositories": {
        "sprinkle-crud6": {
            "type": "git",
            "url": "https://github.com/ssnukala/sprinkle-crud6.git"
        }
    }
}
```

### Step 2: Require C6Admin

Add to your UserFrosting 6 project:

```bash
composer require ssnukala/sprinkle-c6admin
```

This will automatically install sprinkle-crud6 as a dependency.

### Step 3: Register Sprinkles

Then register the sprinkle in your main application sprinkle class:

```php
use UserFrosting\Sprinkle\C6Admin\C6Admin;

public function getSprinkles(): array
{
    return [
        Core::class,
        Account::class,
        CRUD6::class,     // Required: CRUD6 must come before C6Admin
        C6Admin::class,
        // Your other sprinkles...
    ];
}
```

**Important**: `CRUD6` must be registered before `C6Admin`.

## Structure

```
app/
├── assets/                # Frontend (Vue.js, TypeScript)
│   ├── components/        # C6Admin components
│   │   └── SidebarMenuItems.vue
│   ├── composables/       # API composables (14 files)
│   ├── views/             # Page components (4 utility pages)
│   │   ├── PageDashboard.vue # Dashboard page
│   │   ├── PageConfig.vue    # Config page
│   │   ├── PageConfigCache.vue
│   │   └── PageConfigInfo.vue
│   ├── routes/            # Vue Router definitions
│   └── interfaces/        # TypeScript types
├── locale/                # Translations
│   ├── en_US/
│   └── fr_FR/
├── schema/crud6/          # JSON schemas for CRUD6
│   ├── users.json
│   ├── roles.json
│   ├── groups.json
│   ├── permissions.json
│   └── activities.json
├── src/                   # Backend (PHP)
│   ├── C6Admin.php        # Main sprinkle class
│   ├── Controller/        # Dashboard & Config controllers
│   └── Routes/            # Route definitions
├── templates/             # Email templates
└── docs/                  # Documentation
    └── CRUD6_MIGRATION.md # Migration guide
```

**Note**: CRUD views (users, groups, roles, permissions, activities) are imported from `@ssnukala/sprinkle-crud6` package.

## Usage

### Frontend Pages

Access admin pages at these routes:
- `/c6/admin/dashboard` - Dashboard with statistics
- `/c6/admin/users` - User list
- `/c6/admin/users/{id}` - User details
- `/c6/admin/groups` - Group list
- `/c6/admin/groups/{id}` - Group details
- `/c6/admin/roles` - Role list
- `/c6/admin/roles/{id}` - Role details
- `/c6/admin/permissions` - Permission list
- `/c6/admin/permissions/{id}` - Permission details
- `/c6/admin/activities` - Activity log
- `/c6/admin/config` - System configuration

**Note**: The `/c6/admin` prefix allows C6Admin to coexist with the standard UserFrosting `sprinkle-admin` which uses `/admin` routes.

### Frontend Integration

C6Admin provides flexible route configuration options for integration into your UserFrosting 6 application:

#### Option 1: Using createC6AdminRoutes() (Recommended)

```typescript
import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'

// In your router configuration (e.g., app/assets/router/index.ts)
const routes = [
  // ... other routes
  ...createC6AdminRoutes({
    layoutComponent: () => import('../layouts/LayoutDashboard.vue')
  })
]
```

You can also customize the base path and title:

```typescript
...createC6AdminRoutes({
  layoutComponent: () => import('../layouts/LayoutDashboard.vue'),
  basePath: '/admin/c6',  // Custom base path
  title: 'Admin Panel'    // Custom meta title
})
```

#### Option 2: Using C6AdminChildRoutes

If you prefer full control over the parent route configuration:

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

#### Option 3: Default Export (Not Recommended)

⚠️ **Warning**: The default export does not include a layout component, which means the admin interface will not have a sidebar panel. This option is provided for backward compatibility only.

**You should use Option 1 or Option 2 instead.**

```typescript
import C6AdminRoutes from '@ssnukala/sprinkle-c6admin/routes'

const routes = [
  ...C6AdminRoutes  // ⚠️ Missing layout component - sidebar will not appear
]
```

If you use this option, the dashboard and other pages will render but **without the left sidebar navigation**. Use `createC6AdminRoutes()` with a `layoutComponent` instead.

#### Sidebar Menu Integration

C6Admin provides a `SidebarMenuItems` component that contains the admin menu items. There are two ways to use it:

**Option 1: Global Component (Automatic)**

When you install the C6Admin plugin in your Vue app, the component is automatically registered globally as `C6AdminSidebarMenuItems`:

```typescript
// In your main.ts or app initialization
import C6AdminPlugin from '@ssnukala/sprinkle-c6admin'

app.use(C6AdminPlugin)
```

Then in your layout (e.g., `LayoutDashboard.vue`):

```vue
<template>
  <UFSideBar>
    <C6AdminSidebarMenuItems />
  </UFSideBar>
</template>
```

**Option 2: Direct Import**

Import the component directly in your layout:

```vue
<script setup lang="ts">
import { SidebarMenuItems } from '@ssnukala/sprinkle-c6admin/components'
</script>

<template>
  <UFSideBar>
    <SidebarMenuItems />
  </UFSideBar>
</template>
```

The sidebar menu items include:
- Dashboard
- Users
- Activities
- Roles
- Permissions
- Groups
- Configuration

Each item is conditionally displayed based on user permissions (`c6_uri_*` permissions).

### API Endpoints

**CRUD Operations (via CRUD6)**:
All models accessible at `/api/crud6/{model}` with ID-based endpoints:
- `GET /api/crud6/{model}` - List records (Sprunje)
- `GET /api/crud6/{model}/{id}` - Get by ID
- `POST /api/crud6/{model}` - Create
- `PUT /api/crud6/{model}/{id}` - Update
- `PUT /api/crud6/{model}/{id}/{field}` - Update single field
- `DELETE /api/crud6/{model}/{id}` - Delete

**Relationship Endpoints**:
- `GET /api/crud6/users/{id}/roles` - Get user's roles
- `GET /api/crud6/roles/{id}/permissions` - Get role's permissions

**Admin Utilities**:
- `GET /api/c6/dashboard` - Dashboard data
- `GET /api/c6/config/info` - System information
- `DELETE /api/c6/cache` - Clear cache

### Frontend Build

Build the frontend assets:

```bash
npm install
npm run build
```

For development:

```bash
npm run dev
```

## Key Differences from sprinkle-admin

1. **CRUD6 Dynamic Templates**: Uses common PageList/PageDynamic templates instead of separate page files
2. **Schema-Driven Views**: All CRUD views rendered from JSON schemas, not hardcoded components
3. **ID-based lookups**: Uses `id` instead of `slug` or `user_name` for consistency
4. **CRUD6 routes**: Uses `/api/crud6/{model}` instead of custom `/api/{model}` routes
5. **No custom controllers**: Leverages CRUD6's generic CRUD controllers
6. **Simpler backend**: Just schemas + dashboard/config utilities
7. **Same frontend UX**: Exact UI/UX replication with refactored implementation

## CRUD6 Dynamic Templates

C6Admin imports CRUD6's dynamic template system from the `@ssnukala/sprinkle-crud6` package:

- **CRUD6ListPage**: Common template for all list views (users, groups, roles, etc.)
- **CRUD6DynamicPage**: Smart wrapper that chooses the appropriate detail view
- **CRUD6RowPage**: Standard detail/edit view
- **CRUD6MasterDetailPage**: Advanced view for models with relationships

This approach provides:
- **No duplication**: Templates provided by CRUD6 package, not duplicated in C6Admin
- **Consistency**: All models use the same proven UI patterns from CRUD6
- **Schema-driven**: Changes only require updating JSON schemas
- **Maintainability**: Single package provides all CRUD functionality

See [docs/CRUD6_MIGRATION.md](docs/CRUD6_MIGRATION.md) for details.

## Development

### Building Frontend

```bash
# Install dependencies
npm install

# Development build with watch
npm run dev

# Production build
npm run build

# Type checking
npm run type-check

# Linting
npm run lint
```

### Testing

This sprinkle includes comprehensive tests for both backend and frontend.

**Backend Tests (PHPUnit)**:
```bash
# Run all backend tests
composer test:php

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/
```

Tests include:
- `UserPasswordResetActionTest` - Password reset functionality
- `DashboardApiTest` - Dashboard statistics API
- `SystemInfoApiActionTest` - System information API
- `CacheApiActionTest` - Cache management API

**Frontend Tests (Vitest)**:
```bash
# Run all frontend tests
npm test

# Run tests in watch mode
npm run test:watch

# Run with coverage
npm run test:coverage
```

Tests include:
- Route validation (ID-based parameters, no slug/user_name)
- Component testing
- API composable validation

**Run All Tests**:
```bash
# Both backend and frontend
composer test
```

## Contributing

Contributions are welcome! This project follows the same coding standards as UserFrosting.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

- Frontend components from [userfrosting/sprinkle-admin](https://github.com/userfrosting/sprinkle-admin)
- CRUD functionality by [ssnukala/sprinkle-crud6](https://github.com/ssnukala/sprinkle-crud6)
- Part of the [UserFrosting](https://www.userfrosting.com) ecosystem

## Support

For issues and questions:
- GitHub Issues: https://github.com/ssnukala/sprinkle-c6admin/issues
- UserFrosting Chat: https://chat.userfrosting.com
