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
 * User administration routes.
 * 
 * Registers admin-specific user management routes that extend beyond basic CRUD:
 * - POST /api/c6/users/{id}/password-reset - Force user to reset password on next login
 * 
 * All routes require authentication via AuthGuard and have NoCache middleware applied.
 * The CRUD6Injector middleware automatically loads the user model from the {id} parameter.
 */
class UsersRoutes implements RouteDefinitionInterface
{
    /**
     * Register user administration routes.
     * 
     * Registers admin-specific user management endpoints with authentication,
     * CRUD6 model injection, and caching middleware.
     * 
     * @param App $app The Slim application instance
     */
    public function register(App $app): void
    {
        $app->group('/api/c6/users', function (RouteCollectorProxy $group) {
            // Password reset - admin forces user to reset password on next login
            $group->post('/{id}/password-reset', UserPasswordResetAction::class)
                  ->add(CRUD6Injector::class)
                  ->setName('c6.api.users.password-reset');
        })->add(AuthGuard::class)->add(NoCache::class);
    }
}
