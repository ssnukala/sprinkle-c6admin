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

use Illuminate\Database\Seeder;
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
class TestUsers extends Seeder implements SeedInterface
{
    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        // Get the default group (should exist from Account sprinkle seeds)
        $defaultGroup = Group::where('slug', 'terran')->first();
        if (!$defaultGroup) {
            echo "Warning: Default group 'terran' not found. Creating it...\n";
            $defaultGroup = Group::create([
                'slug' => 'terran',
                'name' => 'Terran',
                'description' => 'Default group for new users',
            ]);
        }

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

            // Extract roles from user data
            $roles = $userData['roles'] ?? [];
            unset($userData['roles']);

            // Create user
            $user = User::create([
                'user_name' => $userData['user_name'],
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'flag_enabled' => $userData['flag_enabled'],
                'flag_verified' => $userData['flag_verified'],
                'group_id' => $defaultGroup->id,
            ]);

            // Attach roles to user (filter out null roles)
            $validRoles = array_filter($roles, fn($role) => $role !== null);
            if (!empty($validRoles)) {
                $user->roles()->attach(array_map(fn($role) => $role->id, $validRoles));
            }

            echo "Created test user: {$userData['user_name']} ({$userData['email']}) - {$userData['description']}\n";
        }

        echo "✅ Test users seed completed\n";
    }
}
