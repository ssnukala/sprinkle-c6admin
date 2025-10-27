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
use UserFrosting\Sprinkle\C6Admin\Middlewares\GroupInjector;

/*
 * Routes for administrative group management using CRUD6.
 */
class GroupsRoute implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->group('/api/groups', function (RouteCollectorProxy $group) {
            // Get group info by slug
            $group->get('/g/{slug}', ApiAction::class)
                  ->add(GroupInjector::class)
                  ->setName('api_group');
            
            // List all groups (sprunje)
            $group->get('', SprunjeAction::class)
                  ->add(GroupInjector::class)
                  ->setName('api_groups');
            
            // Delete group
            $group->delete('/g/{slug}', DeleteAction::class)
                  ->add(GroupInjector::class)
                  ->setName('api.groups.delete');
            
            // Create group
            $group->post('', CreateAction::class)
                  ->add(GroupInjector::class)
                  ->setName('api.groups.create');
            
            // Update group
            $group->put('/g/{slug}', EditAction::class)
                  ->add(GroupInjector::class)
                  ->setName('api.groups.edit');
            
            // Update single field
            $group->put('/g/{slug}/{field}', UpdateFieldAction::class)
                  ->add(GroupInjector::class)
                  ->setName('api.groups.update-field');
        })->add(AuthGuard::class)->add(NoCache::class);
    }
}
