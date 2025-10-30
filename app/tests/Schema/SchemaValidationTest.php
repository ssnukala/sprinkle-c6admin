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
        $requiredFields = ['model', 'table', 'fields', 'primary_key'];

        foreach ($this->schemas as $schema) {
            $path = __DIR__ . "/../../../../schema/crud6/{$schema}.json";
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            foreach ($requiredFields as $field) {
                $this->assertArrayHasKey($field, $decoded, "Schema {$schema}.json missing required field: {$field}");
            }
        }
    }

    public function testSchemasHaveValidFields(): void
    {
        foreach ($this->schemas as $schema) {
            $path = __DIR__ . "/../../../../schema/crud6/{$schema}.json";
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            $this->assertIsArray($decoded['fields'], "Schema {$schema}.json fields must be an array");
            $this->assertNotEmpty($decoded['fields'], "Schema {$schema}.json must have at least one field");

            foreach ($decoded['fields'] as $field => $config) {
                $this->assertIsArray($config, "Field {$field} in {$schema}.json must be an array");
            }
        }
    }

    public function testUserSchemaHasIdField(): void
    {
        $path = __DIR__ . '/../../../../schema/crud6/users.json';
        $content = file_get_contents($path);
        $decoded = json_decode($content, true);

        $this->assertArrayHasKey('id', $decoded['fields'], "users.json must have 'id' field");
    }

    public function testSchemasUseIdAsPrimaryKey(): void
    {
        foreach ($this->schemas as $schema) {
            $path = __DIR__ . "/../../../../schema/crud6/{$schema}.json";
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            // Check if schema has a 'primary_key' field and it's set to 'id'
            $this->assertArrayHasKey('primary_key', $decoded, "Schema {$schema}.json must have 'primary_key' field");
            $this->assertEquals('id', $decoded['primary_key'], "Schema {$schema}.json should use 'id' as primary_key");

            // Also check that 'id' field exists
            $this->assertArrayHasKey('id', $decoded['fields'], "Schema {$schema}.json must have 'id' field");
        }
    }

    /**
     * Test that filterable attribute does not exist in any field.
     * According to cleanup rules, filterable should be removed and only searchable should be used.
     */
    public function testSchemasDoNotHaveFilterableAttribute(): void
    {
        foreach ($this->schemas as $schema) {
            $path = __DIR__ . "/../../../../schema/crud6/{$schema}.json";
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            foreach ($decoded['fields'] as $fieldName => $field) {
                $this->assertArrayNotHasKey(
                    'filterable',
                    $field,
                    "Field '{$fieldName}' in {$schema}.json should not have 'filterable' attribute - use 'searchable' instead"
                );
            }
        }
    }

    /**
     * Test that sortable attribute only exists when listable is true.
     * If a field is not listable, it should not have sortable attribute.
     */
    public function testSortableOnlyExistsWhenListable(): void
    {
        foreach ($this->schemas as $schema) {
            $path = __DIR__ . "/../../../../schema/crud6/{$schema}.json";
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            foreach ($decoded['fields'] as $fieldName => $field) {
                $listable = $field['listable'] ?? false;
                
                if (!$listable && isset($field['sortable'])) {
                    $this->fail(
                        "Field '{$fieldName}' in {$schema}.json has 'sortable' attribute but listable is false. " .
                        "Sortable should only exist when listable is true."
                    );
                }
            }
        }
    }

    /**
     * Test that searchable attribute only exists when listable is true.
     * If a field is not listable, it should not have searchable attribute.
     */
    public function testSearchableOnlyExistsWhenListable(): void
    {
        foreach ($this->schemas as $schema) {
            $path = __DIR__ . "/../../../../schema/crud6/{$schema}.json";
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            foreach ($decoded['fields'] as $fieldName => $field) {
                $listable = $field['listable'] ?? false;
                
                if (!$listable && isset($field['searchable'])) {
                    $this->fail(
                        "Field '{$fieldName}' in {$schema}.json has 'searchable' attribute but listable is false. " .
                        "Searchable should only exist when listable is true."
                    );
                }
            }
        }
    }
}
