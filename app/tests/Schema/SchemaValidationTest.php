<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Tests\Schema;

use UserFrosting\Sprinkle\C6Admin\Tests\C6AdminTestCase;

/**
 * Test for JSON schemas.
 * 
 * Validates that all schemas are properly formatted and contain required fields.
 */
class SchemaValidationTest extends C6AdminTestCase
{
    protected array $schemas = [
        'users',
        'roles',
        'groups',
        'permissions',
        'activities',
    ];

    public function testAllSchemasExist(): void
    {
        foreach ($this->schemas as $schema) {
            $path = __DIR__ . "/../../../../schema/crud6/{$schema}.json";
            $this->assertFileExists($path, "Schema file {$schema}.json not found");
        }
    }

    public function testAllSchemasAreValidJson(): void
    {
        foreach ($this->schemas as $schema) {
            $path = __DIR__ . "/../../../../schema/crud6/{$schema}.json";
            $content = file_get_contents($path);
            $this->assertNotFalse($content, "Could not read {$schema}.json");
            
            $decoded = json_decode($content, true);
            $this->assertNotNull($decoded, "Invalid JSON in {$schema}.json: " . json_last_error_msg());
        }
    }

    public function testSchemasHaveRequiredFields(): void
    {
        $requiredFields = ['table', 'columns', 'sprunje'];

        foreach ($this->schemas as $schema) {
            $path = __DIR__ . "/../../../../schema/crud6/{$schema}.json";
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            foreach ($requiredFields as $field) {
                $this->assertArrayHasKey($field, $decoded, "Schema {$schema}.json missing required field: {$field}");
            }
        }
    }

    public function testSchemasHaveValidColumns(): void
    {
        foreach ($this->schemas as $schema) {
            $path = __DIR__ . "/../../../../schema/crud6/{$schema}.json";
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            $this->assertIsArray($decoded['columns'], "Schema {$schema}.json columns must be an array");
            $this->assertNotEmpty($decoded['columns'], "Schema {$schema}.json must have at least one column");

            foreach ($decoded['columns'] as $column => $config) {
                $this->assertIsArray($config, "Column {$column} in {$schema}.json must be an array");
            }
        }
    }

    public function testUserSchemaHasIdColumn(): void
    {
        $path = __DIR__ . '/../../../../schema/crud6/users.json';
        $content = file_get_contents($path);
        $decoded = json_decode($content, true);

        $this->assertArrayHasKey('id', $decoded['columns'], "users.json must have 'id' column");
    }

    public function testSchemasUseIdAsKey(): void
    {
        foreach ($this->schemas as $schema) {
            $path = __DIR__ . "/../../../../schema/crud6/{$schema}.json";
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            // Check if schema has a 'key' field and it's set to 'id'
            if (isset($decoded['key'])) {
                $this->assertEquals('id', $decoded['key'], "Schema {$schema}.json should use 'id' as key");
            }

            // Also check that 'id' column exists
            $this->assertArrayHasKey('id', $decoded['columns'], "Schema {$schema}.json must have 'id' column");
        }
    }
}
