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
use UserFrosting\Sprinkle\C6Admin\Controller\Config\CacheApiAction;
use UserFrosting\Sprinkle\C6Admin\Controller\Config\SystemInfoApiAction;
use UserFrosting\Sprinkle\Core\Middlewares\NoCache;

/**
 * Configuration and system management routes.
 * 
 * Registers API routes for system configuration and management:
 * - GET /api/c6/config/info - Get system information
 * - DELETE /api/c6/cache - Clear application caches
 * 
 * All routes require authentication via AuthGuard and have NoCache middleware applied.
 */
class ConfigRoutes implements RouteDefinitionInterface
{
    /**
     * Register configuration and system management routes.
     * 
     * Registers system information and cache management endpoints with
     * authentication and caching middleware.
     * 
     * @param App $app The Slim application instance
     */
    public function register(App $app): void
    {
        $app->get('/api/c6/config/info', SystemInfoApiAction::class)
            ->setName('c6.api.config.info')
            ->add(AuthGuard::class)
            ->add(NoCache::class);

        $app->delete('/api/c6/cache', CacheApiAction::class)
            ->setName('c6.api.cache.clear')
            ->add(AuthGuard::class)
            ->add(NoCache::class);
    }
}
