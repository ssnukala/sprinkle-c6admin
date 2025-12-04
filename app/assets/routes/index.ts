import AdminActivitiesRoutes from './ActivitiesRoutes'
import AdminConfigRoutes from './ConfigRoutes'
import AdminDashboardRoutes from './DashboardRoutes'
import AdminGroupsRoutes from './GroupsRoutes'
import AdminPermissionsRoutes from './PermissionsRoutes'
import AdminRolesRoutes from './RolesRoutes'
import AdminUsersRoutes from './UserRoutes'
import CRUD6Routes from '@ssnukala/sprinkle-crud6/routes'
import type { RouteRecordRaw, Component } from 'vue-router'

/**
 * Child routes for the C6Admin panel (without parent wrapper)
 * These are the actual admin routes that will be nested under /c6/admin
 */
export const C6AdminChildRoutes: RouteRecordRaw[] = [
    { path: '', redirect: { name: 'c6admin.dashboard' } },
    ...AdminDashboardRoutes,
    ...AdminActivitiesRoutes,
    ...AdminGroupsRoutes,
    ...AdminPermissionsRoutes,
    ...AdminRolesRoutes,
    ...AdminUsersRoutes,
    ...AdminConfigRoutes,
    ...CRUD6Routes,
    
]

/**
 * Options for creating C6Admin routes
 */
export interface C6AdminRoutesOptions {
    /**
     * The layout component to use for the admin panel
     * If not provided, defaults to a lazy-loaded LayoutDashboard
     */
    layoutComponent?: Component | (() => Promise<Component>)
    
    /**
     * The base path for the admin panel
     * @default '/c6/admin'
     */
    basePath?: string
    
    /**
     * Meta title for the admin panel
     * @default 'CRUD6.ADMIN_PANEL'
     */
    title?: string
}

/**
 * Create C6Admin route configuration with parent route
 * 
 * This function creates a complete route configuration that can be directly
 * imported and used in the UserFrosting 6 application router.
 * 
 * @param options - Configuration options for the routes
 * @returns Array of route records ready to be used in Vue Router
 * 
 * @example Simple usage with default layout:
 * ```typescript
 * import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'
 * import LayoutDashboard from './layouts/LayoutDashboard.vue'
 * 
 * const routes = [
 *   ...createC6AdminRoutes({ 
 *     layoutComponent: () => import('./layouts/LayoutDashboard.vue')
 *   }),
 *   // other routes
 * ]
 * ```
 * 
 * @example Custom configuration:
 * ```typescript
 * import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'
 * 
 * const routes = [
 *   ...createC6AdminRoutes({
 *     basePath: '/admin/c6',
 *     title: 'Admin Panel',
 *     layoutComponent: () => import('./MyLayout.vue')
 *   })
 * ]
 * ```
 * 
 * @example Using child routes directly:
 * ```typescript
 * import { C6AdminChildRoutes } from '@ssnukala/sprinkle-c6admin/routes'
 * 
 * const routes = [
 *   {
 *     path: '/c6/admin',
 *     component: () => import('./layouts/LayoutDashboard.vue'),
 *     children: C6AdminChildRoutes,
 *     meta: { title: 'CRUD6.ADMIN_PANEL' }
 *   }
 * ]
 * ```
 */
export function createC6AdminRoutes(options: C6AdminRoutesOptions = {}): RouteRecordRaw[] {
    const {
        layoutComponent,
        basePath = '/c6/admin',
        title = 'CRUD6.ADMIN_PANEL'
    } = options

    const route: RouteRecordRaw = {
        path: basePath,
        children: C6AdminChildRoutes,
        meta: {
            auth: {},  // Require authentication for all C6Admin routes
            title
        }
    }

    // Only set component if layoutComponent is provided
    if (layoutComponent) {
        route.component = layoutComponent
    }

    return [route]
}

/**
 * Default export matching sprinkle-admin pattern
 * 
 * Exports FLAT child routes without a parent wrapper, intended to be used as
 * children of a layout route configured in the consuming application.
 * 
 * **Usage in UserFrosting 6 Application:**
 * 
 * The consuming application should create a parent route with a layout component:
 * ```typescript
 * import C6AdminRoutes from '@ssnukala/sprinkle-c6admin/routes'
 * import LayoutDashboard from './layouts/LayoutDashboard.vue'
 * 
 * const routes = [
 *   {
 *     path: '/c6/admin',
 *     component: () => import('./layouts/LayoutDashboard.vue'),
 *     children: C6AdminRoutes,  // <-- Use as children of layout route
 *     meta: { auth: {}, title: 'CRUD6.ADMIN_PANEL' }
 *   }
 * ]
 * ```
 * 
 * **Convenience Helper:**
 * 
 * For simpler integration, use `createC6AdminRoutes()` which creates the parent route automatically:
 * ```typescript
 * import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'
 * 
 * const routes = [
 *   ...createC6AdminRoutes({
 *     layoutComponent: () => import('./layouts/LayoutDashboard.vue')
 *   })
 * ]
 * ```
 * 
 * This matches the pattern used by @userfrosting/sprinkle-admin.
 */
const C6AdminRoutes: RouteRecordRaw[] = C6AdminChildRoutes

export default C6AdminRoutes

export {
    AdminDashboardRoutes,
    AdminActivitiesRoutes,
    AdminGroupsRoutes,
    AdminPermissionsRoutes,
    AdminRolesRoutes,
    AdminUsersRoutes,
    AdminConfigRoutes,
    CRUD6Routes    
}
