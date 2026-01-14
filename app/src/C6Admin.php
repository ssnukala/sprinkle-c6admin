<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin;

use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\CRUD6\CRUD6;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Sprinkle\C6Admin\Routes\ConfigRoutes;
use UserFrosting\Sprinkle\C6Admin\Routes\DashboardRoutes;
use UserFrosting\Sprinkle\C6Admin\Routes\UsersRoutes;

/**
 * C6Admin Sprinkle - Complete admin interface for UserFrosting 6.
 * 
 * This sprinkle provides a comprehensive admin interface powered by sprinkle-crud6.
 * It replicates all functionality of the official userfrosting/sprinkle-admin while
 * leveraging sprinkle-crud6's generic CRUD operations.
 * 
 * Key Features:
 * - JSON schema definitions for admin models (users, roles, groups, permissions, activities)
 * - Dashboard with statistics and recent activity
 * - System configuration utilities (cache management, system info)
 * - All CRUD operations delegated to CRUD6 at /api/crud6/{model} endpoints
 * - Vue.js frontend with 14 pages, composables, and TypeScript interfaces
 * - Complete i18n support (English and French)
 * 
 * Architecture:
 * - This sprinkle provides schemas, dashboard, and config utilities only
 * - CRUD6 handles all CRUD operations (list, view, create, update, delete)
 * - Frontend uses CRUD6's dynamic components for consistent UI
 * 
 * Dependencies:
 * - Core: UserFrosting Core sprinkle
 * - Account: UserFrosting Account sprinkle (authentication/authorization)
 * - CRUD6: Generic CRUD operations (must be registered before C6Admin)
 * 
 * Routes:
 * - /api/c6/dashboard - Dashboard statistics
 * - /api/c6/config/info - System information
 * - /api/c6/cache - Cache management
 * - /api/c6/users/{id}/password-reset - Force password reset
 * 
 * @see CRUD6 For CRUD operation implementation
 * @see https://github.com/ssnukala/sprinkle-c6admin
 */
class C6Admin implements SprinkleRecipe
{
    /**
     * Get the sprinkle name.
     * 
     * @return string The human-readable sprinkle name
     */
    public function getName(): string
    {
        return 'C6Admin Sprinkle';
    }

    /**
     * Get the sprinkle path.
     * 
     * Returns the path to the sprinkle's root directory.
     * 
     * @return string The absolute path to the sprinkle directory
     */
    public function getPath(): string
    {
        return __DIR__ . '/../';
    }

    /**
     * Get required sprinkles.
     * 
     * Returns sprinkles that C6Admin depends on, in order:
     * 1. Core - Base UserFrosting functionality
     * 2. Account - Authentication and authorization
     * 3. CRUD6 - Generic CRUD operations
     * 
     * These will be automatically loaded before C6Admin.
     * 
     * @return array<class-string> Array of sprinkle class names
     */
    public function getSprinkles(): array
    {
        return [
            Core::class,
            Account::class,
            CRUD6::class,
        ];
    }

    /**
     * Get route definition classes.
     * 
     * Returns route definition classes for C6Admin functionality:
     * - DashboardRoutes: Dashboard statistics API
     * - ConfigRoutes: System info and cache management
     * - UsersRoutes: User-specific admin operations
     * 
     * Note: CRUD routes are provided by the CRUD6 sprinkle.
     * 
     * @return array<class-string> Array of route definition class names
     */
    public function getRoutes(): array
    {
        return [
            DashboardRoutes::class,
            ConfigRoutes::class,
            UsersRoutes::class,
        ];
    }

    /**
     * Get service provider classes.
     * 
     * C6Admin does not register any custom services as it relies on
     * services provided by Core, Account, and CRUD6 sprinkles.
     * 
     * @return array<class-string> Empty array (no custom services)
     */
    public function getServices(): array
    {
        return [];
    }
}
