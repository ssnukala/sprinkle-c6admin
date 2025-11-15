import AdminActivitiesRoutes from './ActivitiesRoutes'
import AdminConfigRoutes from './ConfigRoutes'
import AdminDashboardRoutes from './DashboardRoutes'
import AdminGroupsRoutes from './GroupsRoutes'
import AdminPermissionsRoutes from './PermissionsRoutes'
import AdminRolesRoutes from './RolesRoutes'
import AdminUsersRoutes from './UserRoutes'
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
    ...AdminConfigRoutes
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
     * @default 'C6ADMIN_PANEL'
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
 *     meta: { title: 'C6ADMIN_PANEL' }
 *   }
 * ]
 * ```
 */
export function createC6AdminRoutes(options: C6AdminRoutesOptions = {}): RouteRecordRaw[] {
    const {
        layoutComponent,
        basePath = '/c6/admin',
        title = 'C6ADMIN_PANEL'
    } = options

    return [
        {
            path: basePath,
            component: layoutComponent,
            children: C6AdminChildRoutes,
            meta: {
                title
            }
        }
    ]
}

/**
 * Default export for backward compatibility
 * Note: This will not include a layout component by default.
 * Use createC6AdminRoutes() for full functionality.
 */
const C6AdminRoutes: RouteRecordRaw[] = createC6AdminRoutes()

export default C6AdminRoutes

export {
    AdminDashboardRoutes,
    AdminActivitiesRoutes,
    AdminGroupsRoutes,
    AdminPermissionsRoutes,
    AdminRolesRoutes,
    AdminUsersRoutes,
    AdminConfigRoutes
}
