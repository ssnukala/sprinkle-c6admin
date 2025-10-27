<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin;

use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\CRUD6\CRUD6;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Sprinkle\C6Admin\Routes\ActivitiesRoutes;
use UserFrosting\Sprinkle\C6Admin\Routes\ConfigRoutes;
use UserFrosting\Sprinkle\C6Admin\Routes\DashboardRoutes;
use UserFrosting\Sprinkle\C6Admin\Routes\GroupsRoute;
use UserFrosting\Sprinkle\C6Admin\Routes\PermissionsRoutes;
use UserFrosting\Sprinkle\C6Admin\Routes\RolesRoutes;
use UserFrosting\Sprinkle\C6Admin\Routes\UsersRoutes;

/**
 * C6Admin Sprinkle - Drop-in replacement for Admin Sprinkle using CRUD6
 *
 * Provides administrative functionality for UserFrosting 6 using the CRUD6 framework.
 * This sprinkle replicates all functionality of the official sprinkle-admin while
 * leveraging sprinkle-crud6 for CRUD operations.
 */
class C6Admin implements SprinkleRecipe
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'C6Admin Sprinkle';
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return __DIR__ . '/../';
    }

    /**
     * {@inheritdoc}
     */
    public function getSprinkles(): array
    {
        return [
            Core::class,
            Account::class,
            CRUD6::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutes(): array
    {
        return [
            ActivitiesRoutes::class,
            DashboardRoutes::class,
            GroupsRoute::class,
            PermissionsRoutes::class,
            RolesRoutes::class,
            UsersRoutes::class,
            ConfigRoutes::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getServices(): array
    {
        return [];
    }
}
