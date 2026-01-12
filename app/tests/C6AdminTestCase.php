<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Tests;

use UserFrosting\Sprinkle\C6Admin\C6Admin;
use UserFrosting\Testing\TestCase;

/**
 * Test case with C6Admin as main sprinkle
 * 
 * Provides schema directory configuration for CRUD6 integration tests.
 * This allows C6Admin's schemas to be discovered by CRUD6's SchemaBasedApiTest.
 */
class C6AdminTestCase extends TestCase
{
    protected string $mainSprinkle = C6Admin::class;

    /**
     * Get test schema directories for CRUD6 integration tests
     * 
     * Returns an array of directories to search for JSON schemas.
     * Supports the configurable schema path feature from sprinkle-crud6 PR #363.
     * 
     * Priority order:
     * 1. Environment variable TEST_SCHEMA_DIRS (comma-separated)
     * 2. Method override in child classes
     * 3. Default: app/schema/crud6
     * 
     * @return string[] Array of schema directory paths
     */
    protected function getTestSchemaDirs(): array
    {
        // Check environment variable first
        $envDirs = getenv('TEST_SCHEMA_DIRS');
        if ($envDirs !== false && $envDirs !== '') {
            $dirs = array_map('trim', explode(',', $envDirs));
            return $this->normalizeTestSchemaDirs($dirs);
        }

        // Default to C6Admin schema directory
        return $this->normalizeTestSchemaDirs([
            __DIR__ . '/../schema/crud6',
        ]);
    }

    /**
     * Normalize test schema directories
     * 
     * Converts relative paths to absolute and filters out non-existent directories.
     * 
     * @param string[] $dirs Array of directory paths
     * @return string[] Array of normalized directory paths
     */
    protected function normalizeTestSchemaDirs(array $dirs): array
    {
        $normalized = [];
        $baseDir = dirname(__DIR__, 2); // Project root

        foreach ($dirs as $dir) {
            // Convert relative paths to absolute
            if (!str_starts_with($dir, '/')) {
                $dir = $baseDir . '/' . $dir;
            }

            // Only include existing directories
            if (is_dir($dir)) {
                $normalized[] = realpath($dir);
            }
        }

        return array_unique($normalized);
    }
}
