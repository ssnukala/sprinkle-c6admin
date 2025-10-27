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
use Slim\Routing\RouteCollectorProxy;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard;
use UserFrosting\Sprinkle\Core\Middlewares\NoCache;
use UserFrosting\Sprinkle\CRUD6\Controller\ApiAction;
use UserFrosting\Sprinkle\CRUD6\Controller\SprunjeAction;
use UserFrosting\Sprinkle\C6Admin\Middlewares\ActivityInjector;

/*
 * Routes for activity logs using CRUD6.
 */
class ActivitiesRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->group('/api/activities', function (RouteCollectorProxy $group) {
            // Get activity info by id
            $group->get('/{id}', ApiAction::class)
                  ->add(ActivityInjector::class)
                  ->setName('api_activity');
            
            // List all activities (sprunje)
            $group->get('', SprunjeAction::class)
                  ->add(ActivityInjector::class)
                  ->setName('api_activities');
            
            // Note: Activities are typically read-only, so no create/update/delete routes
        })->add(AuthGuard::class)->add(NoCache::class);
    }
}
