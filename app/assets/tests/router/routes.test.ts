import { describe, expect, test } from 'vitest'

import C6AdminRoutes from '../../routes'

/**
 * Tests for C6Admin frontend routes
 * 
 * Ensures all admin routes are properly registered:
 * - Dashboard
 * - Users (list and detail)
 * - Groups (list and detail)
 * - Roles (list and detail)
 * - Permissions (list and detail)
 * - Activities
 * - Config
 */
describe('routes.test.ts', () => {
    test('C6AdminRoutes should contain all the individual routes', () => {
        // We have 7 route modules: Dashboard, Users, Groups, Roles, Permissions, Activities, Config
        expect(C6AdminRoutes.length).toBe(7)
    })

    test('Routes should have expected paths', () => {
        const routePaths = C6AdminRoutes.flatMap(route => {
            if (route.children) {
                return route.children.map(child => route.path + '/' + child.path)
            }
            return [route.path]
        })

        // Check for key routes
        expect(routePaths).toContain('dashboard')
        expect(routePaths.some(p => p.includes('users'))).toBe(true)
        expect(routePaths.some(p => p.includes('groups'))).toBe(true)
        expect(routePaths.some(p => p.includes('roles'))).toBe(true)
        expect(routePaths.some(p => p.includes('permissions'))).toBe(true)
        expect(routePaths.some(p => p.includes('activities'))).toBe(true)
        expect(routePaths.some(p => p.includes('config'))).toBe(true)
    })

    test('Routes should use ID-based parameters', () => {
        const allRoutes = C6AdminRoutes.flatMap(route => {
            if (route.children) {
                return route.children
            }
            return [route]
        })

        // Check that we're using :id, not :slug or :user_name
        const routesWithParams = allRoutes.filter(r => r.path?.includes(':'))
        
        routesWithParams.forEach(route => {
            // Should use :id
            if (route.path?.includes(':')) {
                expect(route.path).toContain(':id')
                // Should NOT use old slug/user_name patterns
                expect(route.path).not.toContain(':slug')
                expect(route.path).not.toContain(':user_name')
            }
        })
    })
})
