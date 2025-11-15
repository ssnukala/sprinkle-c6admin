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
use UserFrosting\Sprinkle\C6Admin\Routes\ConfigRoutes;
use UserFrosting\Sprinkle\C6Admin\Routes\DashboardRoutes;
use UserFrosting\Sprinkle\C6Admin\Routes\UsersRoutes;
use UserFrosting\Sprinkle\C6Admin\Database\Seeds\TestGroups;
use UserFrosting\Sprinkle\C6Admin\Database\Seeds\TestUsers;

/**
 * C6Admin Sprinkle - Admin schemas for CRUD6
 *
 * Provides JSON schemas for administrative models (users, roles, groups, permissions, activities)
 * that work with sprinkle-crud6. All CRUD operations are handled by CRUD6 at /api/crud6/{model}.
 * This sprinkle only provides non-CRUD admin functionality (Dashboard, Config).
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
            DashboardRoutes::class,
            ConfigRoutes::class,
            UsersRoutes::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getServices(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function getSeeds(): array
    {
        return [
            TestGroups::class,
            TestUsers::class,
        ];
    }
    
}
