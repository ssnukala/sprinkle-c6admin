import { describe, expect, test } from 'vitest'

import C6AdminRoutes, { C6AdminChildRoutes, createC6AdminRoutes } from '../../routes'

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
    describe('Default Export (C6AdminRoutes)', () => {
        test('should have parent route wrapper', () => {
            // Should export array with single parent route
            expect(C6AdminRoutes.length).toBe(1)
            
            // Parent route should have path '/c6/admin'
            expect(C6AdminRoutes[0].path).toBe('/c6/admin')
            
            // Parent route should have meta title
            expect(C6AdminRoutes[0].meta?.title).toBe('C6ADMIN_PANEL')
            
            // Parent route should have children
            expect(C6AdminRoutes[0].children).toBeDefined()
            expect(Array.isArray(C6AdminRoutes[0].children)).toBe(true)
        })
    })

    describe('createC6AdminRoutes()', () => {
        test('should create routes with default options', () => {
            const routes = createC6AdminRoutes()
            
            expect(routes.length).toBe(1)
            expect(routes[0].path).toBe('/c6/admin')
            expect(routes[0].meta?.title).toBe('C6ADMIN_PANEL')
            expect(routes[0].children).toBe(C6AdminChildRoutes)
        })

        test('should accept custom layout component', () => {
            const mockLayout = () => Promise.resolve({})
            const routes = createC6AdminRoutes({
                layoutComponent: mockLayout
            })
            
            expect(routes[0].component).toBe(mockLayout)
        })

        test('should accept custom base path', () => {
            const routes = createC6AdminRoutes({
                basePath: '/admin/c6'
            })
            
            expect(routes[0].path).toBe('/admin/c6')
        })

        test('should accept custom title', () => {
            const routes = createC6AdminRoutes({
                title: 'Custom Admin'
            })
            
            expect(routes[0].meta?.title).toBe('Custom Admin')
        })

        test('should accept all options together', () => {
            const mockLayout = () => Promise.resolve({})
            const routes = createC6AdminRoutes({
                layoutComponent: mockLayout,
                basePath: '/my-admin',
                title: 'My Admin Panel'
            })
            
            expect(routes[0].path).toBe('/my-admin')
            expect(routes[0].component).toBe(mockLayout)
            expect(routes[0].meta?.title).toBe('My Admin Panel')
            expect(routes[0].children).toBe(C6AdminChildRoutes)
        })
    })

    describe('C6AdminChildRoutes', () => {
        test('should contain all the individual routes', () => {
            // We have 7 route modules + 1 redirect, total 8 items
            expect(C6AdminChildRoutes.length).toBe(8)
        })

        test('should have expected paths', () => {
            const routePaths = C6AdminChildRoutes.flatMap(route => {
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

        test('should use ID-based parameters', () => {
            const allRoutes = C6AdminChildRoutes.flatMap(route => {
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
})
