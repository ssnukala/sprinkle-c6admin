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
use UserFrosting\Sprinkle\C6Admin\Middlewares\PermissionInjector;

/*
 * Routes for administrative permission management using CRUD6.
 */
class PermissionsRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->group('/api/permissions', function (RouteCollectorProxy $group) {
            // Get permission info by slug
            $group->get('/p/{slug}', ApiAction::class)
                  ->add(PermissionInjector::class)
                  ->setName('api_permission');
            
            // List all permissions (sprunje)
            $group->get('', SprunjeAction::class)
                  ->add(PermissionInjector::class)
                  ->setName('api_permissions');
            
            // Delete permission
            $group->delete('/p/{slug}', DeleteAction::class)
                  ->add(PermissionInjector::class)
                  ->setName('api.permissions.delete');
            
            // Create permission
            $group->post('', CreateAction::class)
                  ->add(PermissionInjector::class)
                  ->setName('api.permissions.create');
            
            // Update permission
            $group->put('/p/{slug}', EditAction::class)
                  ->add(PermissionInjector::class)
                  ->setName('api.permissions.edit');
            
            // Update single field
            $group->put('/p/{slug}/{field}', UpdateFieldAction::class)
                  ->add(PermissionInjector::class)
                  ->setName('api.permissions.update-field');
        })->add(AuthGuard::class)->add(NoCache::class);
    }
}
