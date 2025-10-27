<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Tests\Routes;

use UserFrosting\Sprinkle\C6Admin\C6Admin;
use UserFrosting\Sprinkle\C6Admin\Routes\ConfigRoutes;
use UserFrosting\Sprinkle\C6Admin\Routes\DashboardRoutes;
use UserFrosting\Sprinkle\C6Admin\Routes\UsersRoutes;
use UserFrosting\Sprinkle\C6Admin\Tests\C6AdminTestCase;

/**
 * Test for route registration.
 * 
 * Validates that all routes are properly registered in the sprinkle.
 */
class RouteRegistrationTest extends C6AdminTestCase
{
    public function testSprinkleRegistersAllRouteClasses(): void
    {
        $sprinkle = new C6Admin();
        $routes = $sprinkle->getRoutes();

        $expectedRoutes = [
            DashboardRoutes::class,
            ConfigRoutes::class,
            UsersRoutes::class,
        ];

        $this->assertCount(count($expectedRoutes), $routes, 'Sprinkle should register exactly 3 route classes');

        foreach ($expectedRoutes as $expectedRoute) {
            $this->assertContains(
                $expectedRoute,
                $routes,
                "Sprinkle should register {$expectedRoute}"
            );
        }
    }

    public function testDashboardRoutesRegistered(): void
    {
        $sprinkle = new C6Admin();
        $routes = $sprinkle->getRoutes();

        $this->assertContains(DashboardRoutes::class, $routes);
    }

    public function testConfigRoutesRegistered(): void
    {
        $sprinkle = new C6Admin();
        $routes = $sprinkle->getRoutes();

        $this->assertContains(ConfigRoutes::class, $routes);
    }

    public function testUsersRoutesRegistered(): void
    {
        $sprinkle = new C6Admin();
        $routes = $sprinkle->getRoutes();

        $this->assertContains(UsersRoutes::class, $routes);
    }

    public function testSprinkleHasCorrectDependencies(): void
    {
        $sprinkle = new C6Admin();
        $dependencies = $sprinkle->getSprinkles();

        $this->assertContains(\UserFrosting\Sprinkle\Core\Core::class, $dependencies);
        $this->assertContains(\UserFrosting\Sprinkle\Account\Account::class, $dependencies);
        $this->assertContains(\UserFrosting\Sprinkle\CRUD6\CRUD6::class, $dependencies);
    }

    public function testSprinkleHasName(): void
    {
        $sprinkle = new C6Admin();
        $name = $sprinkle->getName();

        $this->assertNotEmpty($name);
        $this->assertIsString($name);
        $this->assertEquals('C6Admin Sprinkle', $name);
    }

    public function testSprinkleHasPath(): void
    {
        $sprinkle = new C6Admin();
        $path = $sprinkle->getPath();

        $this->assertNotEmpty($path);
        $this->assertIsString($path);
        $this->assertDirectoryExists($path);
    }
}
