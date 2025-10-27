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

/*
 * Routes for administrative role management using CRUD6.
 * TODO: Implement CRUD6-based routes similar to GroupsRoute
 */
class RolesRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        // TODO: Implement role routes using CRUD6
    }
}
