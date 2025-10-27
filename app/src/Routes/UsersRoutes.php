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
use UserFrosting\Sprinkle\C6Admin\Controller\User\UserPasswordResetAction;
use UserFrosting\Sprinkle\CRUD6\Middlewares\CRUD6Injector;

/**
 * User admin routes.
 *
 * These are admin-specific routes for user management that go beyond basic CRUD.
 */
class UsersRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->group('/api/users', function (RouteCollectorProxy $group) {
            // Password reset - admin forces user to reset password on next login
            $group->post('/{id}/password-reset', UserPasswordResetAction::class)
                  ->add(CRUD6Injector::class)
                  ->setName('api.users.password-reset');
        })->add(AuthGuard::class)->add(NoCache::class);
    }
}
