import { describe, expect, test } from 'vitest'
import { createApp } from 'vue'
import C6AdminPlugin, { SidebarMenuItems } from '../index'

/**
 * Tests for C6Admin Vue plugin
 * 
 * Ensures the plugin properly registers components:
 * - SidebarMenuItems is registered globally as C6AdminSidebarMenuItems
 * - Component can be exported directly
 */
describe('plugin.test.ts', () => {
    describe('C6AdminPlugin', () => {
        test('should be a valid Vue plugin', () => {
            expect(C6AdminPlugin).toBeDefined()
            expect(typeof C6AdminPlugin.install).toBe('function')
        })

        test('should register SidebarMenuItems globally', () => {
            const app = createApp({})
            
            // Install the plugin
            app.use(C6AdminPlugin)
            
            // Verify the component is registered
            const component = app.component('C6AdminSidebarMenuItems')
            expect(component).toBeDefined()
            expect(component).toBe(SidebarMenuItems)
        })
    })

    describe('SidebarMenuItems export', () => {
        test('should export SidebarMenuItems for direct import', () => {
            expect(SidebarMenuItems).toBeDefined()
            // It should be a Vue component (has __name or similar Vue component properties)
            expect(SidebarMenuItems).toBeTruthy()
        })
    })
})
