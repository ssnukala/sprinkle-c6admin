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
use UserFrosting\Sprinkle\C6Admin\Controller\Dashboard\DashboardApi;
use UserFrosting\Sprinkle\Core\Middlewares\NoCache;

/*
 * Routes for dashboard page.
 */
class DashboardRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->get('/api/dashboard', DashboardApi::class)
            ->setName('dashboard')
            ->add(NoCache::class);
    }
}
