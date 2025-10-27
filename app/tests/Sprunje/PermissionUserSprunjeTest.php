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
use UserFrosting\Sprinkle\C6Admin\Sprunje\PermissionUserSprunje;
use UserFrosting\Sprinkle\C6Admin\Tests\C6AdminTestCase;

/**
 * Test for PermissionUserSprunje.
 * 
 * Tests the Sprunje that shows users for a specific permission with role via info.
 */
class PermissionUserSprunjeTest extends C6AdminTestCase
{
    public function testPermissionUserSprunjeCreation(): void
    {
        /** @var Permission */
        $permission = Permission::factory()->create();

        $sprunje = new PermissionUserSprunje(['permission_id' => $permission->id]);

        $this->assertInstanceOf(PermissionUserSprunje::class, $sprunje);
    }

    public function testPermissionUserSprunjeReturnsPermissionUsers(): void
    {
        // Create permission
        /** @var Permission */
        $permission = Permission::factory()->create();

        // Create role with users
        /** @var Role */
        $role = Role::factory()->create();
        
        /** @var User */
        $user1 = User::factory()->create();
        /** @var User */
        $user2 = User::factory()->create();

        $role->permissions()->attach($permission->id);
        $user1->roles()->attach($role->id);
        $user2->roles()->attach($role->id);

        // Test Sprunje
        $sprunje = new PermissionUserSprunje(['permission_id' => $permission->id]);
        $results = $sprunje->getResults()->toArray();

        $this->assertCount(2, $results['rows']);
    }

    public function testPermissionUserSprunjeWithViaInfo(): void
    {
        // Create permission
        /** @var Permission */
        $permission = Permission::factory()->create();

        // Create role with user
        /** @var Role */
        $role = Role::factory()->create(['name' => 'Test Role']);
        
        /** @var User */
        $user = User::factory()->create();

        $role->permissions()->attach($permission->id);
        $user->roles()->attach($role->id);

        // Test Sprunje
        $sprunje = new PermissionUserSprunje(['permission_id' => $permission->id]);
        $results = $sprunje->getResults()->toArray();

        // Check that via info (roles) is included
        $this->assertArrayHasKey('rows', $results);
        $this->assertNotEmpty($results['rows']);
        
        $firstRow = $results['rows'][0];
        $this->assertArrayHasKey('roles_via', $firstRow);
    }

    public function testPermissionUserSprunjeFiltering(): void
    {
        /** @var Permission */
        $permission = Permission::factory()->create();

        /** @var Role */
        $role = Role::factory()->create();
        
        /** @var User */
        $user1 = User::factory()->create(['user_name' => 'filter_test_user']);
        /** @var User */
        $user2 = User::factory()->create(['user_name' => 'other_user']);

        $role->permissions()->attach($permission->id);
        $user1->roles()->attach($role->id);
        $user2->roles()->attach($role->id);

        // Test filtering
        $sprunje = new PermissionUserSprunje([
            'permission_id' => $permission->id,
            'filters' => ['user_name' => 'filter_test']
        ]);
        $results = $sprunje->getResults()->toArray();

        $this->assertCount(1, $results['rows']);
        $this->assertEquals('filter_test_user', $results['rows'][0]['user_name']);
    }
}
