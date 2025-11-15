<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (https://github.com/ssnukala/sprinkle-c6admin)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Database\Seeds;

use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;

/**
 * Test Users Seed
 * 
 * Creates test users for integration testing and development.
 * These users have different roles and permissions for testing C6Admin functionality.
 */
class TestUsers implements SeedInterface
{
    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        echo "========================================\n";
        echo "Creating Test Users\n";
        echo "========================================\n";
        
        // Get groups (should exist from Account and C6Admin TestGroups seeds)
        $defaultGroup = Group::where('slug', 'terran')->first();
        if (!$defaultGroup) {
            echo "Warning: Default group 'terran' not found. Creating it...\n";
            $defaultGroup = Group::create([
                'slug' => 'terran',
                'name' => 'Terran',
                'description' => 'Default group for new users',
            ]);
        }
        
        $developersGroup = Group::where('slug', 'developers')->first();
        $managersGroup = Group::where('slug', 'managers')->first();
        $testersGroup = Group::where('slug', 'testers')->first();

        // Get roles
        $siteAdminRole = Role::where('slug', 'site-admin')->first();
        $crud6AdminRole = Role::where('slug', 'crud6-admin')->first();
        $userRole = Role::where('slug', 'user')->first();

        // Create test users
        $testUsers = [
            [
                'user_name' => 'testadmin',
                'first_name' => 'Test',
                'last_name' => 'Administrator',
                'email' => 'testadmin@example.com',
                'password' => 'testpass123',
                'flag_enabled' => true,
                'flag_verified' => true,
                'group' => $managersGroup ?? $defaultGroup,  // Assign to managers group
                'roles' => [$siteAdminRole],
                'description' => 'Test administrator with full site-admin permissions',
            ],
            [
                'user_name' => 'c6admin',
                'first_name' => 'C6',
                'last_name' => 'Administrator',
                'email' => 'c6admin@example.com',
                'password' => 'testpass123',
                'flag_enabled' => true,
                'flag_verified' => true,
                'group' => $developersGroup ?? $defaultGroup,  // Assign to developers group
                'roles' => [$crud6AdminRole],
                'description' => 'Test user with CRUD6/C6Admin permissions',
            ],
            [
                'user_name' => 'testuser',
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'testuser@example.com',
                'password' => 'testpass123',
                'flag_enabled' => true,
                'flag_verified' => true,
                'group' => $defaultGroup,  // Assign to default group
                'roles' => [$userRole],
                'description' => 'Regular test user with basic permissions',
            ],
            [
                'user_name' => 'testmoderator',
                'first_name' => 'Test',
                'last_name' => 'Moderator',
                'email' => 'testmoderator@example.com',
                'password' => 'testpass123',
                'flag_enabled' => true,
                'flag_verified' => true,
                'group' => $testersGroup ?? $defaultGroup,  // Assign to testers group
                'roles' => [$userRole, $crud6AdminRole],
                'description' => 'Test moderator with user and CRUD6 admin permissions',
            ],
        ];

        foreach ($testUsers as $userData) {
            // Check if user already exists
            $existingUser = User::where('user_name', $userData['user_name'])->first();
            if ($existingUser) {
                echo "User '{$userData['user_name']}' already exists. Skipping...\n";
                continue;
            }

            // Extract roles, group, and description from user data
            $roles = $userData['roles'] ?? [];
            $group = $userData['group'] ?? $defaultGroup;
            $description = $userData['description'] ?? '';
            unset($userData['roles'], $userData['group'], $userData['description']);

            // Create user with group_id assigned
            $user = User::create([
                'user_name' => $userData['user_name'],
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'flag_enabled' => $userData['flag_enabled'],
                'flag_verified' => $userData['flag_verified'],
                'group_id' => $group->id,  // Assign to specified group
            ]);

            echo "Created user: {$userData['user_name']} (ID: {$user->id})\n";
            echo "  ✅ Assigned to group: {$group->name} (ID: {$group->id})\n";

            // Attach roles to user via role_users pivot table (filter out null roles)
            $validRoles = array_filter($roles, fn($role) => $role !== null);
            if (!empty($validRoles)) {
                $roleIds = array_map(fn($role) => $role->id, $validRoles);
                $user->roles()->attach($roleIds);
                
                $roleNames = array_map(fn($role) => $role->name, $validRoles);
                echo "  ✅ Assigned roles: " . implode(', ', $roleNames) . "\n";
                echo "  ✅ Created " . count($roleIds) . " role_users record(s)\n";
            } else {
                echo "  ⚠️  No roles assigned\n";
            }
            
            echo "  Description: {$description}\n";
            echo "\n";
        }

        echo "========================================\n";
        echo "Test Users Seed Summary\n";
        echo "========================================\n";
        echo "✅ Test users seed completed\n";
        echo "   - Users assigned to appropriate groups with group_id\n";
        echo "   - Role assignments created in role_users pivot table\n";
        
        // Summary statistics
        $totalUsers = User::count();
        // Count all role_users assignments by summing roles for all users
        $totalRoleUsers = User::withCount('roles')->get()->sum('roles_count');
        echo "   - Total users in database: {$totalUsers}\n";
        echo "   - Total role_users assignments: {$totalRoleUsers}\n";
    }
}
