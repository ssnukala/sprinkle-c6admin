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
use UserFrosting\Sprinkle\C6Admin\Middlewares\UserInjector;

/*
 * Routes for administrative user management using CRUD6.
 */
class UsersRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->group('/api/users', function (RouteCollectorProxy $group) {
            // Get user info by user_name
            $group->get('/u/{user_name}', ApiAction::class)
                  ->add(UserInjector::class)
                  ->setName('api_user');
            
            // List all users (sprunje)
            $group->get('', SprunjeAction::class)
                  ->add(UserInjector::class)
                  ->setName('api_users');
            
            // Delete user
            $group->delete('/u/{user_name}', DeleteAction::class)
                  ->add(UserInjector::class)
                  ->setName('api.users.delete');
            
            // Create user
            $group->post('', CreateAction::class)
                  ->add(UserInjector::class)
                  ->setName('api.users.create');
            
            // Update user
            $group->put('/u/{user_name}', EditAction::class)
                  ->add(UserInjector::class)
                  ->setName('api.users.edit');
            
            // Update single field
            $group->put('/u/{user_name}/{field}', UpdateFieldAction::class)
                  ->add(UserInjector::class)
                  ->setName('api.users.update-field');
            
            // Additional user-specific routes can be added here
            // e.g., password reset, roles, permissions, activities
        })->add(AuthGuard::class)->add(NoCache::class);
    }
}
