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
use UserFrosting\Sprinkle\CRUD6\Controller\CreateAction;
use UserFrosting\Sprinkle\CRUD6\Controller\DeleteAction;
use UserFrosting\Sprinkle\CRUD6\Controller\EditAction;
use UserFrosting\Sprinkle\CRUD6\Controller\SprunjeAction;
use UserFrosting\Sprinkle\CRUD6\Controller\UpdateFieldAction;
use UserFrosting\Sprinkle\C6Admin\Middlewares\RoleInjector;

/*
 * Routes for administrative role management using CRUD6.
 */
class RolesRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->group('/api/roles', function (RouteCollectorProxy $group) {
            // Get role info by slug
            $group->get('/r/{slug}', ApiAction::class)
                  ->add(RoleInjector::class)
                  ->setName('api_role');
            
            // List all roles (sprunje)
            $group->get('', SprunjeAction::class)
                  ->add(RoleInjector::class)
                  ->setName('api_roles');
            
            // Delete role
            $group->delete('/r/{slug}', DeleteAction::class)
                  ->add(RoleInjector::class)
                  ->setName('api.roles.delete');
            
            // Create role
            $group->post('', CreateAction::class)
                  ->add(RoleInjector::class)
                  ->setName('api.roles.create');
            
            // Update role
            $group->put('/r/{slug}', EditAction::class)
                  ->add(RoleInjector::class)
                  ->setName('api.roles.edit');
            
            // Update single field
            $group->put('/r/{slug}/{field}', UpdateFieldAction::class)
                  ->add(RoleInjector::class)
                  ->setName('api.roles.update-field');
        })->add(AuthGuard::class)->add(NoCache::class);
    }
}
