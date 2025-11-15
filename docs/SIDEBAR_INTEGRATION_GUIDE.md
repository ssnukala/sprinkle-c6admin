# Sidebar Integration Guide

## Problem

The left panel on the C6Admin dashboard was not showing because the `SidebarMenuItems` component was not being registered for use in layouts.

## Solution

The C6Admin Vue plugin now properly registers the `SidebarMenuItems` component globally when installed, making it available to any layout component in your UserFrosting 6 application.

## Integration Steps

### 1. Install the C6Admin Plugin

In your main Vue application entry point (usually `app/assets/main.ts` or similar):

```typescript
import { createApp } from 'vue'
import C6AdminPlugin from '@ssnukala/sprinkle-c6admin'

const app = createApp(App)

// Install the C6Admin plugin
app.use(C6AdminPlugin)

// ... other plugins
```

### 2. Use the Sidebar Component in Your Layout

There are two ways to use the sidebar menu items:

#### Option A: Using the Global Component (Recommended)

After installing the plugin, the component is automatically available as `C6AdminSidebarMenuItems`:

```vue
<!-- In your LayoutDashboard.vue or similar layout file -->
<template>
  <div class="layout">
    <UFSideBar>
      <!-- C6Admin menu items -->
      <C6AdminSidebarMenuItems />
      
      <!-- Other sidebar items from other sprinkles -->
      <OtherSidebarItems />
    </UFSideBar>
    
    <main>
      <router-view />
    </main>
  </div>
</template>
```

#### Option B: Direct Import

If you prefer not to use the global component, you can import it directly:

```vue
<script setup lang="ts">
import { SidebarMenuItems } from '@ssnukala/sprinkle-c6admin/components'
</script>

<template>
  <div class="layout">
    <UFSideBar>
      <SidebarMenuItems />
    </UFSideBar>
    
    <main>
      <router-view />
    </main>
  </div>
</template>
```

### 3. Menu Items Included

The sidebar includes the following menu items (each shown based on user permissions):

- **Dashboard** (`c6_uri_dashboard`) - `/c6/admin/dashboard`
- **Users** (`c6_uri_users`) - `/c6/admin/users`
- **Activities** (`c6_uri_activities`) - `/c6/admin/activities`
- **Roles** (`c6_uri_roles`) - `/c6/admin/roles`
- **Permissions** (`c6_uri_permissions`) - `/c6/admin/permissions`
- **Groups** (`c6_uri_groups`) - `/c6/admin/groups`
- **Configuration** (`c6_view_system_info` OR `c6_clear_cache`) - `/c6/admin/config`

### 4. Permissions

Each menu item is conditionally rendered based on the user's permissions. The component uses the `$checkAccess()` method to verify permissions before displaying each item.

If a user doesn't have the required permission for a menu item, that item will not appear in their sidebar.

## Technical Details

### Plugin Implementation

The plugin registers the component in its `install` method:

```typescript
export default {
    install: (app: App) => {
        app.component('C6AdminSidebarMenuItems', SidebarMenuItems)
    }
}
```

### Component Structure

The `SidebarMenuItems.vue` component uses UserFrosting's `UFSideBarItem` component for each menu item:

```vue
<template>
    <UFSideBarItem
        v-if="$checkAccess('c6_uri_dashboard')"
        :to="{ name: 'c6admin.dashboard' }"
        faIcon="gauge-high"
        :label="$t('DASHBOARD')" />
    <!-- ... other items ... -->
</template>
```

## Troubleshooting

### Sidebar Not Showing

1. **Check Plugin Installation**: Ensure `app.use(C6AdminPlugin)` is called in your main app setup
2. **Check Component Name**: Use `<C6AdminSidebarMenuItems />` (with the `C6Admin` prefix) when using the global component
3. **Check Permissions**: Verify the user has at least one of the required permissions
4. **Check Routes**: Ensure C6Admin routes are properly registered in your router

### Menu Items Not Clickable

1. **Check Route Names**: Verify routes are registered with the expected names (e.g., `c6admin.dashboard`)
2. **Check Router**: Ensure Vue Router is properly configured and the C6Admin routes are included

### Permission Errors

1. **Check Backend Permissions**: Ensure the `c6_uri_*` permissions exist in your database
2. **Check User Roles**: Verify the user's roles have the appropriate permissions assigned
3. **Check Permission Slugs**: Permission slugs must match exactly (e.g., `c6_uri_dashboard`, not `c6-uri-dashboard`)

## Example Complete Integration

Here's a complete example of integrating C6Admin into a UserFrosting 6 application:

```typescript
// main.ts
import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia } from 'pinia'
import App from './App.vue'

// Import C6Admin
import C6AdminPlugin from '@ssnukala/sprinkle-c6admin'
import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'

// Create router with C6Admin routes
const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      component: () => import('./layouts/LayoutMain.vue'),
      children: [
        // ... other routes
      ]
    },
    ...createC6AdminRoutes({
      layoutComponent: () => import('./layouts/LayoutDashboard.vue')
    })
  ]
})

// Create and configure app
const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(C6AdminPlugin) // Register C6Admin components

app.mount('#app')
```

```vue
<!-- layouts/LayoutDashboard.vue -->
<template>
  <div class="layout-dashboard">
    <UFNavBar>
      <!-- Navigation bar content -->
    </UFNavBar>
    
    <div class="layout-dashboard__body">
      <UFSideBar>
        <!-- C6Admin sidebar menu -->
        <C6AdminSidebarMenuItems />
      </UFSideBar>
      
      <main class="layout-dashboard__main">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
// No imports needed - C6AdminSidebarMenuItems is registered globally
</script>
```

## Changelog

### v1.0.1 (Current)

- **Fixed**: Sidebar menu items not displaying in layouts
- **Added**: Proper Vue plugin implementation with component registration
- **Added**: Global component `C6AdminSidebarMenuItems` registration
- **Added**: Export for direct component import
- **Added**: Documentation for sidebar integration
- **Added**: Tests for plugin component registration
