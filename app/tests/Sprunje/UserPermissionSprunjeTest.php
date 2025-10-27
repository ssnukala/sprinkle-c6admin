<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Tests\Sprunje;

use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\C6Admin\Sprunje\UserPermissionSprunje;
use UserFrosting\Sprinkle\C6Admin\Tests\C6AdminTestCase;

/**
 * Test for UserPermissionSprunje.
 * 
 * Tests the Sprunje that shows permissions for a specific user with role via info.
 */
class UserPermissionSprunjeTest extends C6AdminTestCase
{
    public function testUserPermissionSprunjeCreation(): void
    {
        /** @var User */
        $user = User::factory()->create();

        $sprunje = new UserPermissionSprunje(['user_id' => $user->id]);

        $this->assertInstanceOf(UserPermissionSprunje::class, $sprunje);
    }

    public function testUserPermissionSprunjeReturnsUserPermissions(): void
    {
        // Create user
        /** @var User */
        $user = User::factory()->create();

        // Create role with permissions
        /** @var Role */
        $role = Role::factory()->create();
        
        /** @var Permission */
        $permission1 = Permission::factory()->create(['slug' => 'test_permission_1']);
        /** @var Permission */
        $permission2 = Permission::factory()->create(['slug' => 'test_permission_2']);

        $role->permissions()->attach([$permission1->id, $permission2->id]);
        $user->roles()->attach($role->id);

        // Test Sprunje
        $sprunje = new UserPermissionSprunje(['user_id' => $user->id]);
        $results = $sprunje->getResults()->toArray();

        $this->assertCount(2, $results['rows']);
    }

    public function testUserPermissionSprunjeWithViaInfo(): void
    {
        // Create user
        /** @var User */
        $user = User::factory()->create();

        // Create role with permission
        /** @var Role */
        $role = Role::factory()->create(['name' => 'Test Role']);
        
        /** @var Permission */
        $permission = Permission::factory()->create();

        $role->permissions()->attach($permission->id);
        $user->roles()->attach($role->id);

        // Test Sprunje
        $sprunje = new UserPermissionSprunje(['user_id' => $user->id]);
        $results = $sprunje->getResults()->toArray();

        // Check that via info (roles) is included
        $this->assertArrayHasKey('rows', $results);
        $this->assertNotEmpty($results['rows']);
        
        $firstRow = $results['rows'][0];
        $this->assertArrayHasKey('roles_via', $firstRow);
    }

    public function testUserPermissionSprunjeFiltering(): void
    {
        /** @var User */
        $user = User::factory()->create();

        /** @var Role */
        $role = Role::factory()->create();
        
        /** @var Permission */
        $permission1 = Permission::factory()->create(['slug' => 'filter_test_1']);
        /** @var Permission */
        $permission2 = Permission::factory()->create(['slug' => 'other_permission']);

        $role->permissions()->attach([$permission1->id, $permission2->id]);
        $user->roles()->attach($role->id);

        // Test filtering
        $sprunje = new UserPermissionSprunje([
            'user_id' => $user->id,
            'filters' => ['slug' => 'filter_test']
        ]);
        $results = $sprunje->getResults()->toArray();

        $this->assertCount(1, $results['rows']);
        $this->assertEquals('filter_test_1', $results['rows'][0]['slug']);
    }
}
