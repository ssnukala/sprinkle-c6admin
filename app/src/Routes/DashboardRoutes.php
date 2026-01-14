<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Routes;

use Slim\App;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard;
use UserFrosting\Sprinkle\C6Admin\Controller\Dashboard\DashboardApi;
use UserFrosting\Sprinkle\Core\Middlewares\NoCache;

/**
 * Dashboard routes.
 * 
 * Registers API routes for dashboard functionality:
 * - GET /api/c6/dashboard - Get dashboard statistics and recent users
 * 
 * All routes require authentication via AuthGuard and have NoCache middleware applied.
 */
class DashboardRoutes implements RouteDefinitionInterface
{
    /**
     * Register dashboard routes.
     * 
     * Registers the dashboard API endpoint with authentication and caching middleware.
     * 
     * @param App $app The Slim application instance
     */
    public function register(App $app): void
    {
        $app->get('/api/c6/dashboard', DashboardApi::class)
            ->setName('c6.api.dashboard')
            ->add(AuthGuard::class)
            ->add(NoCache::class);
    }
}
