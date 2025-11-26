import type { App } from 'vue'
import { SidebarMenuItems } from './components'
import CRUD6 from '@ssnukala/sprinkle-crud6'

/**
 * Admin Sprinkle initialization recipe.
 * 
 * Registers C6Admin components globally so they can be used in layouts.
 */
export default {
    install: (app: App) => {
        // Register the SidebarMenuItems component globally
        // This allows layouts to use <SidebarMenuItems /> to display the admin menu
        app.use(CRUD6)  // Install CRUD6 dependency
        app.component('C6AdminSidebarMenuItems', SidebarMenuItems)
    }
}

// Also export the component for direct import
export { SidebarMenuItems }
