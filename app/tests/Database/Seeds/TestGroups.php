<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (https://github.com/ssnukala/sprinkle-c6admin)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Tests\Database\Seeds;

use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;

/**
 * Test Groups Seed
 * 
 * Creates additional test groups for integration testing and development.
 */
class TestGroups implements SeedInterface
{
    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        echo "========================================\n";
        echo "Creating Test Groups\n";
        echo "========================================\n";

        $testGroups = [
            [
                'slug' => 'developers',
                'name' => 'Developers',
                'description' => 'Development team members with elevated privileges',
                'icon' => 'fa fa-code',
            ],
            [
                'slug' => 'managers',
                'name' => 'Managers',
                'description' => 'Management team with administrative access',
                'icon' => 'fa fa-users',
            ],
            [
                'slug' => 'testers',
                'name' => 'Testers',
                'description' => 'Quality assurance and testing team',
                'icon' => 'fa fa-bug',
            ],
        ];

        foreach ($testGroups as $groupData) {
            // Check if group already exists
            $existingGroup = Group::where('slug', $groupData['slug'])->first();
            if ($existingGroup) {
                echo "Group '{$groupData['slug']}' already exists. Skipping...\n";
                continue;
            }

            // Create group
            $group = Group::create($groupData);

            echo "Created test group: {$groupData['slug']} (ID: {$group->id}) - {$groupData['name']}\n";
        }

        echo "\n========================================\n";
        echo "Test Groups Seed Summary\n";
        echo "========================================\n";
        echo "✅ Test groups seed completed\n";
        
        // Show all groups
        $allGroups = Group::orderBy('slug')->get();
        echo "   Total groups in database: " . $allGroups->count() . "\n";
        foreach ($allGroups as $group) {
            echo "   - {$group->slug}: {$group->name} (ID: {$group->id})\n";
        }
    }
}
